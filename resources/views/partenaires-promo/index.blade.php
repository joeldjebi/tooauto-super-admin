@include('layouts.header')
@include('layouts.menu')
@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createPartenairePromo">
            Ajouter un partenaire promo
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

        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Partenaires et codes promo</h4>

                @if($partenaires->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Partenaire</th>
                                    <th>Contact</th>
                                    <th>Code</th>
                                    <th>Réduction</th>
                                    <th>Validité</th>
                                    <th>Utilisations</th>
                                    <th>Forfait</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($partenaires as $key => $partenaire)
                                    @php
                                        $codePromo = $partenaire->codePromo;
                                        $usageLabel = $codePromo && $codePromo->is_unlimited
                                            ? ($codePromo->usage_count ?? 0) . ' / Illimité'
                                            : (($codePromo->usage_count ?? 0) . ' / ' . ($codePromo->usage_limit ?? '-'));
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $partenaire->nom }}</strong><br>
                                            <small>{{ $partenaire->adresse }}</small>
                                        </td>
                                        <td>
                                            {{ $partenaire->telephone ?? '-' }}<br>
                                            <small>{{ $partenaire->email ?? '-' }}</small>
                                        </td>
                                        <td><strong>{{ optional($codePromo)->code ?? '-' }}</strong></td>
                                        <td>{{ optional($codePromo)->pourcentage ? number_format($codePromo->pourcentage, 0, ',', ' ') . '%' : '-' }}</td>
                                        <td>
                                            {{ optional(optional($codePromo)->date_debut)->format('d/m/Y') ?? 'Immédiat' }}
                                            -
                                            {{ optional(optional($codePromo)->date_fin)->format('d/m/Y') ?? 'Sans fin' }}
                                        </td>
                                        <td>{{ $usageLabel }}</td>
                                        <td>{{ optional(optional($codePromo)->forfaitUsager)->libelle ?? 'Tous' }}</td>
                                        <td>
                                            <span class="badge badge-{{ (int) $partenaire->statut === 1 ? 'success' : 'secondary' }}">
                                                {{ (int) $partenaire->statut === 1 ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info mb-1" data-toggle="modal" data-target="#editPartenairePromo{{ $partenaire->id }}">
                                                Modifier
                                            </button>
                                            <form action="{{ route('partenaires-promo.toggle-status', ['id' => $partenaire->id]) }}" method="post" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning mb-1">
                                                    {{ (int) $partenaire->statut === 1 ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('partenaires-promo.destroy', ['id' => $partenaire->id]) }}" method="post" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger mb-1" id="delete">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editPartenairePromo{{ $partenaire->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('partenaires-promo.update', ['id' => $partenaire->id]) }}" method="post">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier le partenaire promo</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @include('partenaires-promo.partials.form', [
                                                            'partenaire' => $partenaire,
                                                            'codePromo' => $codePromo,
                                                            'forfait_usagers' => $forfait_usagers,
                                                        ])
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
                @else
                    <p>Aucun partenaire promo enregistré.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createPartenairePromo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('partenaires-promo.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un partenaire promo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('partenaires-promo.partials.form', [
                        'partenaire' => null,
                        'codePromo' => null,
                        'forfait_usagers' => $forfait_usagers,
                    ])
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
