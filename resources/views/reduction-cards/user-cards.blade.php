@include('layouts.header')
@include('layouts.menu')
@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @include('reduction-cards.partials.nav')

        <div class="card text-left mb-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Filtres cartes usagers</h4>
                <form method="GET" action="{{ route('reduction-cards.user-cards') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label>Recherche</label>
                            <input type="text" name="search" class="form-control" placeholder="Usager, téléphone, code, QR..." value="{{ $filters['search'] ?? '' }}">
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
                            <label>Forfait</label>
                            <select name="forfait_usager_id" class="form-control">
                                <option value="">Tous les forfaits</option>
                                @foreach($forfait_usagers as $forfait)
                                    <option value="{{ $forfait->id }}" {{ (string) ($filters['forfait_usager_id'] ?? '') === (string) $forfait->id ? 'selected' : '' }}>{{ $forfait->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Statut</label>
                            <select name="statut" class="form-control">
                                <option value="">Tous</option>
                                <option value="1" {{ (string) ($filters['statut'] ?? '') === '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ (string) ($filters['statut'] ?? '') === '0' ? 'selected' : '' }}>Inactif</option>
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
                        <div class="col-md-3 mb-3">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                            <a href="{{ route('reduction-cards.user-cards') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Cartes attribuées aux usagers</h4>
                @if($userCards->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>QR</th>
                                    <th>Usager</th>
                                    <th>Téléphone</th>
                                    <th>Carte</th>
                                    <th>Forfait</th>
                                    <th>Validité</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userCards as $userCard)
                                    @php
                                        $phone = trim((optional($userCard->user)->indicatif ?? '') . ' ' . (optional($userCard->user)->mobile ?? ''));
                                        $phone = $phone ?: (optional($userCard->user)->telephone ?? '-');
                                    @endphp
                                    <tr>
                                        <td>{{ $userCards->firstItem() + $loop->index }}</td>
                                        <td><strong>{{ $userCard->card_code }}</strong></td>
                                        <td>{{ $userCard->qr_code }}</td>
                                        <td>{{ trim((optional($userCard->user)->nom ?? '') . ' ' . (optional($userCard->user)->prenoms ?? '')) ?: optional($userCard->user)->name }}</td>
                                        <td>{{ $phone }}</td>
                                        <td>{{ optional($userCard->reductionCard)->name }}</td>
                                        <td>{{ optional(optional($userCard->reductionCard)->forfaitUsager)->libelle ?? optional($userCard->forfaitUsager)->libelle }}</td>
                                        <td>{{ optional($userCard->date_debut)->format('d/m/Y') }} - {{ optional($userCard->date_fin)->format('d/m/Y') }}</td>
                                        <td>{{ (int) $userCard->statut === 1 ? 'Actif' : 'Inactif' }}</td>
                                        <td>
                                            <form action="{{ route('reduction-cards.user-cards.toggle-status', ['id' => $userCard->id]) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ (int) $userCard->statut === 1 ? 'btn-warning' : 'btn-success' }}">
                                                    {{ (int) $userCard->statut === 1 ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $userCards->links() }}
                @else
                    <p>Aucune carte attribuée.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
