@extends('call-centers.layout')

@php
    use App\Support\CallCenterOutcomes;
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');
    $pct = fn ($n) => $n === null ? '—' : $n . ' %';
    $query = array_filter(['periode' => $period, 'du' => $period === 'personnalisee' ? $from->toDateString() : null, 'au' => $period === 'personnalisee' ? $to->toDateString() : null, 'agent_id' => $agentId]);
    $agentName = $agentId ? optional($agents->firstWhere('id', $agentId), fn ($a) => trim($a->prenoms . ' ' . $a->nom)) : null;
@endphp

@section('lead')
    {{ $from->isSameDay($to) ? 'Le ' . $from->format('d/m/Y') : 'Du ' . $from->format('d/m/Y') . ' au ' . $to->format('d/m/Y') }}{{ $agentName ? ' · ' . $agentName : " · toute l'équipe" }}
@endsection

@section('header-actions')
    @if($available)
        <a href="{{ route('call-center.rapports.export', $query) }}" class="btn btn-outline-primary">
            @include('call-centers.partials.icon', ['name' => 'download'])
            Exporter les appels (CSV)
        </a>
    @endif
@endsection

@section('content')
    @include('call-centers.reports.partials.styles')

    {{-- Filtres --}}
    <div class="cc-card mb-0">
        <form method="GET" class="cc-r-filters">
            <div>
                <label class="cc-field-label" for="cc-r-periode">Période</label>
                <select id="cc-r-periode" name="periode" class="form-control" onchange="document.querySelectorAll('[data-cc-custom]').forEach(function (el) { el.hidden = this.value !== 'personnalisee'; }, this)">
                    @foreach($periods as $key => $label)
                        <option value="{{ $key }}" {{ $period === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div data-cc-custom {{ $period === 'personnalisee' ? '' : 'hidden' }}>
                <label class="cc-field-label" for="cc-r-du">Du</label>
                <input id="cc-r-du" type="date" name="du" class="form-control" value="{{ $from->toDateString() }}">
            </div>
            <div data-cc-custom {{ $period === 'personnalisee' ? '' : 'hidden' }}>
                <label class="cc-field-label" for="cc-r-au">Au</label>
                <input id="cc-r-au" type="date" name="au" class="form-control" value="{{ $to->toDateString() }}">
            </div>
            <div>
                <label class="cc-field-label" for="cc-r-agent">Agent</label>
                <select id="cc-r-agent" name="agent_id" class="form-control">
                    <option value="">Toute l'équipe</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ (int) $agentId === (int) $agent->id ? 'selected' : '' }}>{{ trim($agent->prenoms . ' ' . $agent->nom) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary w-100">Afficher</button>
            </div>
        </form>
    </div>

    @if(! $available)
        <div class="alert alert-warning mt-4">Le journal des appels n'est pas encore installe : créez la table <code>call_center_interactions</code>.</div>
    @else
        {{-- Indicateurs cles --}}
        <div class="cc-r-tiles">
            <div class="cc-r-tile">
                <span class="cc-r-tile-label">Appels</span>
                <span class="cc-r-tile-value">{{ $fmt($summary['total']) }}</span>
                <span class="cc-r-tile-hint">{{ $fmt($summary['contacts']) }} contacts différents{{ $summary['minutes'] ? ' · ' . $fmt($summary['minutes']) . ' min' : '' }}</span>
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
                <span class="cc-r-tile-hint">abonnements &le; {{ \App\Services\CallCenterReportService::CONVERSION_DAYS }} j après un appel · {{ $fmt($acquisition['abonnements_payants']) }} payants</span>
            </div>
            <div class="cc-r-tile">
                <span class="cc-r-tile-label">Couverture des alertes</span>
                <span class="cc-r-tile-value">{{ $pct($alerts['couverture'] ?? null) }}</span>
                <span class="cc-r-tile-hint">{{ $fmt($alerts['a_venir_relancees'] ?? 0) }} / {{ $fmt($alerts['a_venir'] ?? 0) }} alertes sous 30 j déjà relancées</span>
            </div>
            <div class="cc-r-tile {{ $callbacks['en_retard'] > 0 ? 'is-alert' : '' }}">
                <span class="cc-r-tile-label">Rappels en retard</span>
                <span class="cc-r-tile-value">{{ $fmt($callbacks['en_retard']) }}</span>
                <span class="cc-r-tile-hint">{{ $fmt($callbacks['en_attente']) }} en attente · {{ $fmt($callbacks['aujourdhui']) }} aujourd'hui</span>
            </div>
        </div>

        {{-- Activite par jour --}}
        @include('call-centers.reports.partials.daily-chart', ['daily' => $daily, 'total' => $summary['total']])

        {{-- Performance par agent --}}
        <div class="cc-card mb-4">
            <div class="cc-card-header">
                <div>
                    <h5 class="cc-card-title">Performance par agent</h5>
                    <p class="cc-card-subtitle">Cliquez sur un agent pour voir ses appels dans le journal.</p>
                </div>
            </div>
            <div class="cc-table-wrap">
                <table class="table table-hover cc-table cc-r-table">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th class="cc-r-num">Appels</th>
                            <th class="cc-r-num">Joints</th>
                            <th class="cc-r-num">Contacts</th>
                            <th class="cc-r-num">Relances alertes</th>
                            <th class="cc-r-num">Intéressés</th>
                            <th class="cc-r-num">À rappeler</th>
                            <th class="cc-r-num">Refus</th>
                            <th class="cc-r-num">Inscrits</th>
                            <th class="cc-r-num">Convertis</th>
                            <th class="cc-r-num" title="Abonnements souscrits dans les {{ \App\Services\CallCenterReportService::CONVERSION_DAYS }} jours après un appel de l'agent">Abonnements</th>
                            <th class="cc-r-num">Rappels en retard</th>
                            <th>Dernier appel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentsReport as $row)
                            <tr>
                                <td data-label="Agent">
                                    <a href="{{ route('call-center.interactions.index', ['agent_id' => $row->id, 'date_from' => $from->toDateString(), 'date_to' => $to->toDateString()]) }}">{{ $row->nom }}</a>
                                    @if($row->role === 'superviseur')
                                        <span class="badge badge-primary ml-1">Superviseur</span>
                                    @endif
                                    @unless($row->actif)
                                        <span class="badge badge-light ml-1">Inactif</span>
                                    @endunless
                                </td>
                                <td class="cc-r-num" data-label="Appels"><strong>{{ $fmt($row->total) }}</strong></td>
                                <td class="cc-r-num" data-label="Joints">{{ $fmt($row->joints) }} <span class="cc-r-sub">{{ $row->taux_joints !== null ? '(' . $row->taux_joints . ' %)' : '' }}</span></td>
                                <td class="cc-r-num" data-label="Contacts">{{ $fmt($row->contacts) }}</td>
                                <td class="cc-r-num" data-label="Relances alertes">{{ $fmt($row->relances) }}</td>
                                <td class="cc-r-num" data-label="Interesses">{{ $fmt($row->interesses) }}</td>
                                <td class="cc-r-num" data-label="A rappeler">{{ $fmt($row->a_rappeler) }}</td>
                                <td class="cc-r-num" data-label="Refus">{{ $fmt($row->refus) }}</td>
                                <td class="cc-r-num" data-label="Inscrits">{{ $fmt($row->inscrits) }}</td>
                                <td class="cc-r-num" data-label="Convertis">{{ $fmt($row->convertis) }}</td>
                                <td class="cc-r-num" data-label="Abonnements">{{ $fmt($row->abonnements) }} <span class="cc-r-sub">{{ $row->abonnements ? '(' . $row->abonnements_payants . ' payants)' : '' }}</span></td>
                                <td class="cc-r-num" data-label="Rappels en retard">
                                    @if($row->rappels_en_retard > 0)
                                        <span class="text-danger">{{ $fmt($row->rappels_en_retard) }}</span>
                                    @else
                                        0
                                    @endif
                                    <span class="cc-r-sub">/ {{ $fmt($row->rappels_en_attente) }}</span>
                                </td>
                                <td data-label="Dernier appel">{{ $row->dernier_appel ? \Carbon\Carbon::parse($row->dernier_appel)->format('d/m/Y H:i') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="cc-r-note">« Joints » : la personne a répondu (tous les résultats sauf pas de réponse, messagerie, injoignable, mauvais numéro). « Rappels en retard » : sur l'ensemble des rappels en attente, quelle que soit la période.</p>
        </div>

        <div class="cc-r-two">
            {{-- Relances d'alertes --}}
            <div class="cc-card">
                <div class="cc-card-header">
                    <div>
                        <h5 class="cc-card-title">Relances d'alertes</h5>
                        <p class="cc-card-subtitle">Assurances, visites techniques... à relancer avant expiration.</p>
                    </div>
                </div>
                @if(empty($alerts))
                    <p class="cc-muted mb-0">Table des alertes indisponible.</p>
                @else
                    <div class="cc-r-section-title" style="margin-top: 0;">Alertes qui expirent dans les 30 prochains jours</div>
                    <div class="cc-r-hbars">
                        <div>
                            <div class="cc-r-hbar-head"><span>Déjà relancées</span><strong>{{ $fmt($alerts['a_venir_relancees']) }} / {{ $fmt($alerts['a_venir']) }} · {{ $pct($alerts['couverture']) }}</strong></div>
                            <div class="cc-r-hbar-track"><div class="cc-r-hbar-fill" style="width: {{ (int) ($alerts['couverture'] ?? 0) }}%"></div></div>
                        </div>
                    </div>
                    <div class="cc-r-sub mt-1">{{ $fmt($alerts['a_venir_7j']) }} expirent dans les 7 jours.</div>

                    <div class="cc-r-section-title">Alertes expirées sur la période</div>
                    <div class="cc-r-kv">
                        <div><span>Alertes expirées</span><strong>{{ $fmt($alerts['expirees']) }}</strong></div>
                        <div><span>Relancées avant ou après expiration</span><strong>{{ $fmt($alerts['expirees_relancees']) }}</strong></div>
                        <div><span class="{{ $alerts['expirees_sans_relance'] > 0 ? 'text-danger' : '' }}">Jamais relancées</span><strong class="{{ $alerts['expirees_sans_relance'] > 0 ? 'text-danger' : '' }}">{{ $fmt($alerts['expirees_sans_relance']) }}</strong></div>
                        <div><span>Renouvelées — si relancées</span><strong>{{ $pct($alerts['taux_renouvellement_relancees']) }}</strong></div>
                        <div><span>Renouvelées — sans relance</span><strong>{{ $pct($alerts['taux_renouvellement_non_relancees']) }}</strong></div>
                    </div>

                    @if(count($alerts['par_type']) > 0)
                        <div class="cc-r-section-title">Relances de la période par type d'alerte</div>
                        <div class="cc-table-wrap">
                            <table class="table cc-table cc-r-table">
                                <thead><tr><th>Type</th><th class="cc-r-num">Relances</th><th class="cc-r-num">Joints</th><th class="cc-r-num">Renouvelées (déclarées)</th></tr></thead>
                                <tbody>
                                    @foreach($alerts['par_type'] as $type)
                                        <tr><td>{{ $type->type }}</td><td class="cc-r-num">{{ $fmt($type->relances) }}</td><td class="cc-r-num">{{ $fmt($type->joints) }}</td><td class="cc-r-num">{{ $fmt($type->renouvelees) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <p class="cc-r-note">Une alerte est « renouvelée » quand une nouvelle alerte du même type existe pour le même véhicule avec une échéance plus lointaine. Comparer les deux taux mesure l'effet des relances.</p>
                @endif
            </div>

            {{-- Acquisition --}}
            <div class="cc-card">
                <div class="cc-card-header">
                    <div>
                        <h5 class="cc-card-title">Acquisition</h5>
                        <p class="cc-card-subtitle">Contacts appelés sur la période, du premier appel à l'abonnement.</p>
                    </div>
                </div>
                @php
                    $funnel = [
                        ['Contacts appelés', $acquisition['appeles']],
                        ['Joints', $acquisition['joints']],
                        ['Intéressés', $acquisition['interesses']],
                        ['Inscrits (déclarés)', $acquisition['inscrits']],
                        ['Convertis (déclarés)', $acquisition['convertis']],
                        ['Abonnement souscrit après l\'appel', $acquisition['abonnements']],
                    ];
                    $funnelMax = max(1, $acquisition['appeles']);
                @endphp
                <div class="cc-r-hbars">
                    @foreach($funnel as [$label, $value])
                        <div>
                            <div class="cc-r-hbar-head"><span>{{ $label }}</span><strong>{{ $fmt($value) }} <span class="cc-r-sub">{{ $acquisition['appeles'] > 0 ? '· ' . round($value * 100 / $acquisition['appeles']) . ' %' : '' }}</span></strong></div>
                            <div class="cc-r-hbar-track"><div class="cc-r-hbar-fill" style="width: {{ round($value * 100 / $funnelMax, 1) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
                <div class="cc-r-kv mt-3">
                    <div><span>Abonnements usagers</span><strong>{{ $fmt($acquisition['abonnements_usagers']) }}</strong></div>
                    <div><span>Abonnements professionnels</span><strong>{{ $fmt($acquisition['abonnements_pros']) }}</strong></div>
                    <div><span>Dont payants</span><strong>{{ $fmt($acquisition['abonnements_payants']) }}</strong></div>
                </div>
                <p class="cc-r-note">« Abonnement souscrit après l'appel » est calculé automatiquement : abonnement créé dans les {{ \App\Services\CallCenterReportService::CONVERSION_DAYS }} jours qui suivent le premier appel de la période. « Inscrits » et « Convertis » sont les résultats saisis par les agents.</p>
            </div>
        </div>

        {{-- Repartitions --}}
        @php
            $breakdowns = [
                'Resultats' => ['data' => $summary['resultats'], 'group' => 'resultat'],
                'Motifs' => ['data' => $byMotif, 'group' => 'motif'],
                'Types de contact' => ['data' => $byCible, 'group' => 'cible'],
                'Canaux' => ['data' => $byCanal, 'group' => 'canal'],
            ];
        @endphp
        @include('call-centers.reports.partials.breakdowns', ['breakdowns' => $breakdowns])
    @endif
@endsection
