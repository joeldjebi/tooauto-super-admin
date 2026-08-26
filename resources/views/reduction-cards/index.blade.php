@include('layouts.header')
@include('layouts.menu')
@include('layouts.fileariane')

@php
    $discountLabel = function ($card) {
        return $card->discount_type === 'percentage'
            ? number_format($card->discount_value, 2, ',', ' ') . '%'
            : number_format($card->discount_value, 0, ',', ' ') . ' FCFA';
    };
@endphp

<div class="row">
    <div class="col-lg-12 col-md-12">
        @include('reduction-cards.partials.nav')

        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createReductionCard">
            Ajouter une carte de réduction
        </button>

        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card text-left mb-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Filtres</h4>
                <form method="GET" action="{{ route('reduction-cards.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-3">
                            <label>Forfait</label>
                            <select name="forfait_usager_id" class="form-control">
                                <option value="">Tous les forfaits</option>
                                @foreach($forfait_usagers as $forfait)
                                    <option value="{{ $forfait->id }}" {{ (string) ($filters['forfait_usager_id'] ?? '') === (string) $forfait->id ? 'selected' : '' }}>
                                        {{ $forfait->libelle }}
                                    </option>
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
                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                            <a href="{{ route('reduction-cards.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card text-left mb-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Cartes configurées</h4>
                @if($cards->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Carte</th>
                                    <th>Forfait</th>
                                    <th>Réduction</th>
                                    <th>Cartes usagers</th>
                                    <th>Historiques</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cards as $card)
                                    <tr>
                                        <td>{{ $cards->firstItem() + $loop->index }}</td>
                                        <td>
                                            <strong>{{ $card->name }}</strong><br>
                                            <small>{{ \Illuminate\Support\Str::limit($card->description, 80) }}</small>
                                        </td>
                                        <td>{{ optional($card->forfaitUsager)->libelle ?? '-' }}</td>
                                        <td>{{ $discountLabel($card) }}</td>
                                        <td>{{ $card->user_cards_count }}</td>
                                        <td>{{ $card->histories_count }}</td>
                                        <td>
                                            <span class="badge badge-{{ (int) $card->statut === 1 ? 'success' : 'secondary' }}">
                                                {{ (int) $card->statut === 1 ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editReductionCard{{ $card->id }}">
                                                        Modifier
                                                    </button>
                                                    <form action="{{ route('reduction-cards.sync', ['id' => $card->id]) }}" method="post">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">Synchroniser</button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('reduction-cards.destroy', ['id' => $card->id]) }}" method="post">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" id="delete">Supprimer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editReductionCard{{ $card->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('reduction-cards.update', ['id' => $card->id]) }}" method="post">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier la carte de réduction</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @include('reduction-cards.partials.form', ['card' => $card, 'forfait_usagers' => $forfait_usagers])
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $cards->links() }}
                @else
                    <p>Aucune carte de réduction enregistrée.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createReductionCard" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('reduction-cards.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une carte de réduction</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('reduction-cards.partials.form', ['card' => null, 'forfait_usagers' => $forfait_usagers])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.footer')
