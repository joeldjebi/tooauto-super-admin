@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')


    <div class="row">
        <div class="col-lg-8 col-md-12">
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
                <h4 class="card-title mb-3">Les forfaits usagers</h4>
                @if($forfait_usagers->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Libellé</th>
                                    <th scope="col">Durée (mois)</th>
                                    <th scope="col">Prix (FCFA)</th>
                                    <th scope="col">Nombre de véhicule</th>
                                    <th scope="col">Avantage</th>
                                    <th scope="col">Catégories de service</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forfait_usagers as $key => $item)
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modifier le forfait usager</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-forfait-usager', ['id' => $item->id]) }}" method="post">
                                                        @csrf
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Libellé</label>
                                                                <input class="form-control" name="libelle" type="text" value="{{ old("libelle")?? $item->libelle }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Durée (en mois)</label>
                                                                <input class="form-control" name="duree" type="number" min="0" value="{{ old("duree")?? $item->duree }}">
                                                                <small class="text-muted">Mettre 0 pour un forfait illimité</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Prix (FCFA)</label>
                                                                <input class="form-control" name="prix" type="number" min="0" value="{{ old("prix")?? $item->prix }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Nombre de véhicule</label>
                                                                <input class="form-control" name="nombre_vehicule" type="number" min="0" value="{{ old("nombre_vehicule") ?? $item->nombre_vehicule }}">
                                                                <small class="text-muted">Mettre 0 pour aucun véhicule inclus</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Avantage</label>
                                                                <select class="form-control" name="forfait_avantage_usager_id">
                                                                    <option value="">Aucun avantage</option>
                                                                    @foreach($forfait_avantage_usagers as $avantage)
                                                                        <option value="{{ $avantage->id }}" {{ (string) old('forfait_avantage_usager_id', $item->forfait_avantage_usager_id) === (string) $avantage->id ? 'selected' : '' }}>
                                                                            {{ $avantage->avantages }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Catégories de service</label>
                                                                <select class="form-control" name="categorie_services[]" multiple style="height: 150px;">
                                                                    @foreach($categorie_services as $categorie)
                                                                        <option value="{{ $categorie->id }}" {{ in_array($categorie->id, $item->categorieServices->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                                            {{ $categorie->libelle }}
                                                                            @if($categorie->sousCategorieService)
                                                                                ({{ $categorie->sousCategorieService->libelle }})
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <small class="text-muted">Maintenez Ctrl/Cmd pour sélectionner plusieurs catégories</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Statut</label>
                                                                <select class="form-control" name="statut" id="">
                                                                    <option value="1" {{ $item->statut == 1 ? 'selected' : '' }}>Activé</option>
                                                                    <option value="0" {{ $item->statut == 0 ? 'selected' : '' }}>Désactivé</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Fermer</button>
                                                            <button class="btn btn-primary ml-2" type="submit">Modifier</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->libelle }}</td>
                                        <td>
                                            @if($item->duree == 0)
                                                <span class="badge badge-info">Illimité</span>
                                            @else
                                                {{ $item->duree }} mois
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->prix, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ $item->nombre_vehicule ?? 0 }}</td>
                                        <td>
                                            @if($item->avantageUsager)
                                                {{ $item->avantageUsager->avantages }}
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @forelse($item->categorieServices as $categorie)
                                                <div style="margin-bottom: 5px;">
                                                    <small class="badge badge-info">{{ $categorie->libelle }}</small>
                                                    @if($categorie->sousCategorieService)
                                                        <small class="badge badge-secondary">{{ $categorie->sousCategorieService->libelle }}</small>
                                                    @endif
                                                </div>
                                            @empty
                                                <small class="text-muted">-</small>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if($item->statut == 1)
                                                <span class="badge badge-success">Activé</span>
                                            @else
                                                <span class="badge badge-danger">Désactivé</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}" title="Modifier">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-forfait-usager', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce forfait usager ?')">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p>Aucun forfait usager enregistré !</p>
                @endif
            </div>
        </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <h4>Ajouter un forfait usager</h4>
            <div class="card mb-5">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <form action="{{ route('store-forfait-usager') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="">Libellé</label>
                                <input class="form-control" name="libelle" type="text" required>
                            </div>
                            <div class="form-group">
                                <label for="">Durée (en mois)</label>
                                <input class="form-control" name="duree" type="number" min="0" required>
                                <small class="text-muted">Mettre 0 pour un forfait illimité</small>
                            </div>
                            <div class="form-group">
                                <label for="">Prix (FCFA)</label>
                                <input class="form-control" name="prix" type="number" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="">Nombre de véhicule</label>
                                <input class="form-control" name="nombre_vehicule" type="number" min="0" value="{{ old('nombre_vehicule', 0) }}" required>
                                <small class="text-muted">Mettre 0 pour aucun véhicule inclus</small>
                            </div>
                            <div class="form-group">
                                <label for="">Avantage (optionnel)</label>
                                <select class="form-control" name="forfait_avantage_usager_id">
                                    <option value="">Aucun avantage</option>
                                    @foreach($forfait_avantage_usagers as $avantage)
                                        <option value="{{ $avantage->id }}" {{ (string) old('forfait_avantage_usager_id') === (string) $avantage->id ? 'selected' : '' }}>
                                            {{ $avantage->avantages }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Statut</label>
                                <select class="form-control" name="statut" id="">
                                    <option value="1" selected>Activé</option>
                                    <option value="0">Désactivé</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Catégories de service (optionnel)</label>
                                <select class="form-control" name="categorie_services[]" multiple style="height: 150px;">
                                    @foreach($categorie_services as $categorie)
                                        <option value="{{ $categorie->id }}">
                                            {{ $categorie->libelle }}
                                            @if($categorie->sousCategorieService)
                                                ({{ $categorie->sousCategorieService->libelle }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Maintenez Ctrl/Cmd pour sélectionner plusieurs catégories</small>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary pd-x-20">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@include('layouts.footer')
