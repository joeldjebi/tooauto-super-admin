@extends('call-centers.layout')

@php
    use App\Support\CallCenterOutcomes;
    $sections = [
        'en_retard' => ['title' => 'En retard', 'subtitle' => 'Rappels dont la date est passee : a traiter en priorite.', 'tone' => 'red'],
        'aujourdhui' => ['title' => "Aujourd'hui", 'subtitle' => 'Rappels prevus pour la journee.', 'tone' => 'orange'],
        'a_venir' => ['title' => 'A venir', 'subtitle' => 'Rappels programmes les prochains jours.', 'tone' => 'gray'],
    ];
    $totalDue = $groups['en_retard']->count() + $groups['aujourdhui']->count();
@endphp

@section('lead')
    {{ $totalDue > 0 ? $totalDue . ' rappel(s) a faire aujourd\'hui, dont ' . $groups['en_retard']->count() . ' en retard.' : "Aucun rappel en attente pour aujourd'hui." }}
@endsection

@section('header-actions')
    @if($isSupervisor)
        <div class="btn-group" role="group" aria-label="Rappels affiches">
            <a href="{{ route('call-center.mes-rappels') }}" class="btn {{ $showAll ? 'btn-outline-secondary' : 'btn-primary' }}">Mes rappels</a>
            <a href="{{ route('call-center.mes-rappels', ['agent' => 'tous']) }}" class="btn {{ $showAll ? 'btn-primary' : 'btn-outline-secondary' }}">Toute l'equipe</a>
        </div>
    @endif
@endsection

@section('content')
    <style>
        .cc-cb-list { display: grid; gap: 10px; }
        .cc-cb { align-items: center; background: var(--cc-surface); border-radius: var(--cc-radius); box-shadow: inset 0 0 0 1px var(--cc-separator); display: grid; gap: 10px 18px; grid-template-columns: 150px minmax(0, 1fr) auto; padding: 14px 16px; }
        .cc-cb-when { font-size: 15px; font-variant-numeric: tabular-nums; font-weight: 600; }
        .cc-cb-when small { color: var(--cc-label-3); display: block; font-size: 12px; font-weight: 400; }
        .cc-cb.is-late .cc-cb-when { color: var(--cc-red); }
        .cc-cb-name { font-size: 15px; font-weight: 600; }
        .cc-cb-meta { color: var(--cc-label-2); display: flex; flex-wrap: wrap; font-size: 12.5px; gap: 4px 10px; margin-top: 4px; }
        .cc-cb-note { color: var(--cc-label-2); font-size: 13px; margin-top: 6px; white-space: pre-line; }
        .cc-cb-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
        .cc-cb-section { margin-bottom: 22px; }
        .cc-cb-section h2 { align-items: center; display: flex; font-size: 18px; font-weight: 700; gap: 8px; margin: 0 0 4px; }
        .cc-cb-section p { color: var(--cc-label-3); font-size: 13px; margin: 0 0 12px; }
        @media (max-width: 900px) {
            .cc-cb { grid-template-columns: 1fr; }
            .cc-cb-actions { justify-content: flex-start; }
        }
    </style>

    @if(! $available)
        <div class="alert alert-warning">Le journal des appels n'est pas encore installe : creez la table <code>call_center_interactions</code>.</div>
    @elseif($groups['en_retard']->isEmpty() && $groups['aujourdhui']->isEmpty() && $groups['a_venir']->isEmpty())
        <div class="cc-card">
            <div class="cc-empty-state">
                @include('call-centers.partials.icon', ['name' => 'calendar'])
                <strong>Aucun rappel en attente</strong>
                <span>Quand un appel se termine par « A rappeler », le rappel apparait ici a la date choisie.</span>
            </div>
        </div>
    @else
        @foreach($sections as $key => $section)
            @continue($groups[$key]->isEmpty() && $key === 'a_venir')
            <section class="cc-cb-section">
                <h2>
                    {{ $section['title'] }}
                    <span class="badge {{ $key === 'en_retard' ? 'badge-danger' : ($key === 'aujourdhui' ? 'badge-warning' : 'badge-light') }}">{{ $groups[$key]->count() }}</span>
                </h2>
                <p>{{ $section['subtitle'] }}</p>

                @if($groups[$key]->isEmpty())
                    <div class="cc-card cc-muted" style="padding: 14px 16px;">Rien {{ $key === 'en_retard' ? 'en retard' : "pour aujourd'hui" }}.</div>
                @else
                    <div class="cc-cb-list">
                        @foreach($groups[$key] as $row)
                            @php
                                $rappel = \Carbon\Carbon::parse($row->rappel_le);
                                $nom = trim((string) $row->cible_nom) ?: CallCenterOutcomes::label('cible', $row->cible_type) . ' #' . $row->cible_id;
                                $alerte = $row->alert_id ? trim(($row->type_alerte ?? 'Alerte') . ($row->vehicule ? ' · ' . $row->vehicule : '') . ($row->alerte_date_fin ? ' · fin ' . \Carbon\Carbon::parse($row->alerte_date_fin)->format('d/m/Y') : '')) : null;
                                $payload = ['cible_type' => $row->cible_type, 'cible_id' => (int) $row->cible_id, 'alert_id' => $row->alert_id, 'nom' => $nom, 'alerte' => $alerte, 'motif' => $row->motif];
                            @endphp
                            <article class="cc-cb {{ $key === 'en_retard' ? 'is-late' : '' }}">
                                <div class="cc-cb-when">
                                    {{ $key === 'aujourdhui' ? $rappel->format('H:i') : $rappel->format('d/m H:i') }}
                                    <small>{{ $key === 'en_retard' ? $rappel->locale('fr')->diffForHumans() : $rappel->locale('fr')->isoFormat('dddd D MMMM') }}</small>
                                </div>
                                <div style="min-width: 0;">
                                    <div class="cc-cb-name">
                                        <a href="{{ route('call-center.interactions.history', [$row->cible_type, $row->cible_id]) }}">{{ $nom }}</a>
                                    </div>
                                    <div class="cc-cb-meta">
                                        <span>{{ CallCenterOutcomes::label('cible', $row->cible_type) }}</span>
                                        <span>{{ CallCenterOutcomes::label('motif', $row->motif) }}</span>
                                        @if($alerte)
                                            <span>{{ $alerte }}</span>
                                        @endif
                                        <span>Programme le {{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') }}{{ $showAll ? ' par ' . ($row->agent ?: 'Agent supprime') : '' }}</span>
                                    </div>
                                    @if($row->commentaire)
                                        <div class="cc-cb-note">{{ \Illuminate\Support\Str::limit($row->commentaire, 220) }}</div>
                                    @endif
                                </div>
                                <div class="cc-cb-actions">
                                    @include('call-centers.partials.phone', ['mobile' => $row->cible_mobile, 'indicatif' => $row->cible_indicatif, 'whatsapp' => (string) ($row->cible_whatsapp ?? '0') === '1'])
                                    <button type="button" class="btn btn-sm btn-primary" data-cc-log="{{ json_encode($payload) }}">
                                        @include('call-centers.partials.icon', ['name' => 'phone'])
                                        Enregistrer l'appel
                                    </button>
                                    <form method="POST" action="{{ route('call-center.mes-rappels.traite', $row->id) }}" class="mb-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Le rappel n'est plus necessaire (client deja joint autrement...)">Marquer traite</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach
        <p class="cc-muted" style="font-size: 12.5px;">Enregistrer un appel sur un contact cloture automatiquement ses rappels en attente. Pour reporter, enregistrez l'appel avec le resultat « A rappeler » et une nouvelle date.</p>
    @endif
@endsection
