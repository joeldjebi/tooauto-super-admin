<?php

namespace App\Http\Controllers;

use App\Models\NotificationCampaign;
use App\Models\NotificationCampaignLog;
use App\Models\Type_alert;
use App\Models\User;
use App\Models\Ville;
use App\Models\Commune;
use App\Services\NotificationCampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationCampaignController extends Controller
{
    public function index(Request $request, NotificationCampaignService $service)
    {
        $this->authorizeAccess();

        $audienceType = $request->input('audience_type', NotificationCampaign::AUDIENCE_ALL_USERS);
        $filters = $this->cleanFilters($request->input('filters', []), $audienceType);

        $data['title'] = 'Notification send';
        $data['menu'] = $this->isCallCenter() ? 'call-center-notification-send' : 'notification-send';
        $data['isCallCenter'] = $this->isCallCenter();
        $data['audienceType'] = $audienceType;
        $data['filters'] = $filters;
        $data['typeAlerts'] = Type_alert::orderBy('libelle')->get(['id', 'libelle']);
        $data['userFilterColumns'] = $this->notificationUserFilterColumns();
        $data['villes'] = Schema::hasTable('villes') ? Ville::orderBy('libelle')->get(['id', 'libelle']) : collect();
        $communeNameColumn = Schema::hasColumn('communes', 'libelle') ? 'libelle' : 'nom';
        $data['communes'] = Schema::hasTable('communes') && Schema::hasColumn('communes', $communeNameColumn)
            ? Commune::select('id', 'ville_id')->selectRaw($communeNameColumn . ' as libelle')->orderBy($communeNameColumn)->get()
            : collect();
        $data['selectedUsers'] = $this->selectedUsers($filters);
        $data['previewUsers'] = $service->audienceQuery($audienceType, $filters)
            ->paginate(20, ['*'], 'users_page')
            ->appends($request->except('users_page'));
        $data['previewCount'] = $service->countAudience($audienceType, $filters);
        $data['campaigns'] = NotificationCampaign::orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'campaigns_page')
            ->appends($request->except('campaigns_page'));
        $data['indexRoute'] = $this->routeName('notification-send.index');
        $data['storeRoute'] = $this->routeName('notification-send.store');
        $data['logsRoute'] = $this->routeName('notification-send.logs');
        $data['sendNowRouteName'] = $this->routeName('notification-send.send-now');
        $data['cancelRouteName'] = $this->routeName('notification-send.cancel');

        return view('notification_send.index', $data);
    }

    public function store(Request $request, NotificationCampaignService $service)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'action_url' => 'nullable|url|max:500',
            'audience_type' => 'required|string|in:all_users,selected_users,alert_expiration',
            'filters' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
            'send_now' => 'nullable|boolean',
        ]);

        $filters = $this->cleanFilters($validated['filters'] ?? [], $validated['audience_type']);
        $sendNow = $request->boolean('send_now');
        $scheduledAt = $sendNow ? now() : ($validated['scheduled_at'] ?? null);

        if (!$sendNow && empty($scheduledAt)) {
            return back()->withErrors(['scheduled_at' => 'Choisissez une date de programmation ou envoyez maintenant.'])->withInput();
        }

        if ($validated['audience_type'] === NotificationCampaign::AUDIENCE_SELECTED_USERS && empty($filters['user_ids'])) {
            return back()->withErrors(['filters.user_ids' => 'Sélectionnez au moins un usager.'])->withInput();
        }

        $campaign = NotificationCampaign::create([
            'title' => html_entity_decode($validated['title']),
            'body' => html_entity_decode($validated['body']),
            'image_url' => $validated['image_url'] ?? null,
            'action_url' => $validated['action_url'] ?? null,
            'audience_type' => $validated['audience_type'],
            'audience_filters' => $filters,
            'scheduled_at' => $scheduledAt,
            'status' => $sendNow ? NotificationCampaign::STATUS_SENDING : NotificationCampaign::STATUS_SCHEDULED,
            'created_by' => $this->actorId(),
            'created_by_type' => $this->actorType(),
        ]);

        if ($sendNow) {
            $result = $service->send($campaign);
            session()->flash('type', $result['success'] ? 'alert-success' : 'alert-danger');
            session()->flash('message', $result['message']);

            return redirect()->route($this->routeName('notification-send.index'));
        }

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Notification programmée avec succès.');

        return redirect()->route($this->routeName('notification-send.index'));
    }

    public function sendNow(NotificationCampaign $notificationCampaign, NotificationCampaignService $service)
    {
        $this->authorizeAccess();

        if (in_array($notificationCampaign->status, [NotificationCampaign::STATUS_SENT, NotificationCampaign::STATUS_SENDING], true)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Cette notification ne peut pas être envoyée à nouveau depuis cette action.');
            return back();
        }

        $result = $service->send($notificationCampaign);
        session()->flash('type', $result['success'] ? 'alert-success' : 'alert-danger');
        session()->flash('message', $result['message']);

        return back();
    }

    public function cancel(NotificationCampaign $notificationCampaign)
    {
        $this->authorizeAccess();

        if ($notificationCampaign->status !== NotificationCampaign::STATUS_SCHEDULED) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Seules les notifications programmées peuvent être annulées.');
            return back();
        }

        $notificationCampaign->update(['status' => NotificationCampaign::STATUS_CANCELLED]);

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Programmation annulée.');

        return back();
    }

    public function logs(Request $request)
    {
        $this->authorizeAccess();

        $query = NotificationCampaignLog::query()
            ->with(['campaign:id,title,status,audience_type', 'user', 'typeAlert:id,libelle', 'alert:id,date_fin,type_alert_id'])
            ->latest('sent_at')
            ->latest('created_at');

        if ($request->filled('notification_campaign_id')) {
            $query->where('notification_campaign_id', $request->notification_campaign_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . trim($request->keyword) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('fcm_token', 'like', $keyword)
                    ->orWhere('error_message', 'like', $keyword)
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        foreach (['nom', 'prenoms', 'name', 'email', 'telephone', 'mobile'] as $column) {
                            if (Schema::hasColumn('users', $column)) {
                                $userQuery->orWhere($column, 'like', $keyword);
                            }
                        }
                    });
            });
        }

        return view('notification_send.logs', [
            'title' => 'Logs notifications',
            'menu' => $this->isCallCenter() ? 'call-center-notification-send' : 'notification-send',
            'isCallCenter' => $this->isCallCenter(),
            'logs' => $query->paginate(25)->appends($request->query()),
            'campaigns' => NotificationCampaign::orderBy('created_at', 'desc')->get(['id', 'title', 'status']),
            'indexRoute' => $this->routeName('notification-send.index'),
            'logsRoute' => $this->routeName('notification-send.logs'),
        ]);
    }

    private function cleanFilters(array $filters, string $audienceType): array
    {
        $filters = collect($filters)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        if ($audienceType === NotificationCampaign::AUDIENCE_SELECTED_USERS) {
            $filters['user_ids'] = collect($filters['user_ids'] ?? [])
                ->flatMap(fn ($value) => is_string($value) ? preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) : [$value])
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if ($audienceType === NotificationCampaign::AUDIENCE_ALERT_EXPIRATION) {
            return collect($filters)
                ->only(['type_alert_id', 'expires_mode', 'days', 'date_from', 'date_to', 'keyword', 'user_id', 'fcm_token', 'statut', 'ville_id', 'commune_id'])
                ->all();
        }

        return collect($filters)
            ->only(['user_ids', 'keyword', 'user_id', 'fcm_token', 'statut', 'ville_id', 'commune_id'])
            ->all();
    }

    private function notificationUserFilterColumns(): array
    {
        return collect(['statut', 'ville_id', 'commune_id'])
            ->mapWithKeys(fn ($column) => [$column => Schema::hasColumn('users', $column)])
            ->all();
    }
    private function selectedUsers(array $filters)
    {
        $ids = $filters['user_ids'] ?? [];

        if (empty($ids)) {
            return collect();
        }

        return User::whereIn('id', $ids)->orderBy('nom')->get();
    }

    private function authorizeAccess(): void
    {
        if ($this->isCallCenter()) {
            return;
        }

        abort_unless(auth()->check() && auth()->user()?->isSuperAdmin(), 403);
    }

    private function isCallCenter(): bool
    {
        return Auth::guard('call_center')->check();
    }

    private function actorId(): ?int
    {
        return $this->isCallCenter() ? Auth::guard('call_center')->id() : Auth::id();
    }

    private function actorType(): string
    {
        return $this->isCallCenter() ? 'call_center' : 'super_admin';
    }

    private function routeName(string $adminRoute): string
    {
        if (!$this->isCallCenter()) {
            return $adminRoute;
        }

        return 'call-center.' . $adminRoute;
    }
}