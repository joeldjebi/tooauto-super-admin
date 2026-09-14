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
    $audienceLabels = [
        'all_users' => 'Tous les users',
        'selected_users' => 'Users sélectionnés',
        'alert_expiration' => 'Alertes à échéance',
    ];
    $formAudienceType = old('audience_type', 'all_users');
    $selectedFormUserIds = collect(old('filters.user_ids', []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<style>
    .notification-user-dropdown .dropdown-menu {
        width: 100%;
        max-height: 320px;
        overflow-y: auto;
    }
    .notification-user-dropdown .dropdown-item {
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
                                <option value="all_users" {{ $formAudienceType === 'all_users' ? 'selected' : '' }}>Tous les usagers avec FCM</option>
                                <option value="selected_users" {{ $formAudienceType === 'selected_users' ? 'selected' : '' }}>Un ou plusieurs usagers</option>
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

                    <div class="form-group" id="notification-users-group">
                        <label>Usagers avec FCM</label>
                        <div class="dropdown notification-user-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle btn-block text-left" type="button" id="notification-users-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Sélectionner les usagers
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="notification-users-button">
                                <input type="text" class="form-control form-control-sm mb-2" id="notification-users-search" placeholder="Rechercher un usager">
                                <div class="d-flex mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="notification-users-check-visible">Tout cocher</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="notification-users-clear">Vider</button>
                                </div>
                                @forelse($fcmUsers as $userOption)
                                    @php
                                        $userName = trim(($userOption->nom ?? '') . ' ' . ($userOption->prenoms ?? ''));
                                        $userName = $userName ?: ($userOption->name ?? 'Usager');
                                        $userContact = $userOption->telephone ?? $userOption->mobile ?? $userOption->email ?? '';
                                        $userLabel = '#' . $userOption->id . ' - ' . $userName . ($userContact ? ' - ' . $userContact : '');
                                    @endphp
                                    <label class="dropdown-item mb-1 notification-user-option" data-label="{{ \Illuminate\Support\Str::lower($userLabel) }}">
                                        <input type="checkbox" name="filters[user_ids][]" value="{{ $userOption->id }}" class="mr-2 notification-user-checkbox" {{ in_array((string) $userOption->id, $selectedFormUserIds, true) ? 'checked' : '' }}>
                                        {{ $userLabel }}
                                    </label>
                                @empty
                                    <div class="text-muted small">Aucun usager avec FCM trouvé.</div>
                                @endforelse
                            </div>
                        </div>
                        <small class="form-text text-muted">Cliquez les usagers à notifier. La liste contient uniquement les usagers qui ont un token FCM.</small>
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
    (function () {
        var audienceSelect = document.getElementById('notification-audience-type');
        var usersGroup = document.getElementById('notification-users-group');
        var usersButton = document.getElementById('notification-users-button');
        var searchInput = document.getElementById('notification-users-search');
        var checkVisibleButton = document.getElementById('notification-users-check-visible');
        var clearButton = document.getElementById('notification-users-clear');

        function options() {
            return Array.prototype.slice.call(document.querySelectorAll('.notification-user-option'));
        }

        function checkboxes() {
            return Array.prototype.slice.call(document.querySelectorAll('.notification-user-checkbox'));
        }

        function updateAudienceMode() {
            if (!audienceSelect || !usersGroup) {
                return;
            }

            var isSelectedUsers = audienceSelect.value === 'selected_users';
            usersGroup.style.display = isSelectedUsers ? '' : 'none';
            checkboxes().forEach(function (checkbox) {
                checkbox.disabled = !isSelectedUsers;
            });
            updateButtonText();
        }

        function updateButtonText() {
            if (!usersButton) {
                return;
            }

            var selectedCount = checkboxes().filter(function (checkbox) {
                return checkbox.checked && !checkbox.disabled;
            }).length;

            usersButton.textContent = selectedCount > 0
                ? selectedCount + ' usager(s) sélectionné(s)'
                : 'Sélectionner les usagers';
        }

        function filterUsers() {
            var keyword = (searchInput ? searchInput.value : '').toLowerCase().trim();
            options().forEach(function (option) {
                option.style.display = !keyword || option.getAttribute('data-label').indexOf(keyword) !== -1 ? '' : 'none';
            });
        }

        var dropdownMenu = document.querySelector('.notification-user-dropdown .dropdown-menu');
        if (dropdownMenu) {
            dropdownMenu.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        if (audienceSelect) {
            audienceSelect.addEventListener('change', updateAudienceMode);
        }
        if (searchInput) {
            searchInput.addEventListener('keyup', filterUsers);
        }
        if (checkVisibleButton) {
            checkVisibleButton.addEventListener('click', function () {
                options().forEach(function (option) {
                    if (option.style.display !== 'none') {
                        var checkbox = option.querySelector('.notification-user-checkbox');
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
        filterUsers();
    })();
</script>

@include('layouts.footer')
