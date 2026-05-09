@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')


<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
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
                <h4 class="card-title mb-3">Les établissements</h4>
                <div class="row mb-4">
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-primary shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Aujourd'hui</div>
                                <div class="h4 mb-0">{{ $stats['today'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-success shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Cette semaine</div>
                                <div class="h4 mb-0">{{ $stats['this_week'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-info shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Mois en cours</div>
                                <div class="h4 mb-0">{{ $stats['current_month'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-warning shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Mois précédent</div>
                                <div class="h4 mb-0">{{ $stats['previous_month'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-dark shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Total</div>
                                <div class="h4 mb-0">{{ $stats['total'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($etablissements->count() > 0)
                    <form method="GET" action="{{ url()->current() }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Rechercher par nom, contact, adresse, type..."
                                    value="{{ $filters['search'] ?? '' }}"
                                >
                            </div>
                            <div class="col-md-6 col-lg-3 mt-3 mt-md-0">
                                <select name="type_etablissement_id" class="form-control">
                                    <option value="">Tous les types</option>
                                    @foreach($typeEtablissements as $typeEtablissement)
                                        <option value="{{ $typeEtablissement->id }}" {{ (string) ($filters['type_etablissement_id'] ?? '') === (string) $typeEtablissement->id ? 'selected' : '' }}>
                                            {{ $typeEtablissement->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 mt-3 mt-lg-0">
                                <input
                                    type="date"
                                    name="date_from"
                                    class="form-control"
                                    title="Date de création à partir de"
                                    value="{{ $filters['date_from'] ?? '' }}"
                                >
                            </div>
                            <div class="col-md-6 col-lg-2 mt-3 mt-lg-0">
                                <input
                                    type="date"
                                    name="date_to"
                                    class="form-control"
                                    title="Date de création jusqu'à"
                                    value="{{ $filters['date_to'] ?? '' }}"
                                >
                            </div>
                            <div class="col-md-6 col-lg-1 mt-3 mt-lg-0 d-flex">
                                <button type="submit" class="btn btn-primary btn-block mr-2">
                                    Filtrer
                                </button>
                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-block">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="etablissements_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Logo</th>
                                    <th scope="col">Nom De l'établissement</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col">Adresse</th>
                                    <th scope="col">Localisation</th>
                                    <th scope="col">Type établissement</th>
                                    <th scope="col">Gérant</th>
                                    <th scope="col">Date de création</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($etablissements as $key => $item)

                                    @php
                                        $longitude = "-3.9171132";
                                        $latitude = "5.3457994";

                                        $url = "https://www.google.com/maps?q={$latitude},{$longitude}";
                                    @endphp
                                    <tr>
                                        <td>{{ $etablissements->firstItem() + $key }}</td>
                                        <td>
                                            <img
                                                width="50"
                                                src="{{ $item->logo_url }}"
                                                alt="{{ $item->libelle }}"
                                            >
                                        </td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->mobile }}</td>
                                        <td>{{ $item->adresse }}</td>
                                        <td> <a target="_blank" href="{{ $url }}" class="btn btn-primary">Map</a> </td>
                                        <td>{{ $item->typeEtablissement->libelle ?? 'Non défini' }}</td>
                                        <td>{{ $item->professionnel->nom ?? '' }} {{ $item->professionnel->prenoms ?? '' }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('show-etablissement', $item->id) }}" class="btn btn-info">
                                                Détails
                                            </a>

                                            <button
                                                class="btn btn-toggle-status btn-{{ $item->statut == 1 ? 'danger' : 'primary' }}"
                                                data-id="{{ $item->id }}"
                                                data-statut="{{ $item->statut }}">
                                                {{ $item->statut ? 'Désactiver' : 'Activer' }}
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-danger"
                                                data-toggle="modal"
                                                data-target="#deleteEtablissementModal{{ $item->id }}"
                                            >
                                                Supprimer
                                            </button>

                                            <div class="modal fade" id="deleteEtablissementModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteEtablissementModalLabel{{ $item->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteEtablissementModalLabel{{ $item->id }}">Confirmer la suppression</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="mb-2">Vous êtes sur le point de supprimer définitivement cet établissement :</p>
                                                            <p class="font-weight-bold mb-3">{{ $item->name }}</p>
                                                            <p class="text-danger mb-0">
                                                                Cette action est irréversible et supprimera aussi les articles, promotions, services, abonnements pro et liaisons associées.
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                            <form action="{{ route('delete-etablissement', $item->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                        <p class="mb-2 mb-md-0">
                            Affichage de {{ $etablissements->firstItem() }} à {{ $etablissements->lastItem() }} sur {{ $etablissements->total() }} établissements
                        </p>
                        <div>
                            {{ $etablissements->links() }}
                        </div>
                    </div>
                    @else
                        <p>Aucun établissement trouvé pour ces filtres.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
