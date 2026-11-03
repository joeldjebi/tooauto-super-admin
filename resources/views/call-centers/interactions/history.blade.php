@extends('call-centers.layout')

@php
    use App\Support\CallCenterOutcomes;
    $cibleNom = trim((string) ($cible->nom ?? '')) ?: $cibleLabel . ' #' . $cible->id;
@endphp

@section('lead', $cibleLabel . ' · ' . $cibleNom)

@section('header-actions')
    <a href="{{ $backUrl }}" class="btn btn-plain">
        @include('call-centers.partials.icon', ['name' => 'back'])
        Retour
    </a>
    <button type="button" class="btn btn-primary" data-cc-log="{{ json_encode(['cible_type' => $cibleType, 'cible_id' => (int) $cible->id, 'nom' => $cibleNom]) }}">
        @include('call-centers.partials.icon', ['name' => 'phone'])
        Enregistrer un appel
    </button>
@endsection

@section('content')
    <div class="cc-card">
        <div class="cc-card-header">
            <div>
                <h5 class="cc-card-title">Appels et actions</h5>
                <p class="cc-card-subtitle">Du plus recent au plus ancien, tous agents confondus.</p>
            </div>
            @if($interactions)
                <span class="cc-count">{{ number_format($interactions->total(), 0, ',', ' ') }} appel(s)</span>
            @endif
        </div>

        @if($interactions === null)
            <div class="alert alert-warning mb-0">Le journal des appels n'est pas encore installe : lancez la migration <code>call_center_interactions</code>.</div>
        @elseif($interactions->count() === 0)
            <div class="cc-empty-state">
                @include('call-centers.partials.icon', ['name' => 'empty'])
                <strong>Aucun appel enregistre</strong>
                <span>Utilisez « Enregistrer un appel » apres chaque contact.</span>
            </div>
        @else
            <div class="cc-table-wrap">
                <table class="table cc-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Agent</th>
                            <th>Canal / motif</th>
                            <th>Resultat</th>
                            <th>Alerte</th>
                            <th>Rappel</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interactions as $interaction)
                            @php
                                $rappel = $interaction->rappel_le ? \Carbon\Carbon::parse($interaction->rappel_le) : null;
                            @endphp
                            <tr>
                                <td data-label="Date">{{ \Carbon\Carbon::parse($interaction->created_at)->format('d/m/Y H:i') }}</td>
                                <td data-label="Agent">{{ $interaction->agent ?: '—' }}</td>
                                <td data-label="Canal / motif">
                                    {{ CallCenterOutcomes::label('canal', $interaction->canal) }}
                                    <div class="cc-muted" style="font-size: 12px;">{{ CallCenterOutcomes::label('motif', $interaction->motif) }}{{ $interaction->duree_minutes ? ' · ' . $interaction->duree_minutes . ' min' : '' }}</div>
                                </td>
                                <td data-label="Resultat">
                                    <span class="badge badge-{{ CallCenterOutcomes::tone($interaction->resultat) }}"><span class="cc-dot"></span>{{ CallCenterOutcomes::label('resultat', $interaction->resultat) }}</span>
                                </td>
                                <td data-label="Alerte">
                                    @if($interaction->alert_id)
                                        {{ $interaction->type_alerte ?? 'Alerte' }}{{ $interaction->vehicule ? ' · ' . $interaction->vehicule : '' }}
                                        @if($interaction->alerte_date_fin)
                                            <div class="cc-muted" style="font-size: 12px;">fin {{ \Carbon\Carbon::parse($interaction->alerte_date_fin)->format('d/m/Y') }}</div>
                                        @endif
                                    @else
                                        <span class="cc-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td data-label="Rappel">
                                    @if($rappel)
                                        {{ $rappel->format('d/m/Y H:i') }}
                                        <div style="font-size: 12px;">
                                            @if($interaction->rappel_fait_at)
                                                <span class="cc-muted">traite</span>
                                            @elseif($rappel->isPast())
                                                <span class="text-danger">en retard</span>
                                            @else
                                                <span class="cc-muted">prevu</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="cc-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td data-label="Commentaire" style="white-space: pre-line; min-width: 220px;">{{ $interaction->commentaire ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $interactions->links('call-centers.partials.pagination') }}
            </div>
        @endif
    </div>
@endsection
