@extends('call-centers.layout')

@php
    use App\Support\CallCenterOutcomes;
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');
    $pct = fn ($n) => $n === null ? '—' : $n . ' %';
@endphp

@section('lead')
    {{ $from->isSameDay($to) ? 'Le ' . $from->format('d/m/Y') : 'Du ' . $from->format('d/m/Y') . ' au ' . $to->format('d/m/Y') }} · vos appels uniquement
@endsection

@section('content')
    @include('call-centers.reports.partials.styles')

    @if(! $available)
        <div class="alert alert-warning">Le journal des appels n'est pas encore installe : créez la table <code>call_center_interactions</code>.</div>
    @else
        {{-- Rappels a faire : le plus urgent en premier --}}
        @if($callbacks['en_retard'] + $callbacks['aujourdhui'] > 0)
            <a href="{{ route('call-center.mes-rappels') }}" class="alert {{ $callbacks['en_retard'] > 0 ? 'alert-danger' : 'alert-warning' }} d-flex justify-content-between align-items-center" style="text-decoration: none;">
                <span>
                    <strong>{{ $callbacks['en_retard'] + $callbacks['aujourdhui'] }} rappel(s) à faire</strong>
                    {{ $callbacks['en_retard'] > 0 ? '· dont ' . $callbacks['en_retard'] . ' en retard' : "aujourd'hui" }}
                </span>
                <span>Voir mes rappels &rarr;</span>
            </a>
        @endif

        <div class="cc-r-tiles" style="margin-top: 0;">
            <div class="cc-r-tile">
                <span class="cc-r-tile-label">Appels aujourd'hui</span>
                <span class="cc-r-tile-value">{{ $fmt($quick['aujourdhui']) }}</span>
                <span class="cc-r-tile-hint">{{ $fmt($quick['semaine']) }} sur 7 jours · {{ $fmt($quick['mois']) }} sur 30 jours</span>
            </div>
            <div class="cc-r-tile">
                <span class="cc-r-tile-label">Appels sur la période</span>
                <span class="cc-r-tile-value">{{ $fmt($summary['total']) }}</span>
                <span class="cc-r-tile-hint">
                    {{ $fmt($summary['contacts']) }} contacts différents
                    @if($team['agents_actifs'] > 1)
                        · moyenne équipe {{ $fmt($team['moyenne']) }}
                    @endif
                </span>
            </div>
            <div class="cc-r-tile">
                <span class="cc-r-tile-label">Taux de joignabilité</span>
                <span class="cc-r-tile-value">{{ $pct($summary['taux_joints']) }}</span>
                <span class="cc-r-tile-hint">{{ $fmt($summary['joints']) }} appels avec la personne en ligne</span>
            </div>
            <div class="cc-r-tile">
                <span class="cc-r-tile-label">Intéressés</span>
                <span class="cc-r-tile-value">{{ $fmt($acquisition['interesses']) }}</span>
                <span class="cc-r-tile-hint">{{ $fmt($acquisition['inscrits']) }} inscrits · {{ $fmt($acquisition['convertis']) }} convertis (déclarés)</span>
            </div>
            <div class="cc-r-tile is-good">
                <span class="cc-r-tile-label">Clients gagnés</span>
                <span class="cc-r-tile-value">{{ $fmt($acquisition['abonnements']) }}</span>
                <span class="cc-r-tile-hint">abonnements &le; {{ \App\Services\CallCenterReportService::CONVERSION_DAYS }} j après vos appels · {{ $fmt($acquisition['abonnements_payants']) }} payants</span>
            </div>
            <div class="cc-r-tile {{ $callbacks['en_retard'] > 0 ? 'is-alert' : '' }}">
                <span class="cc-r-tile-label">Rappels en retard</span>
                <span class="cc-r-tile-value">{{ $fmt($callbacks['en_retard']) }}</span>
                <span class="cc-r-tile-hint">{{ $fmt($callbacks['en_attente']) }} en attente · {{ $fmt($callbacks['aujourdhui']) }} aujourd'hui</span>
            </div>
        </div>

        <div class="cc-card mb-4">
            <form method="GET" class="cc-r-filters">
                <div>
                    <label class="cc-field-label" for="cc-a-periode">Période</label>
                    <select id="cc-a-periode" name="periode" class="form-control" onchange="document.querySelectorAll('[data-cc-custom]').forEach(function (el) { el.hidden = this.value !== 'personnalisee'; }, this)">
                        @foreach($periods as $key => $label)
                            <option value="{{ $key }}" {{ $period === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div data-cc-custom {{ $period === 'personnalisee' ? '' : 'hidden' }}>
                    <label class="cc-field-label" for="cc-a-du">Du</label>
                    <input id="cc-a-du" type="date" name="du" class="form-control" value="{{ $from->toDateString() }}">
                </div>
                <div data-cc-custom {{ $period === 'personnalisee' ? '' : 'hidden' }}>
                    <label class="cc-field-label" for="cc-a-au">Au</label>
                    <input id="cc-a-au" type="date" name="au" class="form-control" value="{{ $to->toDateString() }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-100">Afficher</button>
                </div>
                <div>
                    <a href="{{ route('call-center.interactions.index', ['agent_id' => $user->id, 'date_from' => $from->toDateString(), 'date_to' => $to->toDateString()]) }}" class="btn btn-outline-primary w-100">Voir mes appels</a>
                </div>
            </form>
        </div>

        @include('call-centers.reports.partials.daily-chart', ['daily' => $daily, 'total' => $summary['total']])

        @include('call-centers.reports.partials.breakdowns', [
            'fmt' => $fmt,
            'breakdowns' => [
                'Mes resultats' => ['data' => $summary['resultats'], 'group' => 'resultat'],
                'Mes motifs d\'appel' => ['data' => $byMotif, 'group' => 'motif'],
            ],
        ])
    @endif
@endsection
