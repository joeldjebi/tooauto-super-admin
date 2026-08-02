@include('layouts.header')
@include('layouts.menu')
@include('layouts.fileariane')

@php
    $badgeClasses = [
        'sent' => 'badge-success',
        'failed' => 'badge-danger',
        'pending' => 'badge-warning',
    ];

    $badgeLabels = [
        'sent' => 'Envoyé',
        'failed' => 'Échec',
        'pending' => 'En attente',
    ];
@endphp

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has('message'))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
        @endif
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body py-3">
                <div class="text-muted small">Total logs</div>
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body py-3">
                <div class="text-muted small">Envoyés</div>
                <h3 class="mb-0 text-success">{{ $stats['sent'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body py-3">
                <div class="text-muted small">Échecs</div>
                <h3 class="mb-0 text-danger">{{ $stats['failed'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title mb-0">Logs des notifications</h4>
                        @if($selectedMessage)
                            <small class="text-muted">Message : {{ $selectedMessage->title }}</small>
                        @endif
                    </div>
                    <a href="{{ route('message-conseils.index') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
                </div>

                <form method="GET" action="{{ route('message-conseils.logs') }}" class="mb-3">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Message conseil</label>
                            <select name="message_conseil_id" class="form-control">
                                <option value="">Tous les messages</option>
                                @foreach($messages as $message)
                                    <option value="{{ $message->id }}" {{ request('message_conseil_id') == $message->id ? 'selected' : '' }}>
                                        #{{ $message->id }} - {{ \Illuminate\Support\Str::limit($message->title, 60) }}
                                    </option>
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
                        <div class="form-group col-md-2">
                            <label>Du</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Au</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Recherche</label>
                            <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Usager, token, erreur">
                        </div>
                    </div>
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                        <a href="{{ route('message-conseils.logs') }}" class="btn btn-light">Réinitialiser</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Message</th>
                                <th>Usager</th>
                                <th>Statut</th>
                                <th>Token</th>
                                <th>Erreur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td style="min-width: 130px;">
                                        {{ optional($log->sent_at ?? $log->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="min-width: 220px;">
                                        @if($log->messageConseil)
                                            <strong>#{{ $log->messageConseil->id }}</strong>
                                            <div>{{ \Illuminate\Support\Str::limit($log->messageConseil->title, 80) }}</div>
                                        @else
                                            <span class="text-muted">Message supprimé</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 190px;">
                                        @if($log->user)
                                            <strong>{{ trim(($log->user->nom ?? '') . ' ' . ($log->user->prenoms ?? '')) ?: 'Usager #' . $log->user->id }}</strong>
                                            <div class="small text-muted">{{ $log->user->telephone ?? $log->user->mobile ?? $log->user->email }}</div>
                                        @elseif($log->user_id)
                                            <span class="text-muted">Usager #{{ $log->user_id }}</span>
                                        @else
                                            <span class="text-muted">Non renseigné</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClasses[$log->status] ?? 'badge-secondary' }}">
                                            {{ $badgeLabels[$log->status] ?? $log->status }}
                                        </span>
                                    </td>
                                    <td style="min-width: 180px;">
                                        <code>{{ \Illuminate\Support\Str::limit($log->fcm_token, 34) }}</code>
                                    </td>
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
                                    <td colspan="6" class="text-center text-muted py-4">Aucun log trouvé.</td>
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