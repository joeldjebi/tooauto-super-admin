<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\NotificationCampaign;
use App\Models\NotificationCampaignLog;
use App\Models\Type_alert;
use App\Models\Ville;
use App\Services\NotificationCampaignService;
use App\Services\SpreadsheetReader;
use App\Support\SchemaCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $importPreview = $this->importPreview();

        // Seuls les destinataires deja selectionnes (formulaire ou import en cours) sont charges :
        // les autres arrivent par la recherche (route recipients), la liste complete pouvant compter
        // des milliers d'entrees.
        $requestedFilters = $request->old('filters', $request->input('filters', []));
        $selectionOptions = collect();
        foreach ($this->selectedAudienceTypes() as $audienceType => $filterKey) {
            $ids = collect(is_array($requestedFilters) ? ($requestedFilters[$filterKey] ?? []) : []);

            foreach ($importPreview['rows'] ?? [] as $row) {
                if (($row['campaign']['audience_type'] ?? null) === $audienceType) {
                    $ids = $ids->merge($row['campaign']['audience_filters'][$filterKey] ?? []);
                }
            }

            $selectionOptions = $selectionOptions->merge(
                $this->recipientsByIds($service, $audienceType, $ids->all())->map(function ($recipient) use ($audienceType, $filterKey) {
                    $recipient->audience_type = $audienceType;
                    $recipient->filter_key = $filterKey;
                    return $recipient;
                })
            );
        }

        return view('notification_send.programmes', [
            'title' => 'Notifications programmées',
            'menu' => $this->isCallCenter() ? 'call-center-notification-programmes' : 'notification-programmes',
            'isCallCenter' => $this->isCallCenter(),
            'campaigns' => $query->paginate(20)->appends($request->query()),
            'selectionOptions' => $selectionOptions,
            'selectionFilterKeys' => $this->selectedAudienceTypes(),
            'recipientsRoute' => $this->routeName('notification-send.recipients'),
            'audienceLabels' => $this->audienceLabels(),
            'indexRoute' => $this->routeName('notification-send.index'),
            'createRoute' => $this->routeName('notification-send.create'),
            'storeRoute' => $this->routeName('notification-send.store'),
            'importRoute' => $this->routeName('notification-send.import'),
            'importTemplateRoute' => $this->routeName('notification-send.import-template'),
            'importConfirmRoute' => $this->routeName('notification-send.import-confirm'),
            'importCancelRoute' => $this->routeName('notification-send.import-cancel'),
            'importRowUpdateRouteName' => $this->routeName('notification-send.import-row-update'),
            'importRowRemoveRouteName' => $this->routeName('notification-send.import-row-remove'),
            'importPreview' => $importPreview,
            'programmesRoute' => $this->routeName('notification-send.programmes'),
            'logsRoute' => $this->routeName('notification-send.logs'),
            'sendNowRouteName' => $this->routeName('notification-send.send-now'),
            'cancelRouteName' => $this->routeName('notification-send.cancel'),
        ]);
    }

    /** Recherche de destinataires (avec token FCM) pour les selecteurs, limitee a 30 resultats. */
    public function recipients(Request $request, NotificationCampaignService $service)
    {
        $this->authorizeAccess();

        $audienceType = (string) $request->input('audience');
        if (! array_key_exists($audienceType, $this->selectedAudienceTypes())) {
            return response()->json([]);
        }

        $query = DB::query()->fromSub($service->audienceQuery($audienceType), 'recipients');
        $probe = (clone $query)->first();
        if (! $probe) {
            return response()->json([]);
        }

        $keyword = trim((string) $request->input('q', ''));
        if ($keyword !== '') {
            $searchColumns = array_values(array_intersect(
                ['nom', 'prenoms', 'name', 'first_name', 'last_name', 'telephone', 'mobile', 'email'],
                array_keys((array) $probe)
            ));
            $query->where(function ($where) use ($keyword, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $where->orWhere($column, 'like', '%' . $keyword . '%');
                }
                if (ctype_digit(ltrim($keyword, '#'))) {
                    $where->orWhere('recipient_id', (int) ltrim($keyword, '#'));
                }
            });
        }

        return response()->json(
            $query->orderBy('recipient_id')
                ->limit(60)
                ->get()
                ->unique('recipient_id')
                ->take(30)
                ->map(fn ($recipient) => ['id' => (int) $recipient->recipient_id, 'label' => $this->recipientLabel($recipient)])
                ->values()
        );
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

    public function import(Request $request, NotificationCampaignService $service, SpreadsheetReader $reader)
    {
        $this->authorizeAccess();

        $request->validate([
            'import_file' => 'required|file|max:5120',
        ], [
            'import_file.required' => 'Choisissez un fichier .xlsx ou .csv à importer.',
            'import_file.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        try {
            $rows = $reader->read($request->file('import_file'));
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['import_file' => $exception->getMessage()]);
        }

        $header = array_shift($rows) ?? [];
        $columns = $this->importColumns($header);
        foreach (['title' => 'titre', 'body' => 'message', 'scheduled_at' => 'date_programmation', 'audience_type' => 'destinataires'] as $field => $label) {
            if (! isset($columns[$field])) {
                return back()->withErrors(['import_file' => "Colonne obligatoire manquante : {$label}. Téléchargez le modèle pour voir le format attendu."]);
            }
        }

        $previewRows = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $values = collect($columns)->map(fn ($position) => is_string($row[$position] ?? null) ? trim($row[$position]) : ($row[$position] ?? null));

            if ($values->filter(fn ($value) => $value !== null && $value !== '')->isEmpty()) {
                continue;
            }

            $previewRows[] = $this->buildImportRow($line, $values->all(), $service);
        }

        if (empty($previewRows)) {
            return back()->withErrors(['import_file' => 'Le fichier ne contient aucune notification à importer.']);
        }

        if (count($previewRows) > 500) {
            return back()->withErrors(['import_file' => 'Maximum 500 notifications par import.']);
        }

        // L'aperçu est conservé dans un fichier temporaire (seule la clé est en session) jusqu'à validation.
        $this->forgetImportPreview();
        $token = (string) \Illuminate\Support\Str::uuid();
        Storage::disk('local')->put($this->importPreviewPath($token), json_encode([
            'file_name' => $request->file('import_file')->getClientOriginalName(),
            'actor' => $this->actorType() . ':' . $this->actorId(),
            'rows' => $previewRows,
        ]));
        session()->put('notification_import_token', $token);

        return redirect()->route($this->routeName('notification-send.programmes'));
    }

    public function confirmImport()
    {
        $this->authorizeAccess();

        $preview = $this->importPreview();
        if (! $preview) {
            return back()->withErrors(['import_file' => 'Aucun import en attente. Chargez à nouveau le fichier.']);
        }

        $invalidLines = collect($preview['rows'])->filter(fn ($row) => ! empty($row['errors']))->pluck('line');
        if ($invalidLines->isNotEmpty()) {
            return back()->withErrors(['import_file' => 'Corrigez les lignes en erreur (' . $invalidLines->implode(', ') . ') puis rechargez le fichier.']);
        }

        // La date a pu passer entre l'aperçu et la validation.
        $pastLines = collect($preview['rows'])
            ->filter(fn ($row) => \Carbon\Carbon::parse($row['campaign']['scheduled_at'])->lte(now()))
            ->pluck('line');
        if ($pastLines->isNotEmpty()) {
            return back()->withErrors(['import_file' => 'Date de programmation dépassée pour les lignes ' . $pastLines->implode(', ') . '. Corrigez le fichier puis rechargez-le.']);
        }

        DB::transaction(function () use ($preview) {
            foreach ($preview['rows'] as $row) {
                NotificationCampaign::create($row['campaign'] + [
                    'status' => NotificationCampaign::STATUS_SCHEDULED,
                    'created_by' => $this->actorId(),
                    'created_by_type' => $this->actorType(),
                ]);
            }
        });

        $this->forgetImportPreview();

        session()->flash('type', 'alert-success');
        session()->flash('message', count($preview['rows']) . ' notification(s) programmée(s) depuis le fichier.');

        return redirect()->route($this->routeName('notification-send.programmes'));
    }

    public function updateImportRow(Request $request, int $line, NotificationCampaignService $service)
    {
        $this->authorizeAccess();

        $preview = $this->importPreview();
        $index = $preview ? collect($preview['rows'])->search(fn ($row) => $row['line'] === $line) : false;
        if ($index === false) {
            return back()->withErrors(['import_file' => 'Ligne introuvable dans l\'import en attente. Chargez à nouveau le fichier.']);
        }

        $preview['rows'][$index] = $this->buildImportRow($line, $request->only([
            'title', 'body', 'scheduled_at', 'audience_type', 'ids', 'image_url', 'action_url',
        ]), $service);
        $this->saveImportPreview($preview);

        $errors = $preview['rows'][$index]['errors'];
        session()->flash('type', empty($errors) ? 'alert-success' : 'alert-danger');
        session()->flash('message', empty($errors) ? "Ligne {$line} corrigée." : "Ligne {$line} enregistrée, mais elle contient encore des erreurs.");

        return redirect()->to(route($this->routeName('notification-send.programmes')) . '#notification-import-preview');
    }

    public function removeImportRow(int $line)
    {
        $this->authorizeAccess();

        $preview = $this->importPreview();
        if (! $preview) {
            return back()->withErrors(['import_file' => 'Aucun import en attente. Chargez à nouveau le fichier.']);
        }

        $preview['rows'] = collect($preview['rows'])->reject(fn ($row) => $row['line'] === $line)->values()->all();
        if (empty($preview['rows'])) {
            return $this->cancelImport();
        }

        $this->saveImportPreview($preview);

        session()->flash('type', 'alert-success');
        session()->flash('message', "Ligne {$line} retirée de l'import.");

        return redirect()->to(route($this->routeName('notification-send.programmes')) . '#notification-import-preview');
    }

    public function cancelImport()
    {
        $this->authorizeAccess();

        $this->forgetImportPreview();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Import annulé.');

        return redirect()->route($this->routeName('notification-send.programmes'));
    }

    public function importTemplate()
    {
        $this->authorizeAccess();

        $rows = [
            ['titre', 'message', 'date_programmation', 'destinataires', 'ids', 'image_url', 'lien_action'],
            ['Rappel vidange', 'Pensez à faire la vidange de votre véhicule.', now()->addDay()->format('d/m/Y') . ' 09:00', NotificationCampaign::AUDIENCE_ALL_USERS, '', '', ''],
            ['Offre spéciale', 'Profitez de -20% ce week-end.', now()->addDays(2)->format('d/m/Y') . ' 10:30', NotificationCampaign::AUDIENCE_SELECTED_USERS, '12,45,78', '', 'https://tooauto.com'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, 'modele_notifications_programmees.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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

        $hasRecipientColumns = SchemaCache::hasColumn('notification_campaign_logs', 'recipient_type');
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
                            if (SchemaCache::hasColumn('users', $column)) {
                                $userQuery->orWhere($column, 'like', $keyword);
                            }
                        }
                    });

                if (SchemaCache::hasColumn('notification_campaign_logs', 'recipient_id') && is_numeric(trim($keyword, '%'))) {
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

    private function buildImportRow(int $line, array $values, NotificationCampaignService $service): array
    {
        $audienceType = $this->importAudienceType((string) $this->importValue($values, 'audience_type'));
        $scheduledAt = $this->importDate($this->importValue($values, 'scheduled_at'));

        $validator = \Illuminate\Support\Facades\Validator::make([
            'titre' => (string) $this->importValue($values, 'title'),
            'message' => (string) $this->importValue($values, 'body'),
            'image_url' => $this->importValue($values, 'image_url') ?: null,
            'lien_action' => $this->importValue($values, 'action_url') ?: null,
        ], [
            'titre' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'lien_action' => 'nullable|url|max:500',
        ], [
            'required' => ':attribute obligatoire.',
            'max' => ':attribute trop long (:max caractères max).',
            'url' => ':attribute doit être une URL valide (https://...).',
        ]);

        $rowErrors = $validator->errors()->all();

        if (! $audienceType) {
            $rowErrors[] = 'destinataires « ' . $this->importValue($values, 'audience_type') . ' » inconnu.';
        }

        if (! $scheduledAt) {
            $rowErrors[] = 'date_programmation invalide (format attendu : JJ/MM/AAAA HH:MM).';
        } elseif ($scheduledAt->lte(now())) {
            $rowErrors[] = 'date_programmation doit être dans le futur.';
        }

        $filters = [];
        $selectionKey = $audienceType ? ($this->selectedAudienceTypes()[$audienceType] ?? null) : null;
        if ($selectionKey) {
            $filters = $this->cleanFilters([$selectionKey => [(string) $this->importValue($values, 'ids')]], $audienceType);
            $ids = $filters[$selectionKey];

            if (empty($ids)) {
                $rowErrors[] = 'ids obligatoires pour ce type de destinataires.';
            } else {
                // On ne verifie que les ids de la ligne, sans charger tous les destinataires.
                $validIds = $this->recipientsByIds($service, $audienceType, $ids)
                    ->pluck('recipient_id')
                    ->map(fn ($id) => (int) $id)
                    ->flip();
                $unknownIds = collect($ids)->reject(fn ($id) => $validIds->has((int) $id));
                if ($unknownIds->isNotEmpty()) {
                    $rowErrors[] = 'ids introuvables ou sans token FCM : ' . $unknownIds->implode(', ') . '.';
                }
            }
        }

        return [
            'line' => $line,
            'errors' => $rowErrors,
            // Valeurs d'origine pour pré-remplir le formulaire de correction quand elles n'ont pas pu être interprétées.
            'raw' => [
                'scheduled_at' => (string) $this->importValue($values, 'scheduled_at'),
                'ids' => (string) $this->importValue($values, 'ids'),
            ],
            'campaign' => [
                'title' => html_entity_decode((string) $this->importValue($values, 'title')),
                'body' => html_entity_decode((string) $this->importValue($values, 'body')),
                'image_url' => $this->importValue($values, 'image_url') ?: null,
                'action_url' => $this->importValue($values, 'action_url') ?: null,
                'audience_type' => $audienceType ?: (string) $this->importValue($values, 'audience_type'),
                'audience_filters' => $filters,
                'scheduled_at' => $scheduledAt?->format('Y-m-d H:i:s'),
            ],
        ];
    }

    private function importValue(array $values, string $key)
    {
        $value = $values[$key] ?? null;

        return is_string($value) ? trim($value) : $value;
    }

    private function importPreviewPath(string $token): string
    {
        return 'notification-imports/' . $token . '.json';
    }

    private function importPreview(): ?array
    {
        $token = session('notification_import_token');
        if (! $token || ! Storage::disk('local')->exists($this->importPreviewPath($token))) {
            return null;
        }

        $preview = json_decode(Storage::disk('local')->get($this->importPreviewPath($token)), true);

        // Un aperçu n'est valable que pour la personne qui l'a chargé.
        if (! is_array($preview) || ($preview['actor'] ?? null) !== $this->actorType() . ':' . $this->actorId()) {
            return null;
        }

        return $preview;
    }

    private function saveImportPreview(array $preview): void
    {
        Storage::disk('local')->put($this->importPreviewPath(session('notification_import_token')), json_encode($preview));
    }

    private function forgetImportPreview(): void
    {
        $token = session()->pull('notification_import_token');
        if ($token) {
            Storage::disk('local')->delete($this->importPreviewPath($token));
        }
    }

    private function importColumns(array $header): array
    {
        $aliases = [
            'title' => ['titre', 'title'],
            'body' => ['message', 'body', 'contenu'],
            'scheduled_at' => ['date_programmation', 'date', 'scheduled_at', 'date_envoi'],
            'audience_type' => ['destinataires', 'cible', 'audience', 'audience_type'],
            'ids' => ['ids', 'id', 'destinataires_ids'],
            'image_url' => ['image_url', 'image'],
            'action_url' => ['lien_action', 'action_url', 'lien'],
        ];

        $columns = [];
        foreach ($header as $position => $name) {
            $normalized = $this->normalizeImportKey((string) $name);
            foreach ($aliases as $field => $names) {
                if (! isset($columns[$field]) && in_array($normalized, $names, true)) {
                    $columns[$field] = $position;
                }
            }
        }

        return $columns;
    }

    private function importAudienceType(string $value): ?string
    {
        $normalized = $this->normalizeImportKey($value);

        foreach ($this->audienceLabels() as $type => $label) {
            if ($type === NotificationCampaign::AUDIENCE_ALERT_EXPIRATION) {
                continue;
            }

            if ($normalized === $type || $normalized === $this->normalizeImportKey($label)) {
                return $type;
            }
        }

        return null;
    }

    private function importDate($value): ?\Carbon\Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Date Excel stockée en nombre de jours depuis le 30/12/1899.
        if (is_int($value) || is_float($value)) {
            return \Carbon\Carbon::create(1899, 12, 30)->addSeconds((int) round($value * 86400))->seconds(0);
        }

        foreach (['d/m/Y H:i', 'd/m/Y H:i:s', 'd-m-Y H:i', 'Y-m-d H:i', 'Y-m-d H:i:s', 'Y-m-d\TH:i'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            $dateErrors = \DateTime::getLastErrors();
            if ($date && (! $dateErrors || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))) {
                return \Carbon\Carbon::instance($date)->seconds(0);
            }
        }

        return null;
    }

    private function normalizeImportKey(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(\Illuminate\Support\Str::ascii(trim($value)))), '_');
    }

    private function audienceViewData(Request $request, NotificationCampaignService $service): array
    {
        $audienceType = $request->input('audience_type', NotificationCampaign::AUDIENCE_ALL_USERS);
        if (! in_array($audienceType, [NotificationCampaign::AUDIENCE_ALL_USERS, NotificationCampaign::AUDIENCE_SELECTED_USERS, NotificationCampaign::AUDIENCE_ALERT_EXPIRATION], true)) {
            $audienceType = NotificationCampaign::AUDIENCE_ALL_USERS;
        }
        $filters = $this->cleanFilters($request->input('filters', []), $audienceType);
        $communeNameColumn = SchemaCache::hasTable('communes') && SchemaCache::hasColumn('communes', 'libelle') ? 'libelle' : 'nom';

        return [
            'isCallCenter' => $this->isCallCenter(),
            'audienceType' => $audienceType,
            'filters' => $filters,
            'typeAlerts' => Type_alert::orderBy('libelle')->get(['id', 'libelle']),
            'userFilterColumns' => $this->notificationUserFilterColumns(),
            'villes' => SchemaCache::hasTable('villes') ? Ville::orderBy('libelle')->get(['id', 'libelle']) : collect(),
            'communes' => SchemaCache::hasTable('communes') && SchemaCache::hasColumn('communes', $communeNameColumn)
                ? Commune::select('id', 'ville_id')->selectRaw($communeNameColumn . ' as libelle')->orderBy($communeNameColumn)->get()
                : collect(),
            'previewUsers' => $service->audienceQuery($audienceType, $filters)
                ->paginate(20, ['*'], 'users_page')
                ->appends($request->except('users_page')),
            'previewCount' => $service->countAudience($audienceType, $filters),
        ];
    }

    private function notificationUserFilterColumns(): array
    {
        return collect(['statut', 'ville_id', 'commune_id'])
            ->mapWithKeys(fn ($column) => [$column => SchemaCache::hasColumn('users', $column)])
            ->all();
    }


    private function recipientsByIds(NotificationCampaignService $service, string $audienceType, array $ids)
    {
        $ids = collect($ids)->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::query()
            ->fromSub($service->audienceQuery($audienceType), 'recipients')
            ->whereIn('recipient_id', $ids->all())
            ->orderBy('recipient_id')
            ->get()
            ->unique('recipient_id')
            ->values();
    }

    private function recipientLabel(object $recipient): string
    {
        $name = trim(($recipient->nom ?? $recipient->first_name ?? $recipient->name ?? '') . ' ' . ($recipient->prenoms ?? $recipient->last_name ?? '')) ?: 'Destinataire';
        $contact = $recipient->telephone ?? $recipient->mobile ?? $recipient->email ?? '';

        return '#' . $recipient->recipient_id . ' - ' . $name . ($contact ? ' - ' . $contact : '');
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
        if (! SchemaCache::hasColumn('notification_campaign_logs', 'recipient_type')) {
            return;
        }

        $tables = [
            'professional' => ['professionnels', ['nom', 'prenoms', 'mobile', 'email']],
            'lavage' => ['lavages', ['first_name', 'last_name', 'mobile', 'email']],
            'station' => ['station_services', ['name', 'mobile', 'email']],
        ];

        foreach ($tables as $type => [$table, $columns]) {
            $ids = $logs->where('recipient_type', $type)->pluck('recipient_id')->filter()->unique();
            if ($ids->isEmpty() || ! SchemaCache::hasTable($table)) {
                continue;
            }

            $availableColumns = collect($columns)->filter(fn ($column) => SchemaCache::hasColumn($table, $column))->all();
            $recipients = DB::table($table)->whereIn('id', $ids)->get(array_merge(['id'], $availableColumns))->keyBy('id');
            $logs->where('recipient_type', $type)->each(function ($log) use ($recipients) {
                $log->recipient_display = $recipients->get($log->recipient_id);
            });
        }
    }
}
