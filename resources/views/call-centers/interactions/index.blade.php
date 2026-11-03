@extends('call-centers.layout')

@php
    use App\Support\CallCenterOutcomes;
    $activeFilters = collect($filters)->filter(fn ($value) => filled($value))->count();
@endphp

@section('lead', "Tous les appels de l'equipe : qui a appele qui, quand, et avec quel resultat.")

@section('content')
    <div class="cc-card">
        <div class="cc-card-header">
            <div>
                <h5 class="cc-card-title">Appels enregistres</h5>
                <p class="cc-card-subtitle">Cliquez sur un contact pour voir tout son historique.</p>
            </div>
            @if($interactions)
                <span class="cc-count">{{ number_format($interactions->total(), 0, ',', ' ') }} appel(s)</span>
            @endif
        </div>

        <form method="GET" class="cc-filter-panel">
            <div class="cc-filter-head">
                <span>
                    Filtres
                    @if($activeFilters > 0)
                        <span class="cc-filter-count">{{ $activeFilters }} actif(s)</span>
                    @endif
                </span>
                @if($activeFilters > 0)
                    <a href="{{ url()->current() }}">Tout effacer</a>
                @endif
            </div>
            <div class="cc-filter-grid">
                <div>
                    <label class="cc-field-label">Recherche</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Nom du contact, commentaire">
                </div>
                <div>
                    <label class="cc-field-label">Agent</label>
                    <select name="agent_id" class="form-control">
                        <option value="">Tous</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ (string) ($filters['agent_id'] ?? '') === (string) $agent->id ? 'selected' : '' }}>{{ trim($agent->prenoms . ' ' . $agent->nom) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="cc-field-label">Resultat</label>
                    <select name="resultat" class="form-control">
                        <option value="">Tous</option>
                        @foreach(CallCenterOutcomes::resultatLabels() as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['resultat'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="cc-field-label">Motif</label>
                    <select name="motif" class="form-control">
                        <option value="">Tous</option>
                        @foreach(CallCenterOutcomes::MOTIFS as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['motif'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="cc-field-label">Type de contact</label>
                    <select name="cible_type" class="form-control">
                        <option value="">Tous</option>
                        @foreach(CallCenterOutcomes::CIBLES as $key => $config)
                            <option value="{{ $key }}" {{ ($filters['cible_type'] ?? '') === $key ? 'selected' : '' }}>{{ $config['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="cc-field-label">Du</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div>
                    <label class="cc-field-label">Au</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>
            </div>
            <div class="cc-filter-actions">
                <button type="submit" class="btn btn-primary">
                    @include('call-centers.partials.icon', ['name' => 'search'])
                    Filtrer
                </button>
            </div>
        </form>

        @if($interactions === null)
            <div class="alert alert-warning mb-0">Le journal des appels n'est pas encore installe : creez la table <code>call_center_interactions</code>.</div>
        @elseif($interactions->count() === 0)
            <div class="cc-empty-state">
                @include('call-centers.partials.icon', ['name' => 'empty'])
                <strong>Aucun appel</strong>
                <span>{{ $activeFilters > 0 ? 'Aucun appel ne correspond a ces filtres.' : 'Les appels enregistres par les agents apparaitront ici.' }}</span>
            </div>
        @else
            <div class="cc-table-wrap">
                <table class="table table-hover cc-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Contact</th>
                            <th>Agent</th>
                            <th>Canal / motif</th>
                            <th>Resultat</th>
                            <th>Rappel</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interactions as $interaction)
                            @php
                                $createdAt = \Carbon\Carbon::parse($interaction->created_at);
                                $rappel = $interaction->rappel_le ? \Carbon\Carbon::parse($interaction->rappel_le) : null;
                                $cibleNom = trim((string) $interaction->cible_nom) ?: '#' . $interaction->cible_id;
                            @endphp
                            <tr>
                                <td data-label="Date">{{ $createdAt->isToday() ? "Aujourd'hui " . $createdAt->format('H:i') : $createdAt->format('d/m/Y H:i') }}</td>
                                <td data-label="Contact">
                                    <a href="{{ route('call-center.interactions.history', [$interaction->cible_type, $interaction->cible_id]) }}">{{ $cibleNom }}</a>
                                    <div class="cc-muted" style="font-size: 12px;">{{ CallCenterOutcomes::label('cible', $interaction->cible_type) }}{{ $interaction->type_alerte ? ' · ' . $interaction->type_alerte : '' }}</div>
                                </td>
                                <td data-label="Agent">{{ $interaction->agent ?: 'Agent supprime' }}</td>
                                <td data-label="Canal / motif">
                                    {{ CallCenterOutcomes::label('canal', $interaction->canal) }}
                                    <div class="cc-muted" style="font-size: 12px;">{{ CallCenterOutcomes::label('motif', $interaction->motif) }}</div>
                                </td>
                                <td data-label="Resultat">
                                    <span class="badge badge-{{ CallCenterOutcomes::tone($interaction->resultat) }}"><span class="cc-dot"></span>{{ CallCenterOutcomes::label('resultat', $interaction->resultat) }}</span>
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
                                <td data-label="Commentaire" style="min-width: 200px;">{{ $interaction->commentaire ? \Illuminate\Support\Str::limit($interaction->commentaire, 120) : '—' }}</td>
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
