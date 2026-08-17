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
                <h4 class="card-title mb-3">Les annonces</h4>
                @php
                    $filters = $filters ?? [];
                    $perPageOptions = [25, 50, 100];
                    $currentPerPage = $per_page ?? 50;
                @endphp
                <form method="GET" action="{{ route('index-annonce') }}" class="mb-4">
	                    <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Recherche</label>
                            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Modèle, marque, pièce, usager">
                        </div>
                        <div class="col-md-3">
                            <label>Usager</label>
                            <input type="text" name="usager" class="form-control" value="{{ $filters['usager'] ?? '' }}" placeholder="Nom ou prénoms">
                        </div>
                        <div class="col-md-2">
                            <label>Marque</label>
                            <input type="text" name="marque" class="form-control" value="{{ $filters['marque'] ?? '' }}" placeholder="Marque">
                        </div>
                        <div class="col-md-2">
                            <label>Type pièce</label>
                            <input type="text" name="type_piece" class="form-control" value="{{ $filters['type_piece'] ?? '' }}" placeholder="Type pièce">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                            <a href="{{ route('index-annonce') }}" class="btn btn-light">Réinitialiser</a>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">Page {{ $annonces->currentPage() }} - {{ $annonces->count() }} annonce(s) affichée(s)</div>
                    <form method="GET" action="{{ route('index-annonce') }}" class="d-flex align-items-center">
                        @foreach($filters as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <label class="mb-0 mr-2">Par page</label>
                        <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}" {{ $currentPerPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @if($annonces->count() > 0 )
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Marque</th>
                                    <th scope="col">Modèle</th>
                                    <th scope="col">Type de pièce</th>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($annonces as $key => $item)
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Détails de l'annonce</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            </div>
                                            <div class="modal-body">
                                                <h4 class="card-title mb-3">Détails de l'annonce</h4>
                                                <p><strong>Marque:</strong> {{ $item->marque->libelle ?? 'N/A' }}</p>
                                                <p><strong>Modèle:</strong> {{ $item->modele ?? 'N/A' }}</p>
                                                <p><strong>Type de pièce:</strong> {{ $item->type_de_piece->libelle ?? 'N/A' }}</p>
                                                <p><strong>Utilisateur:</strong> {{ trim(($item->currentUser->nom ?? '') . ' ' . ($item->currentUser->prenoms ?? '')) ?: 'N/A' }}</p>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>

                                        </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ (($annonces->currentPage() - 1) * $annonces->perPage()) + $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->marque->libelle ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $item->modele ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">
                                                {{ $item->type_de_piece->libelle ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ trim(($item->currentUser->nom ?? '') . ' ' . ($item->currentUser->prenoms ?? '')) ?: 'N/A' }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Eye font-weight-bold"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $annonces->links() }}
                    </div>
                    @else
                        <p>Aucune annonce enregistrer !</p>
                @endif
            </div>
        </div>
    </div>
</div>





@include('layouts.footer')
