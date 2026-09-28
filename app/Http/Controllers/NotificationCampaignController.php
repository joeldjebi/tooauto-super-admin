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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class NotificationCampaignController extends Controller
{
    public function index(Request $request, NotificationCampaignService $service)
    {
        $this->authorizeAccess();
        $this->sendDueCampaigns($service);

        $audienceData = $this->audienceViewData($request, $service);

        $data['title'] = 'Ciblage notifications';
        $data['menu'] = $this->isCallCenter() ? 'call-center-notification-send' : 'notification-send';
        $data = array_merge($data, $audienceData);
        $data['indexRoute'] = $this->routeName('notification-send.index');
        $data['createRoute'] = $this->routeName('notification-send.create');
        $data['storeRoute'] = $this->routeName('notification-send.store');
        $data['programmesRoute'] = $this->routeName('notification-send.programmes');
        $data['logsRoute'] = $this->routeName('notification-send.logs');

        return view('notification_send.index', $data);
    }

    public function create(Request $request, NotificationCampaignService $service)
    {
        $this->authorizeAccess();

        return redirect()->route($this->routeName('notification-send.programmes'), $request->query());
    }

    public function programmes(Request $request, NotificationCampaignService $service)
    {
        $this->authorizeAccess();
        $this->sendDueCampaigns($service);

        $query = NotificationCampaign::query()->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('audience_type')) {
            $query->where('audience_type', $request->audience_type);
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . trim($request->keyword) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', $keyword)
                    ->orWhere('body', 'like', $keyword)
                    ->orWhere('last_error', 'like', $keyword);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        $selectionOptions = collect();
        if (! $this->isCallCenter()) {
            foreach ($this->selectedAudienceTypes() as $audienceType => $filterKey) {
                $selectionOptions = $selectionOptions->merge(
                    $service->selectionOptions($audienceType)->map(function ($recipient) use ($audienceType, $filterKey) {
                        $recipient->audience_type = $audienceType;
                        $recipient->filter_key = $filterKey;
                        return $recipient;
                    })
                );
            }
        } else {
            $selectionOptions = $this->notificationFcmUsers()->map(function ($recipient) {
                $recipient->recipient_id = $recipient->id;
                $recipient->audience_type = NotificationCampaign::AUDIENCE_SELECTED_USERS;
                $recipient->filter_key = 'user_ids';
                return $recipient;
            });
        }

        return view('notification_send.programmes', [
            'title' => 'Notifications programmées',
            'menu' => $this->isCallCenter() ? 'call-center-notification-programmes' : 'notification-programmes',
            'isCallCenter' => $this->isCallCenter(),
            'campaigns' => $query->paginate(20)->appends($request->query()),
            'selectionOptions' => $selectionOptions,
            'audienceLabels' => $this->audienceLabels(),
            'indexRoute' => $this->routeName('notification-send.index'),
            'createRoute' => $this->routeName('notification-send.create'),
            'storeRoute' => $this->routeName('notification-send.store'),
            'programmesRoute' => $this->routeName('notification-send.programmes'),
            'logsRoute' => $this->routeName('notification-send.logs'),
            'sendNowRouteName' => $this->routeName('notification-send.send-now'),
            'cancelRouteName' => $this->routeName('notification-send.cancel'),
        ]);
    }

    public function store(Request $request, NotificationCampaignService $service)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'action_url' => 'nullable|url|max:500',
            'audience_type' => ['required', 'string', Rule::in($this->allowedAudienceTypes())],
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

        $selectionKey = $this->selectedAudienceTypes()[$validated['audience_type']] ?? null;
        if ($selectionKey && empty($filters[$selectionKey])) {
            return back()->withErrors(['filters.' . $selectionKey => 'Sélectionnez au moins un destinataire.'])->withInput();
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

        if ($sendNow || \Carbon\Carbon::parse($scheduledAt)->lte(now())) {
            $result = $service->send($campaign);
            session()->flash('type', $result['success'] ? 'alert-success' : 'alert-danger');
            session()->flash('message', $result['message']);

            return redirect()->route($this->routeName('notification-send.programmes'));
        }

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Notification programmée avec succès.');

        return redirect()->route($this->routeName('notification-send.programmes'));
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

        $hasRecipientColumns = Schema::hasColumn('notification_campaign_logs', 'recipient_type');
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

        if ($hasRecipientColumns && $request->filled('recipient_type')) {
            $query->where('recipient_type', $request->recipient_type);
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

                if (Schema::hasColumn('notification_campaign_logs', 'recipient_id') && is_numeric(trim($keyword, '%'))) {
                    $q->orWhere('recipient_id', (int) trim($keyword, '%'));
                }
            });
        }

        $logs = $query->paginate(25)->appends($request->query());
        $this->hydrateLogRecipients($logs->getCollection());

        return view('notification_send.logs', [
            'title' => 'Logs notifications',
            'menu' => $this->isCallCenter() ? 'call-center-notification-send' : 'notification-send',
            'isCallCenter' => $this->isCallCenter(),
            'logs' => $logs,
            'hasRecipientColumns' => $hasRecipientColumns,
            'campaigns' => NotificationCampaign::orderBy('created_at', 'desc')->get(['id', 'title', 'status']),
            'indexRoute' => $this->routeName('notification-send.index'),
            'createRoute' => $this->routeName('notification-send.create'),
            'programmesRoute' => $this->routeName('notification-send.programmes'),
            'logsRoute' => $this->routeName('notification-send.logs'),
        ]);
    }

    private function cleanFilters(array $filters, string $audienceType): array
    {
        $filters = collect($filters)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        $selectionKey = $this->selectedAudienceTypes()[$audienceType] ?? null;
        if ($selectionKey) {
            $filters[$selectionKey] = collect($filters[$selectionKey] ?? [])
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

        if (in_array($audienceType, $this->professionalAudienceTypes(), true)) {
            return collect($filters)->only(['professional_ids', 'keyword', 'recipient_id', 'statut'])->all();
        }

        if (in_array($audienceType, $this->lavageAudienceTypes(), true)) {
            return collect($filters)->only(['lavage_ids', 'keyword', 'recipient_id', 'statut'])->all();
        }

        if (in_array($audienceType, $this->stationAudienceTypes(), true)) {
            return collect($filters)->only(['station_ids', 'keyword', 'recipient_id', 'statut'])->all();
        }

        return collect($filters)
            ->only(['user_ids', 'keyword', 'user_id', 'fcm_token', 'statut', 'ville_id', 'commune_id'])
            ->all();
    }

    private function audienceViewData(Request $request, NotificationCampaignService $service): array
    {
        $audienceType = $request->input('audience_type', NotificationCampaign::AUDIENCE_ALL_USERS);
        if (! in_array($audienceType, [NotificationCampaign::AUDIENCE_ALL_USERS, NotificationCampaign::AUDIENCE_SELECTED_USERS, NotificationCampaign::AUDIENCE_ALERT_EXPIRATION], true)) {
            $audienceType = NotificationCampaign::AUDIENCE_ALL_USERS;
        }
        $filters = $this->cleanFilters($request->input('filters', []), $audienceType);
        $communeNameColumn = Schema::hasTable('communes') && Schema::hasColumn('communes', 'libelle') ? 'libelle' : 'nom';

        return [
            'isCallCenter' => $this->isCallCenter(),
            'audienceType' => $audienceType,
            'filters' => $filters,
            'typeAlerts' => Type_alert::orderBy('libelle')->get(['id', 'libelle']),
            'userFilterColumns' => $this->notificationUserFilterColumns(),
            'fcmUsers' => $this->notificationFcmUsers(),
            'villes' => Schema::hasTable('villes') ? Ville::orderBy('libelle')->get(['id', 'libelle']) : collect(),
            'communes' => Schema::hasTable('communes') && Schema::hasColumn('communes', $communeNameColumn)
                ? Commune::select('id', 'ville_id')->selectRaw($communeNameColumn . ' as libelle')->orderBy($communeNameColumn)->get()
                : collect(),
            'selectedUsers' => $this->selectedUsers($filters),
            'previewUsers' => $service->audienceQuery($audienceType, $filters)
                ->paginate(20, ['*'], 'users_page')
                ->appends($request->except('users_page')),
            'previewCount' => $service->countAudience($audienceType, $filters),
        ];
    }

    private function notificationUserFilterColumns(): array
    {
        return collect(['statut', 'ville_id', 'commune_id'])
            ->mapWithKeys(fn ($column) => [$column => Schema::hasColumn('users', $column)])
            ->all();
    }

    private function notificationFcmUsers()
    {
        $columns = collect(['id', 'fcm_token'])
            ->merge(collect(['nom', 'prenoms', 'name', 'email', 'telephone', 'mobile'])
                ->filter(fn ($column) => Schema::hasColumn('users', $column)))
            ->unique()
            ->values()
            ->all();

        $orderColumn = collect(['nom', 'name', 'telephone', 'mobile', 'email'])
            ->first(fn ($column) => Schema::hasColumn('users', $column));

        $query = User::select($columns)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');

        if ($orderColumn) {
            $query->orderBy($orderColumn);
        }

        return $query->orderBy('id')->get();
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

    private function sendDueCampaigns(NotificationCampaignService $service): void
    {
        $service->sendDueCampaigns(50);
    }

    private function allowedAudienceTypes(): array
    {
        $types = [
            NotificationCampaign::AUDIENCE_ALL_USERS,
            NotificationCampaign::AUDIENCE_SELECTED_USERS,
            NotificationCampaign::AUDIENCE_ALERT_EXPIRATION,
        ];

        if (! $this->isCallCenter()) {
            $types = array_merge($types, $this->professionalAudienceTypes(), $this->lavageAudienceTypes(), $this->stationAudienceTypes());
        }

        return $types;
    }

    private function selectedAudienceTypes(): array
    {
        $types = [NotificationCampaign::AUDIENCE_SELECTED_USERS => 'user_ids'];

        if (! $this->isCallCenter()) {
            $types += [
                NotificationCampaign::AUDIENCE_SELECTED_PROFESSIONALS => 'professional_ids',
                NotificationCampaign::AUDIENCE_SELECTED_LAVAGES => 'lavage_ids',
                NotificationCampaign::AUDIENCE_SELECTED_STATIONS => 'station_ids',
            ];
        }

        return $types;
    }

    private function professionalAudienceTypes(): array
    {
        return [NotificationCampaign::AUDIENCE_ALL_PROFESSIONALS, NotificationCampaign::AUDIENCE_SELECTED_PROFESSIONALS];
    }

    private function lavageAudienceTypes(): array
    {
        return [NotificationCampaign::AUDIENCE_ALL_LAVAGES, NotificationCampaign::AUDIENCE_SELECTED_LAVAGES];
    }

    private function stationAudienceTypes(): array
    {
        return [NotificationCampaign::AUDIENCE_ALL_STATIONS, NotificationCampaign::AUDIENCE_SELECTED_STATIONS];
    }

    private function audienceLabels(): array
    {
        $labels = [
            NotificationCampaign::AUDIENCE_ALL_USERS => 'Tous les usagers avec FCM',
            NotificationCampaign::AUDIENCE_SELECTED_USERS => 'Un ou plusieurs usagers',
            NotificationCampaign::AUDIENCE_ALERT_EXPIRATION => 'Usagers avec alerte à échéance',
        ];

        if (! $this->isCallCenter()) {
            $labels += [
                NotificationCampaign::AUDIENCE_ALL_PROFESSIONALS => 'Tous les professionnels avec FCM',
                NotificationCampaign::AUDIENCE_SELECTED_PROFESSIONALS => 'Un ou plusieurs professionnels',
                NotificationCampaign::AUDIENCE_ALL_LAVAGES => 'Tous les lavages avec FCM',
                NotificationCampaign::AUDIENCE_SELECTED_LAVAGES => 'Un ou plusieurs lavages',
                NotificationCampaign::AUDIENCE_ALL_STATIONS => 'Toutes les stations-service avec FCM',
                NotificationCampaign::AUDIENCE_SELECTED_STATIONS => 'Une ou plusieurs stations-service',
            ];
        }

        return $labels;
    }

    private function hydrateLogRecipients($logs): void
    {
        if (! Schema::hasColumn('notification_campaign_logs', 'recipient_type')) {
            return;
        }

        $tables = [
            'professional' => ['professionnels', ['nom', 'prenoms', 'mobile', 'email']],
            'lavage' => ['lavages', ['first_name', 'last_name', 'mobile', 'email']],
            'station' => ['station_services', ['name', 'mobile', 'email']],
        ];

        foreach ($tables as $type => [$table, $columns]) {
            $ids = $logs->where('recipient_type', $type)->pluck('recipient_id')->filter()->unique();
            if ($ids->isEmpty() || ! Schema::hasTable($table)) {
                continue;
            }

            $availableColumns = collect($columns)->filter(fn ($column) => Schema::hasColumn($table, $column))->all();
            $recipients = DB::table($table)->whereIn('id', $ids)->get(array_merge(['id'], $availableColumns))->keyBy('id');
            $logs->where('recipient_type', $type)->each(function ($log) use ($recipients) {
                $log->recipient_display = $recipients->get($log->recipient_id);
            });
        }
    }
}
