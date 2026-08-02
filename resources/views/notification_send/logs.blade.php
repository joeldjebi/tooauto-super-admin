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
    $statusClasses = ['sent' => 'badge-success', 'failed' => 'badge-danger', 'pending' => 'badge-warning'];
    $statusLabels = ['sent' => 'Envoyé', 'failed' => 'Échec', 'pending' => 'En attente'];
@endphp

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Logs Notification send</h4>
                    <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary btn-sm">Retour</a>
                </div>

                <form method="GET" action="{{ route($logsRoute) }}" class="mb-3">
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label>Campagne</label>
                            <select name="notification_campaign_id" class="form-control">
                                <option value="">Toutes</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}" {{ request('notification_campaign_id') == $campaign->id ? 'selected' : '' }}>#{{ $campaign->id }} - {{ \Illuminate\Support\Str::limit($campaign->title, 70) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Statut</label>
                            <select name="status" class="form-control">
                                <option value="">Tous</option>
                                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Envoyé</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échec</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label>Recherche</label>
                            <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Usager, token, erreur">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="{{ route($logsRoute) }}" class="btn btn-light">Réinitialiser</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Campagne</th>
                                <th>User</th>
                                <th>Alerte</th>
                                <th>Statut</th>
                                <th>Token</th>
                                <th>Erreur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ optional($log->sent_at ?? $log->created_at)->format('d/m/Y H:i') }}</td>
                                    <td style="min-width: 220px;">
                                        @if($log->campaign)
                                            <strong>#{{ $log->campaign->id }}</strong>
                                            <div>{{ \Illuminate\Support\Str::limit($log->campaign->title, 80) }}</div>
                                        @else
                                            <span class="text-muted">Campagne supprimée</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 180px;">
                                        @if($log->user)
                                            <strong>#{{ $log->user->id }}</strong>
                                            <div>{{ trim(($log->user->nom ?? '') . ' ' . ($log->user->prenoms ?? '')) ?: ($log->user->name ?? 'Usager') }}</div>
                                            <small class="text-muted">{{ $log->user->telephone ?? $log->user->mobile ?? $log->user->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->alert_id)
                                            <strong>#{{ $log->alert_id }}</strong>
                                            <div>{{ optional($log->typeAlert)->libelle ?? 'Type #' . $log->type_alert_id }}</div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $statusClasses[$log->status] ?? 'badge-secondary' }}">{{ $statusLabels[$log->status] ?? $log->status }}</span></td>
                                    <td><code>{{ \Illuminate\Support\Str::limit($log->fcm_token, 34) }}</code></td>
                                    <td style="min-width: 240px;">
                                        @if($log->error_message)
                                            <span class="text-danger">{{ \Illuminate\Support\Str::limit($log->error_message, 120) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Aucun log trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')