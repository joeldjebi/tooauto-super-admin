@include('layouts.header')
@include('layouts.menu')
@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @include('reduction-cards.partials.nav')

        <div class="card text-left mb-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Filtres historique</h4>
                <form method="GET" action="{{ route('reduction-cards.histories') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label>Recherche</label>
                            <input type="text" name="search" class="form-control" placeholder="Usager, établissement, note..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Carte</label>
                            <select name="reduction_card_id" class="form-control">
                                <option value="">Toutes les cartes</option>
                                @foreach($cards as $card)
                                    <option value="{{ $card->id }}" {{ (string) ($filters['reduction_card_id'] ?? '') === (string) $card->id ? 'selected' : '' }}>{{ $card->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Type établissement</label>
                            <select name="establishment_type" class="form-control">
                                <option value="">Tous</option>
                                <option value="etablissement" {{ (string) ($filters['establishment_type'] ?? '') === 'etablissement' ? 'selected' : '' }}>Établissement</option>
                                <option value="lavage" {{ (string) ($filters['establishment_type'] ?? '') === 'lavage' ? 'selected' : '' }}>Lavage</option>
                                <option value="station" {{ (string) ($filters['establishment_type'] ?? '') === 'station' ? 'selected' : '' }}>Station</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Date début</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Date fin</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                            <a href="{{ route('reduction-cards.histories') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Historique des réductions</h4>
                @if($histories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Usager</th>
                                    <th>Carte</th>
                                    <th>Réduction</th>
                                    <th>Montant initial</th>
                                    <th>Montant réduction</th>
                                    <th>Montant final</th>
                                    <th>Appliqué par</th>
                                    <th>Établissement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histories as $history)
                                    <tr>
                                        <td>{{ $histories->firstItem() + $loop->index }}</td>
                                        <td>{{ optional($history->used_at ?? $history->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ trim((optional($history->user)->nom ?? '') . ' ' . (optional($history->user)->prenoms ?? '')) ?: optional($history->user)->name }}</td>
                                        <td>{{ optional($history->reductionCard)->name }}</td>
                                        <td>{{ $history->discount_type === 'percentage' ? number_format($history->discount_value, 2, ',', ' ') . '%' : number_format($history->discount_value, 0, ',', ' ') . ' FCFA' }}</td>
                                        <td>{{ number_format($history->montant_initial, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ number_format($history->montant_reduction, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ number_format($history->montant_final, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ $history->applied_by_id ?? '-' }}</td>
                                        <td>{{ $history->establishment_type }} #{{ $history->establishment_id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $histories->links() }}
                @else
                    <p>Aucune réduction historisée.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
