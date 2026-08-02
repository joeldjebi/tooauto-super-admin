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
                <h4 class="card-title mb-3">Notification</h4>
                <form method="POST" action="{{ route($storeRoute) }}" id="notification-send-form">
                    @csrf
                    <input type="hidden" name="audience_type" value="{{ $audienceType }}">
                    @foreach($filters as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $item)
                                <input type="hidden" name="filters[{{ $key }}][]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                        @endif
                    @endforeach

                    <div id="selected-users-holder"></div>

                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="body" class="form-control" rows="4" required maxlength="1000">{{ old('body') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Image URL</label>
                        <input type="url" name="image_url" class="form-control" value="{{ old('image_url') }}" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label>Lien d'action</label>
                        <input type="url" name="action_url" class="form-control" value="{{ old('action_url') }}" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label>Date de programmation</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                    </div>
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary flex-fill mr-2">Programmer</button>
                        <button type="submit" name="send_now" value="1" class="btn btn-success flex-fill">Envoyer</button>
                    </div>
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
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Campagnes</h4>
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
                                    <td style="min-width: 220px;">
                                        <strong>{{ $campaign->title }}</strong>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($campaign->body, 90) }}</div>
                                        @if($campaign->last_error)
                                            <div class="small text-danger">{{ \Illuminate\Support\Str::limit($campaign->last_error, 90) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $audienceLabels[$campaign->audience_type] ?? $campaign->audience_type }}</td>
                                    <td>
                                        <div>{{ optional($campaign->scheduled_at)->format('d/m/Y H:i') ?? '-' }}</div>
                                        <small class="text-muted">Créé {{ optional($campaign->created_at)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $campaign->total_targets }}</span>
                                        <span class="badge badge-success">{{ $campaign->success_count }} ok</span>
                                        <span class="badge badge-danger">{{ $campaign->failure_count }} ko</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClasses[$campaign->status] ?? 'badge-secondary' }}">{{ $statusLabels[$campaign->status] ?? $campaign->status }}</span>
                                    </td>
                                    <td style="min-width: 210px;">
                                        <a href="{{ route($logsRoute, ['notification_campaign_id' => $campaign->id]) }}" class="btn btn-sm btn-outline-info">Logs</a>
                                        @if(in_array($campaign->status, ['draft', 'scheduled', 'failed', 'cancelled']))
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
                                    <td colspan="6" class="text-center text-muted py-4">Aucune campagne pour le moment.</td>
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
        var form = document.getElementById('notification-send-form');
        var holder = document.getElementById('selected-users-holder');

        if (!form || !holder) {
            return;
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

        Array.prototype.forEach.call(document.querySelectorAll('.js-user-checkbox'), function (checkbox) {
            checkbox.addEventListener('change', syncSelectedUsers);
        });
        syncSelectedUsers();
    })();
</script>

@include('layouts.footer')