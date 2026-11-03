@include('layouts.header')
@if(!$isCallCenter)
    @include('layouts.menu')
@endif
@include('layouts.fileariane')

@if($isCallCenter)
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <strong>Espace Call Center</strong>
                        <div class="small text-muted">Notifications programmées</div>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('call-center.dashboard') }}" class="btn btn-outline-secondary btn-sm mr-2">Dashboard</a>
                        <form action="{{ route('call-center.logout') }}" method="POST" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@php
    $statusClasses = [
        'draft' => 'badge-secondary',
        'scheduled' => 'badge-warning',
        'sending' => 'badge-primary',
        'sent' => 'badge-success',
        'failed' => 'badge-danger',
        'cancelled' => 'badge-dark',
    ];
    $statusLabels = [
        'draft' => 'Brouillon',
        'scheduled' => 'Programmé',
        'sending' => 'Envoi',
        'sent' => 'Envoyé',
        'failed' => 'Échec',
        'cancelled' => 'Annulé',
    ];
    $formAudienceType = old('audience_type', request('audience_type', 'all_users'));
    $selectedFormIds = collect(old('filters', request('filters', [])))
        ->map(fn ($ids) => collect(is_array($ids) ? $ids : [$ids])->map(fn ($id) => (string) $id)->all())
        ->all();
@endphp

<style>
    .notification-recipient-dropdown .dropdown-menu {
        width: 100%;
        max-height: 320px;
        overflow-y: auto;
    }
    .notification-recipient-dropdown .dropdown-item {
        white-space: normal;
    }
</style>

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has('message'))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

@include('notification_send.partials.nav')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if($importPreview)
            @php
                $previewRows = collect($importPreview['rows']);
                $invalidCount = $previewRows->filter(fn ($row) => ! empty($row['errors']))->count();
                $importRecipientOptions = $selectionOptions->groupBy('audience_type')->map(fn ($recipients) => $recipients->map(function ($recipient) {
                    $name = trim(($recipient->nom ?? '') . ' ' . ($recipient->prenoms ?? '')) ?: 'Destinataire';
                    $contact = $recipient->telephone ?? $recipient->mobile ?? $recipient->email ?? '';

                    return ['id' => (int) $recipient->recipient_id, 'label' => '#' . $recipient->recipient_id . ' - ' . $name . ($contact ? ' - ' . $contact : '')];
                })->values());
            @endphp
            <script>window.notificationImportRecipients = @json($importRecipientOptions);</script>
            <div class="card mb-4 border {{ $invalidCount ? 'border-danger' : 'border-success' }}" id="notification-import-preview">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Aperçu de l'import</h4>
                            <div class="small text-muted">{{ $importPreview['file_name'] }}</div>
                        </div>
                        <div>
                            <span class="badge badge-light border">{{ $previewRows->count() }} ligne(s)</span>
                            <span class="badge badge-success">{{ $previewRows->count() - $invalidCount }} valide(s)</span>
                            @if($invalidCount)
                                <span class="badge badge-danger">{{ $invalidCount }} en erreur</span>
                            @endif
                        </div>
                    </div>

                    @if($invalidCount)
                        <div class="alert alert-danger py-2">
                            Corrigez les lignes en rouge avec le bouton « Corriger », retirez-les, ou rechargez un fichier corrigé. Aucune notification n'est créée tant que l'import n'est pas validé.
                        </div>
                    @endif

                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Ligne</th>
                                    <th>Notification</th>
                                    <th>Programmation</th>
                                    <th>Destinataires</th>
                                    <th>Liens</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewRows as $row)
                                    @php
                                        $previewCampaign = $row['campaign'];
                                        $previewIds = collect($previewCampaign['audience_filters'])->flatten()->filter();
                                    @endphp
                                    <tr class="{{ empty($row['errors']) ? '' : 'table-danger' }}">
                                        <td>{{ $row['line'] }}</td>
                                        <td style="min-width: 240px;">
                                            <strong>{{ $previewCampaign['title'] ?: '-' }}</strong>
                                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($previewCampaign['body'], 150) }}</div>
                                        </td>
                                        <td style="min-width: 140px;">
                                            {{ $previewCampaign['scheduled_at'] ? \Carbon\Carbon::parse($previewCampaign['scheduled_at'])->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td style="min-width: 180px;">
                                            {{ $audienceLabels[$previewCampaign['audience_type']] ?? ($previewCampaign['audience_type'] ?: '-') }}
                                            @if($previewIds->isNotEmpty())
                                                <div class="small text-muted" title="{{ $previewIds->implode(', ') }}">
                                                    {{ $previewIds->count() }} id(s) : {{ \Illuminate\Support\Str::limit($previewIds->implode(', '), 60) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="small" style="max-width: 200px; word-break: break-all;">
                                            @if($previewCampaign['image_url'])<div>Image : {{ $previewCampaign['image_url'] }}</div>@endif
                                            @if($previewCampaign['action_url'])<div>Lien : {{ $previewCampaign['action_url'] }}</div>@endif
                                        </td>
                                        <td style="min-width: 200px;">
                                            @if(empty($row['errors']))
                                                <span class="badge badge-success">OK</span>
                                            @else
                                                <ul class="small text-danger pl-3 mb-0">
                                                    @foreach($row['errors'] as $rowError)
                                                        <li>{{ $rowError }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td style="min-width: 150px;">
                                            <button type="button" class="btn btn-sm {{ empty($row['errors']) ? 'btn-outline-primary' : 'btn-primary' }} notification-import-edit-toggle" data-target="notification-import-edit-{{ $row['line'] }}">
                                                {{ empty($row['errors']) ? 'Modifier' : 'Corriger' }}
                                            </button>
                                            <form method="POST" action="{{ route($importRowRemoveRouteName, $row['line']) }}" class="d-inline" onsubmit="return confirm('Retirer la ligne {{ $row['line'] }} de l\'import ?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Retirer</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @php
                                        $editIds = $previewIds->isNotEmpty() ? $previewIds->implode(',') : ($row['raw']['ids'] ?? '');
                                        $editDate = $previewCampaign['scheduled_at'] ? \Carbon\Carbon::parse($previewCampaign['scheduled_at'])->format('Y-m-d\TH:i') : '';
                                    @endphp
                                    <tr id="notification-import-edit-{{ $row['line'] }}" class="bg-light" style="display: none;">
                                        <td colspan="7">
                                            <form method="POST" action="{{ route($importRowUpdateRouteName, $row['line']) }}" class="p-2">
                                                @csrf
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <label class="small mb-1">Titre</label>
                                                        <input type="text" name="title" class="form-control form-control-sm" value="{{ $previewCampaign['title'] }}" maxlength="255">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label class="small mb-1">Date de programmation</label>
                                                        <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm" value="{{ $editDate }}">
                                                        @if(! $editDate && ! empty($row['raw']['scheduled_at']))
                                                            <small class="text-muted">Valeur du fichier : {{ $row['raw']['scheduled_at'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label class="small mb-1">Destinataires</label>
                                                        <select name="audience_type" class="form-control form-control-sm">
                                                            @unless(isset($audienceLabels[$previewCampaign['audience_type']]))
                                                                <option value="">-- Choisir ({{ $previewCampaign['audience_type'] ?: 'vide' }}) --</option>
                                                            @endunless
                                                            @foreach(collect($audienceLabels)->except('alert_expiration') as $value => $label)
                                                                <option value="{{ $value }}" {{ $previewCampaign['audience_type'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="small mb-1">Message</label>
                                                    <textarea name="body" class="form-control form-control-sm" rows="2" maxlength="1000">{{ $previewCampaign['body'] }}</textarea>
                                                </div>
                                                <div class="form-group notification-import-ids">
                                                    <label class="small mb-1">Destinataires sélectionnés <span class="notification-import-ids-count text-muted"></span></label>
                                                    <input type="hidden" name="ids" value="{{ $editIds }}">
                                                    <div class="small text-muted notification-import-ids-none">Pas de sélection nécessaire : la notification part à tous les destinataires de ce type.</div>
                                                    <div class="border rounded bg-white p-2 notification-import-ids-picker">
                                                        <div class="d-flex mb-2">
                                                            <input type="text" class="form-control form-control-sm mr-2 notification-import-ids-search" placeholder="Rechercher (nom, téléphone, email, #id)">
                                                            <button type="button" class="btn btn-sm btn-outline-primary mr-2 text-nowrap notification-import-ids-check">Tout cocher</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary notification-import-ids-clear">Vider</button>
                                                        </div>
                                                        <div class="notification-import-ids-list" style="max-height: 200px; overflow-y: auto;"></div>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <label class="small mb-1">Image URL</label>
                                                        <input type="text" name="image_url" class="form-control form-control-sm" value="{{ $previewCampaign['image_url'] }}" placeholder="https://...">
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label class="small mb-1">Lien d'action</label>
                                                        <input type="text" name="action_url" class="form-control form-control-sm" value="{{ $previewCampaign['action_url'] }}" placeholder="https://...">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-success">Enregistrer la ligne</button>
                                                <button type="button" class="btn btn-sm btn-light notification-import-edit-toggle" data-target="notification-import-edit-{{ $row['line'] }}">Fermer</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex mt-3">
                        <form method="POST" action="{{ route($importConfirmRoute) }}" class="flex-fill mr-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" {{ $invalidCount ? 'disabled' : '' }}>
                                Valider et programmer {{ $previewRows->count() }} notification(s)
                            </button>
                        </form>
                        <form method="POST" action="{{ route($importCancelRoute) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-block">Annuler l'import</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Formulaire notification</h4>
                <form method="POST" action="{{ route($storeRoute) }}" id="notification-program-form">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Titre</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Destinataires</label>
                            <select name="audience_type" id="notification-audience-type" class="form-control">
                                @foreach($audienceLabels as $value => $label)
                                    @continue($value === 'alert_expiration')
                                    <option value="{{ $value }}" {{ $formAudienceType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Date de programmation</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="body" class="form-control" rows="4" required maxlength="1000">{{ old('body') }}</textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Image URL</label>
                            <input type="url" name="image_url" class="form-control" value="{{ old('image_url') }}" placeholder="https://...">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Lien d'action</label>
                            <input type="url" name="action_url" class="form-control" value="{{ old('action_url') }}" placeholder="https://...">
                        </div>
                    </div>

                    <div class="form-group" id="notification-recipients-group">
                        <label>Destinataires avec FCM</label>
                        <div class="dropdown notification-recipient-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle btn-block text-left" type="button" id="notification-recipients-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Sélectionner les destinataires
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="notification-recipients-button">
                                <input type="text" class="form-control form-control-sm mb-2" id="notification-recipients-search" placeholder="Rechercher un destinataire">
                                <div class="d-flex mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="notification-recipients-check-visible">Tout cocher</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="notification-recipients-clear">Vider</button>
                                </div>
                                @forelse($selectionOptions as $recipientOption)
                                    @php
                                        $recipientName = trim(($recipientOption->nom ?? '') . ' ' . ($recipientOption->prenoms ?? '')) ?: 'Destinataire';
                                        $recipientContact = $recipientOption->telephone ?? $recipientOption->mobile ?? $recipientOption->email ?? '';
                                        $recipientLabel = '#' . $recipientOption->recipient_id . ' - ' . $recipientName . ($recipientContact ? ' - ' . $recipientContact : '');
                                        $selectedIds = $selectedFormIds[$recipientOption->filter_key] ?? [];
                                    @endphp
                                    <label class="dropdown-item mb-1 notification-recipient-option" data-audience="{{ $recipientOption->audience_type }}" data-label="{{ \Illuminate\Support\Str::lower($recipientLabel) }}">
                                        <input type="checkbox" name="filters[{{ $recipientOption->filter_key }}][]" value="{{ $recipientOption->recipient_id }}" class="mr-2 notification-recipient-checkbox" {{ in_array((string) $recipientOption->recipient_id, $selectedIds, true) ? 'checked' : '' }}>
                                        {{ $recipientLabel }}
                                    </label>
                                @empty
                                    <div class="text-muted small">Aucun destinataire avec FCM trouvé.</div>
                                @endforelse
                            </div>
                        </div>
                        <small class="form-text text-muted">La liste contient uniquement les destinataires disposant d'au moins un token FCM.</small>
                    </div>

                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary flex-fill mr-2">Programmer</button>
                        <button type="submit" name="send_now" value="1" class="btn btn-success flex-fill">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Importer un fichier Excel / CSV</h4>
                    <a href="{{ route($importTemplateRoute) }}" class="btn btn-sm btn-outline-secondary">Télécharger le modèle</a>
                </div>
                <form method="POST" action="{{ route($importRoute) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-9">
                            <label>Fichier (.xlsx ou .csv, 5 Mo max)</label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx,.csv" required>
                        </div>
                        <div class="form-group col-md-3">
                            <button type="submit" class="btn btn-primary btn-block">Importer et programmer</button>
                        </div>
                    </div>
                </form>
                <small class="form-text text-muted">
                    Une ligne = une notification. Colonnes : <strong>titre</strong>, <strong>message</strong>,
                    <strong>date_programmation</strong> (JJ/MM/AAAA HH:MM, dans le futur), <strong>destinataires</strong>,
                    ids (obligatoire pour les types « selected_… », séparés par des virgules), image_url, lien_action.
                    Valeurs possibles pour destinataires :
                    @foreach(collect($audienceLabels)->except('alert_expiration') as $value => $label)
                        <code title="{{ $label }}">{{ $value }}</code>{{ $loop->last ? '.' : ',' }}
                    @endforeach
                    Un aperçu de chaque ligne s'affiche avant validation.
                </small>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Filtrer les notifs programmées</h4>
                <form method="GET" action="{{ route($programmesRoute) }}">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Recherche</label>
                            <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Titre, message, erreur">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Statut</label>
                            <select name="status" class="form-control">
                                <option value="">Tous</option>
                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Cible</label>
                            <select name="audience_type" class="form-control">
                                <option value="">Toutes</option>
                                @foreach($audienceLabels as $value => $label)
                                    <option value="{{ $value }}" {{ request('audience_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Date début</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Date fin</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="{{ route($programmesRoute) }}" class="btn btn-light">Réinitialiser</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Notifs programmées</h4>
                    <span class="badge badge-light border">{{ $campaigns->total() }} notification(s)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Notification</th>
                                <th>Cible</th>
                                <th>Programmation</th>
                                <th>Résultat</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                                <tr>
                                    <td style="min-width: 240px;">
                                        <strong>{{ $campaign->title }}</strong>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($campaign->body, 100) }}</div>
                                        @if($campaign->last_error)
                                            <div class="small text-danger">{{ \Illuminate\Support\Str::limit($campaign->last_error, 100) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $audienceLabels[$campaign->audience_type] ?? $campaign->audience_type }}</td>
                                    <td style="min-width: 180px;">
                                        <div>Programmé {{ optional($campaign->scheduled_at)->format('d/m/Y H:i') ?? '-' }}</div>
                                        <small class="text-muted">Créé {{ optional($campaign->created_at)->format('d/m/Y H:i') }}</small>
                                        @if($campaign->sent_at)
                                            <div><small class="text-success">Envoyé {{ optional($campaign->sent_at)->format('d/m/Y H:i') }}</small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $campaign->total_targets }}</span>
                                        <span class="badge badge-success">{{ $campaign->success_count }} ok</span>
                                        <span class="badge badge-danger">{{ $campaign->failure_count }} ko</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClasses[$campaign->status] ?? 'badge-secondary' }}">{{ $statusLabels[$campaign->status] ?? $campaign->status }}</span>
                                    </td>
                                    <td style="min-width: 220px;">
                                        <a href="{{ route($logsRoute, ['notification_campaign_id' => $campaign->id]) }}" class="btn btn-sm btn-outline-info">Logs</a>
                                        @if(in_array($campaign->status, ['draft', 'scheduled', 'failed', 'cancelled'], true))
                                            <form method="POST" action="{{ route($sendNowRouteName, $campaign->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Envoyer</button>
                                            </form>
                                        @endif
                                        @if($campaign->status === 'scheduled')
                                            <form method="POST" action="{{ route($cancelRouteName, $campaign->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">Annuler</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Aucune notification trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.notification-import-edit-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            var row = document.getElementById(button.getAttribute('data-target'));
            if (row) {
                row.style.display = row.style.display === 'none' ? '' : 'none';
            }
        });
    });

    document.querySelectorAll('.notification-import-ids').forEach(function (group) {
        var form = group.closest('form');
        var audienceSelect = form.querySelector('select[name="audience_type"]');
        var hiddenInput = group.querySelector('input[name="ids"]');
        var picker = group.querySelector('.notification-import-ids-picker');
        var none = group.querySelector('.notification-import-ids-none');
        var list = group.querySelector('.notification-import-ids-list');
        var search = group.querySelector('.notification-import-ids-search');
        var count = group.querySelector('.notification-import-ids-count');
        var selected = hiddenInput.value.split(/[\s,;]+/).filter(Boolean).map(Number).filter(function (id) { return !isNaN(id); });

        function sync() {
            hiddenInput.value = selected.join(',');
            count.textContent = selected.length ? '(' + selected.length + ')' : '';
        }

        function render() {
            var audience = audienceSelect.value;
            var isSelection = audience.indexOf('selected_') === 0;
            picker.style.display = isSelection ? '' : 'none';
            none.style.display = isSelection ? 'none' : '';
            count.style.display = isSelection ? '' : 'none';
            if (!isSelection) {
                return;
            }

            var options = (window.notificationImportRecipients || {})[audience] || [];
            var known = {};
            options.forEach(function (option) { known[option.id] = true; });
            // Les ids du fichier absents de la liste restent visibles pour pouvoir les décocher.
            var unknown = selected.filter(function (id) { return !known[id]; }).map(function (id) {
                return { id: id, label: '#' + id + ' - introuvable ou sans token FCM', unknown: true };
            });
            var keyword = search.value.toLowerCase().trim();

            list.innerHTML = '';
            unknown.concat(options).forEach(function (option) {
                if (keyword && option.label.toLowerCase().indexOf(keyword) === -1) {
                    return;
                }

                var label = document.createElement('label');
                label.className = 'd-block mb-1 small' + (option.unknown ? ' text-danger' : '');
                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'mr-2';
                checkbox.value = option.id;
                checkbox.checked = selected.indexOf(option.id) !== -1;
                checkbox.addEventListener('change', function () {
                    selected = selected.filter(function (id) { return id !== option.id; });
                    if (checkbox.checked) {
                        selected.push(option.id);
                    }
                    sync();
                });
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(option.label));
                list.appendChild(label);
            });

            if (!list.children.length) {
                list.innerHTML = '<div class="small text-muted">Aucun destinataire avec FCM trouvé.</div>';
            }
            sync();
        }

        audienceSelect.addEventListener('change', function () {
            selected = [];
            search.value = '';
            render();
        });
        search.addEventListener('keyup', render);
        group.querySelector('.notification-import-ids-check').addEventListener('click', function () {
            list.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                var id = Number(checkbox.value);
                if (selected.indexOf(id) === -1) {
                    selected.push(id);
                }
            });
            render();
        });
        group.querySelector('.notification-import-ids-clear').addEventListener('click', function () {
            selected = [];
            render();
        });

        render();
    });

    (function () {
        var audienceSelect = document.getElementById('notification-audience-type');
        var recipientsGroup = document.getElementById('notification-recipients-group');
        var recipientsButton = document.getElementById('notification-recipients-button');
        var searchInput = document.getElementById('notification-recipients-search');
        var checkVisibleButton = document.getElementById('notification-recipients-check-visible');
        var clearButton = document.getElementById('notification-recipients-clear');

        function options() {
            return Array.prototype.slice.call(document.querySelectorAll('.notification-recipient-option'));
        }

        function checkboxes() {
            return Array.prototype.slice.call(document.querySelectorAll('.notification-recipient-checkbox'));
        }

        function updateAudienceMode() {
            if (!audienceSelect || !recipientsGroup) {
                return;
            }

            var selectedAudience = audienceSelect.value.indexOf('selected_') === 0 ? audienceSelect.value : '';
            recipientsGroup.style.display = selectedAudience ? '' : 'none';
            options().forEach(function (option) {
                var isCurrent = option.getAttribute('data-audience') === selectedAudience;
                option.style.display = isCurrent ? '' : 'none';
                var checkbox = option.querySelector('.notification-recipient-checkbox');
                if (checkbox) {
                    checkbox.disabled = !isCurrent;
                }
            });
            updateButtonText();
        }

        function updateButtonText() {
            if (!recipientsButton) {
                return;
            }

            var selectedCount = checkboxes().filter(function (checkbox) {
                return checkbox.checked && !checkbox.disabled;
            }).length;

            recipientsButton.textContent = selectedCount > 0
                ? selectedCount + ' destinataire(s) sélectionné(s)'
                : 'Sélectionner les destinataires';
        }

        function filterRecipients() {
            var keyword = (searchInput ? searchInput.value : '').toLowerCase().trim();
            options().forEach(function (option) {
                var currentAudience = audienceSelect && option.getAttribute('data-audience') === audienceSelect.value;
                var matches = !keyword || option.getAttribute('data-label').indexOf(keyword) !== -1;
                option.style.display = currentAudience && matches ? '' : 'none';
            });
        }

        var dropdownMenu = document.querySelector('.notification-recipient-dropdown .dropdown-menu');
        if (dropdownMenu) {
            dropdownMenu.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        if (audienceSelect) {
            audienceSelect.addEventListener('change', function () {
                updateAudienceMode();
                filterRecipients();
            });
        }
        if (searchInput) {
            searchInput.addEventListener('keyup', filterRecipients);
        }
        if (checkVisibleButton) {
            checkVisibleButton.addEventListener('click', function () {
                options().forEach(function (option) {
                    if (option.style.display !== 'none') {
                        var checkbox = option.querySelector('.notification-recipient-checkbox');
                        if (checkbox && !checkbox.disabled) {
                            checkbox.checked = true;
                        }
                    }
                });
                updateButtonText();
            });
        }
        if (clearButton) {
            clearButton.addEventListener('click', function () {
                checkboxes().forEach(function (checkbox) {
                    checkbox.checked = false;
                });
                updateButtonText();
            });
        }

        checkboxes().forEach(function (checkbox) {
            checkbox.addEventListener('change', updateButtonText);
        });

        updateAudienceMode();
        filterRecipients();
    })();
</script>

@include('layouts.footer')
