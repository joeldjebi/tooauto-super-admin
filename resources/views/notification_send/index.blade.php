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
                        <div class="small text-muted">Notification send</div>
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
@endphp

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
    <div class="col-lg-4 col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Ciblage</h4>
                    <span class="badge badge-info">{{ $previewCount }} cible(s)</span>
                </div>

                <form method="GET" action="{{ route($indexRoute) }}">
                    <div class="form-group">
                        <label>Type d'envoi</label>
                        <select name="audience_type" class="form-control" onchange="this.form.submit()">
                            <option value="all_users" {{ $audienceType === 'all_users' ? 'selected' : '' }}>Tous les users</option>
                            <option value="selected_users" {{ $audienceType === 'selected_users' ? 'selected' : '' }}>Un ou quelques users</option>
                            <option value="alert_expiration" {{ $audienceType === 'alert_expiration' ? 'selected' : '' }}>Users avec alerte qui expire</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Recherche user</label>
                        <input type="text" name="filters[keyword]" class="form-control" value="{{ $filters['keyword'] ?? '' }}" placeholder="Nom, téléphone, email">
                    </div>

                    <div class="form-row" data-notification-user-filters>
                        <div class="form-group col-md-4">
                            <label>Statut</label>
                            <select name="filters[statut]" class="form-control" {{ ($userFilterColumns['statut'] ?? false) ? '' : 'disabled' }}>
                                <option value="">Tous</option>
                                <option value="1" {{ ($filters['statut'] ?? '') === '1' ? 'selected' : '' }}>Actifs</option>
                                <option value="0" {{ ($filters['statut'] ?? '') === '0' ? 'selected' : '' }}>Inactifs</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Ville</label>
                            <select name="filters[ville_id]" class="form-control js-ville-select" {{ ($userFilterColumns['ville_id'] ?? false) ? '' : 'disabled' }}>
                                <option value="">Toutes</option>
                                @foreach($villes as $ville)
                                    <option value="{{ $ville->id }}" {{ ($filters['ville_id'] ?? '') == $ville->id ? 'selected' : '' }}>{{ $ville->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Commune</label>
                            <select name="filters[commune_id]" class="form-control js-commune-select" {{ ($userFilterColumns['commune_id'] ?? false) ? '' : 'disabled' }}>
                                <option value="">Toutes</option>
                                @foreach($communes as $commune)
                                    <option value="{{ $commune->id }}" data-ville-id="{{ $commune->ville_id }}" {{ ($filters['commune_id'] ?? '') == $commune->id ? 'selected' : '' }}>{{ $commune->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($audienceType === 'selected_users')
                        <div class="form-group">
                            <label>IDs users manuels</label>
                            <textarea name="filters[user_ids]" class="form-control" rows="2" placeholder="Ex: 12, 44, 81">{{ implode(', ', $filters['user_ids'] ?? []) }}</textarea>
                            <small class="text-muted">Vous pouvez aussi cocher les users dans la prévisualisation.</small>
                        </div>
                    @endif

                    @if($audienceType === 'alert_expiration')
                        <div class="form-group">
                            <label>Type d'alerte</label>
                            <select name="filters[type_alert_id]" class="form-control">
                                <option value="">Toutes</option>
                                @foreach($typeAlerts as $typeAlert)
                                    <option value="{{ $typeAlert->id }}" {{ ($filters['type_alert_id'] ?? '') == $typeAlert->id ? 'selected' : '' }}>{{ $typeAlert->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Échéance</label>
                            <select name="filters[expires_mode]" class="form-control">
                                <option value="in_days" {{ ($filters['expires_mode'] ?? 'in_days') === 'in_days' ? 'selected' : '' }}>Expire dans X jours</option>
                                <option value="today" {{ ($filters['expires_mode'] ?? '') === 'today' ? 'selected' : '' }}>Expire aujourd'hui</option>
                                <option value="between" {{ ($filters['expires_mode'] ?? '') === 'between' ? 'selected' : '' }}>Entre deux dates</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Jours</label>
                                <input type="number" min="0" name="filters[days]" class="form-control" value="{{ $filters['days'] ?? 7 }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Du</label>
                                <input type="date" name="filters[date_from]" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Au</label>
                                <input type="date" name="filters[date_to]" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-outline-primary btn-block">Prévisualiser</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Programmer la notification</h4>
                <p class="text-muted">Le formulaire de notification se trouve sur la page des notifs programmées.</p>
                <form method="GET" action="{{ route($programmesRoute) }}" id="notification-create-form">
                    <input type="hidden" name="audience_type" value="{{ $audienceType }}">
                    @foreach($filters as $key => $value)
                        @continue($key === 'user_ids')
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="filters[{{ $key }}][]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                        @endif
                    @endforeach

                    <div id="selected-users-holder"></div>
                    <button type="submit" class="btn btn-primary btn-block">Ouvrir les notifs programmées</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Prévisualisation</h4>
                    <a href="{{ route($logsRoute) }}" class="btn btn-outline-info btn-sm">Logs</a>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Page {{ $previewUsers->currentPage() }} / {{ $previewUsers->lastPage() }}</small>
                    <small class="text-muted">{{ $previewUsers->firstItem() ?? 0 }}-{{ $previewUsers->lastItem() ?? 0 }} sur {{ $previewUsers->total() }}</small>
                </div>
                <form method="GET" action="{{ route($indexRoute) }}" id="preview-table-filter-form">
                    <input type="hidden" name="audience_type" value="{{ $audienceType }}">
                    @foreach($filters as $key => $value)
                        @continue(in_array($key, ['user_id', 'keyword', 'fcm_token'], true))
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="filters[{{ $key }}][]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                        @endif
                    @endforeach
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                @if($audienceType === 'selected_users')
                                    <th style="width: 50px;">Choix</th>
                                @endif
                                <th>User</th>
                                <th>Contact</th>
                                @if($audienceType === 'alert_expiration')
                                    <th>Alerte</th>
                                @endif
                                <th>Token</th>
                            </tr>
                            <tr>
                                @if($audienceType === 'selected_users')
                                    <th></th>
                                @endif
                                <th>
                                    <input form="preview-table-filter-form" type="number" name="filters[user_id]" class="form-control form-control-sm" value="{{ $filters['user_id'] ?? '' }}" placeholder="ID">
                                </th>
                                <th>
                                    <input form="preview-table-filter-form" type="text" name="filters[keyword]" class="form-control form-control-sm" value="{{ $filters['keyword'] ?? '' }}" placeholder="Nom, téléphone, email">
                                </th>
                                @if($audienceType === 'alert_expiration')
                                    <th></th>
                                @endif
                                <th>
                                    <div class="d-flex">
                                        <input form="preview-table-filter-form" type="text" name="filters[fcm_token]" class="form-control form-control-sm mr-1" value="{{ $filters['fcm_token'] ?? '' }}" placeholder="Token">
                                        <button form="preview-table-filter-form" type="submit" class="btn btn-sm btn-primary">OK</button>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($previewUsers as $user)
                                <tr>
                                    @if($audienceType === 'selected_users')
                                        <td>
                                            <input type="checkbox" class="js-user-checkbox" value="{{ $user->id }}" {{ in_array($user->id, $filters['user_ids'] ?? []) ? 'checked' : '' }}>
                                        </td>
                                    @endif
                                    <td>
                                        <strong>#{{ $user->id }}</strong>
                                        <div>{{ trim(($user->nom ?? '') . ' ' . ($user->prenoms ?? '')) ?: ($user->name ?? 'Usager') }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $user->telephone ?? $user->mobile ?? '-' }}</div>
                                        <small class="text-muted">{{ $user->email ?? '' }}</small>
                                    </td>
                                    @if($audienceType === 'alert_expiration')
                                        <td>
                                            <strong>#{{ $user->alert_id ?? '-' }}</strong>
                                            <div>Type: {{ $user->alert_type_alert_id ?? '-' }}</div>
                                        </td>
                                    @endif
                                    <td><code>{{ \Illuminate\Support\Str::limit($user->fcm_token, 28) }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $audienceType === 'alert_expiration' ? 4 : ($audienceType === 'selected_users' ? 4 : 3) }}" class="text-center text-muted py-4">Aucune cible trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $previewUsers->links() }}
            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('notification-create-form');
        var holder = document.getElementById('selected-users-holder');

        if (!form || !holder) {
            return;
        }

        function syncCommunes() {
            var wrapper = document.querySelector('[data-notification-user-filters]');
            if (!wrapper) {
                return;
            }

            var villeSelect = wrapper.querySelector('.js-ville-select');
            var communeSelect = wrapper.querySelector('.js-commune-select');
            if (!villeSelect || !communeSelect || villeSelect.disabled || communeSelect.disabled) {
                return;
            }

            var villeId = villeSelect.value;
            var selectedOptionHidden = false;
            Array.prototype.forEach.call(communeSelect.options, function (option) {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                var visible = !villeId || option.getAttribute('data-ville-id') === villeId;
                option.hidden = !visible;
                if (option.selected && !visible) {
                    selectedOptionHidden = true;
                }
            });

            if (selectedOptionHidden) {
                communeSelect.value = '';
            }
        }
        function syncSelectedUsers() {
            holder.innerHTML = '';
            Array.prototype.forEach.call(document.querySelectorAll('.js-user-checkbox:checked'), function (checkbox) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'filters[user_ids][]';
                input.value = checkbox.value;
                holder.appendChild(input);
            });
        }

        var villeSelect = document.querySelector('[data-notification-user-filters] .js-ville-select');
        if (villeSelect) {
            villeSelect.addEventListener('change', syncCommunes);
        }
        syncCommunes();
        Array.prototype.forEach.call(document.querySelectorAll('.js-user-checkbox'), function (checkbox) {
            checkbox.addEventListener('change', syncSelectedUsers);
        });
        syncSelectedUsers();
    })();
</script>

@include('layouts.footer')
