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
                <h4 class="card-title mb-3">Les categories services</h4>
                @if($categorie_services->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Libellé</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Activé pour les pros ?</th>
                                    <th scope="col">Usager ou pros ?</th>
                                    <th scope="col">Visible par défaut</th>
                                    <th scope="col">Accès expiré</th>
                                    <th scope="col">Accès via forfait</th>
                                    <th scope="col">Sous-catégories</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categorie_services as $key => $item)
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modifier la catégorie</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-service-categorie', ['id' => $item->id]) }}" method="post"  enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Libellé</label>
                                                                <input class="form-control" name="libelle" type="text" value="{{ old("libelle")?? $item->libelle }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Sous-catégories</label>
                                                                @php
                                                                    $selectedSousCategories = old(
                                                                        'sous_categorie_service_ids',
                                                                        $item->sousCategorieServices->pluck('id')->all() ?: array_filter([$item->sous_categorie_service_id])
                                                                    );
                                                                @endphp
                                                                <select class="form-control" name="sous_categorie_service_ids[]" multiple size="6">
                                                                    @foreach($sous_categorie_services as $sousCategorie)
                                                                        <option value="{{ $sousCategorie->id }}" {{ in_array($sousCategorie->id, (array) $selectedSousCategories) ? 'selected' : '' }}>
                                                                            {{ $sousCategorie->libelle }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <small class="form-text text-muted">Maintenir Ctrl ou Cmd pour sélectionner plusieurs sous-catégories.</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Image</label>
                                                                <input class="form-control" name="image" type="file">
                                                                @if (!empty($item->image))
                                                                    <img src="{{ $item->image }}" alt="" width="80">
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Activé pour les pros</label>
                                                                <select class="form-control" name="is_pro" id="">
                                                                    <option value="1" {{ $item->is_pro == 1 ? 'selected' : '' }}>Oui</option>
                                                                    <option value="0" {{ $item->is_pro == 0 ? 'selected' : '' }}>Non</option>
                                                                </select>
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
														<div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Usager/Pro ou Les deux</label>
                                                                <select class="form-control" name="pro_or_usager" id="">
    <option value="1" {{ $item->pro_or_usager == 1 ? 'selected' : '' }}>Pro</option>
    <option value="0" {{ $item->pro_or_usager == 0 ? 'selected' : '' }}>Usager</option>
    <option value="2" {{ $item->pro_or_usager == 2 ? 'selected' : '' }}>Les deux (Usager/Pro)</option>
</select>

                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Visible par défaut</label>
                                                                <select class="form-control" name="visible_par_defaut">
                                                                    <option value="1" {{ $item->visible_par_defaut == 1 ? 'selected' : '' }}>Oui</option>
                                                                    <option value="0" {{ $item->visible_par_defaut == 0 ? 'selected' : '' }}>Non</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Accessible si abonnement expiré</label>
                                                                <select class="form-control" name="accessible_abonnement_expire">
                                                                    <option value="1" {{ $item->accessible_abonnement_expire == 1 ? 'selected' : '' }}>Oui</option>
                                                                    <option value="0" {{ $item->accessible_abonnement_expire == 0 ? 'selected' : '' }}>Non</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Accessible selon mon abonnement actif</label>
                                                                <select class="form-control" name="accessible_en_fonction_de_mon_abonnement_actif">
                                                                    <option value="1" {{ $item->accessible_en_fonction_de_mon_abonnement_actif == 1 ? 'selected' : '' }}>Oui</option>
                                                                    <option value="0" {{ $item->accessible_en_fonction_de_mon_abonnement_actif == 0 ? 'selected' : '' }}>Non</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Fermé</button>
                                                            <button class="btn btn-primary ml-2" type="submit">Modifier</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if (!empty($item->image))
                                                <img width="50" src="{{ $item->image }}" alt="">
                                            @endif
                                        </td>
                                        <td>{{ $item->libelle }}</td>
                                        <td>{{ $item->statut == 1 ? 'Activé' : 'Désactivé' }}</td>
                                        <td>{{ $item->is_pro == 1 ? 'Oui' : 'Non' }}</td>
                                        <td>
											@switch($item->pro_or_usager)
												@case(0)
													Usager
													@break
												@case(1)
													Pro
													@break
												@case(2)
													Usager et Pro
													@break
												@default
													—
											@endswitch
										</td>
                                        <td>{{ $item->visible_par_defaut == 1 ? 'Oui' : 'Non' }}</td>
                                        <td>{{ $item->accessible_abonnement_expire == 1 ? 'Oui' : 'Non' }}</td>
                                        <td>{{ $item->accessible_en_fonction_de_mon_abonnement_actif == 1 ? 'Oui' : 'Non' }}</td>
                                        <td>
                                            @php
                                                $sousCategorieLabels = $item->sousCategorieServices->pluck('libelle')->all();
                                            @endphp
                                            {{ !empty($sousCategorieLabels) ? implode(', ', $sousCategorieLabels) : ($item->sousCategorieService->libelle ?? '-') }}
                                        </td>

                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-service-categorie', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete">
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
                        <p>Aucune catégorie enregistrer !'</p>
                @endif
            </div>
        </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <h4>Ajouter une catégorie de service</h4>
            <div class="card mb-5">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <form action="{{ route('store-service-categorie') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="">Libellé</label>
                                <input class="form-control" name="libelle" type="text">
                            </div>
                            <div class="form-group">
                                <label for="">Activé pour les pros</label>
                                <select class="form-control" name="is_pro" id="">
                                    <option value="1">Oui</option>
                                    <option value="0">Non</option>
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
                                <label for="">Statut</label>
                                <select class="form-control" name="pro_or_usager" id="">
                                    <option value="0" selected>Usager</option>
                                    <option value="1">Pro</option>
                                    <option value="2">Usager et Pro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Sous-catégories</label>
                                <select class="form-control" name="sous_categorie_service_ids[]" multiple size="6">
                                    @foreach($sous_categorie_services as $sousCategorie)
                                        <option value="{{ $sousCategorie->id }}" {{ in_array($sousCategorie->id, (array) old('sous_categorie_service_ids', [])) ? 'selected' : '' }}>
                                            {{ $sousCategorie->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Maintenir Ctrl ou Cmd pour sélectionner plusieurs sous-catégories.</small>
                            </div>
                            <div class="form-group">
                                <label for="">Visible par défaut</label>
                                <select class="form-control" name="visible_par_defaut">
                                    <option value="0" selected>Non</option>
                                    <option value="1">Oui</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Accessible si abonnement expiré</label>
                                <select class="form-control" name="accessible_abonnement_expire">
                                    <option value="0" selected>Non</option>
                                    <option value="1">Oui</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Accessible selon mon abonnement actif</label>
                                <select class="form-control" name="accessible_en_fonction_de_mon_abonnement_actif">
                                    <option value="1" selected>Oui</option>
                                    <option value="0">Non</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Image</label>
                                <input class="form-control" name="image" type="file">
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary pd-x-20">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card mb-5">
                <div class="card-body">
                    <h4 class="card-title mb-3">Ajouter une sous-catégorie de service</h4>
                    <div class="d-flex flex-column">
                        <form action="{{ route('store-sous-categorie-service') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="">Libellé</label>
                                <input class="form-control" name="libelle" type="text">
                            </div>
                            <div class="form-group">
                                <label for="">Activé pour les pros</label>
                                <select class="form-control" name="is_pro" id="">
                                    <option value="1">Oui</option>
                                    <option value="0" selected>Non</option>
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
                                <label for="">Usager/Pro ou Les deux</label>
                                <select class="form-control" name="pro_or_usager" id="">
                                    <option value="0" selected>Usager</option>
                                    <option value="1">Pro</option>
                                    <option value="2">Usager et Pro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">SS-catégories</label>
                                <input type="hidden" name="ss_categorie_service_ids" value="">
                                <select class="form-control" name="ss_categorie_service_ids[]" multiple size="6">
                                    @foreach($ss_categorie_services as $ssCategorie)
                                        <option value="{{ $ssCategorie->id }}" {{ in_array($ssCategorie->id, (array) old('ss_categorie_service_ids', [])) ? 'selected' : '' }}>
                                            {{ $ssCategorie->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Maintenir Ctrl ou Cmd pour sélectionner plusieurs SS-catégories.</small>
                            </div>
                            <div class="form-group">
                                <label for="">Image</label>
                                <input class="form-control" name="image" type="file">
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-secondary pd-x-20">Enregistrer la sous-catégorie</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@include('layouts.footer')
