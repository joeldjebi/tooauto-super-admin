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
                <h4 class="card-title mb-3">Les usagers</h4>
                @if($usagers->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    {{-- <th scope="col">Avatar</th> --}}
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénoms</th>
                                    <th scope="col">Mobile</th>
                                    <th scope="col">E-mail</th>
                                    <th scope="col">Abonnement</th>
                                    <th scope="col">Commercial</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usagers as $key => $item)
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modifier un usager</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-usagers', ['id' => $item->id]) }}" method="post">
                                                        @csrf
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Adresse E-mail</label>
                                                                <input class="form-control" name="email" type="text" value="{{ old("email")?? $item->email }}">
                                                            </div>
                                                        </div>
                                                        <div class="container">
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label for="">Indicatif</label>
                                                                        <input class="form-control" name="indicatif" type="number" value="{{ old("indicatif")?? $item->indicatif }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-9">
                                                                    <div class="form-group">
                                                                        <label for="">Numero de téléphone</label>
                                                                        <input class="form-control" name="mobile" type="number" value="{{ old("mobile")?? $item->mobile }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Nom</label>
                                                                <input class="form-control" name="nom" type="text" value="{{ old("nom")?? $item->nom }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Prénoms</label>
                                                                <input class="form-control" name="prenoms" type="text" value="{{ old("prenoms")?? $item->prenoms }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Statut</label>
                                                                <select class="form-control" name="statut" id="">
                                                                    <option value="1" {{ $item->statut == 1 ? 'selected' : ''}}>Activer</option>
                                                                    <option value="1" {{ $item->id == 0 ? 'selected' : ''}}>Desactiver</option>
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
                                        {{-- <td>
                                            <img 
                                                width="50" 
                                                height="50" 
                                                src="{{ config('app.url_api_usager') }}/images/avatar/{{ $item->avatar }}" 
                                                alt="{{ $item->libelle }}"
                                            >
                                        </td> --}}
                                        <td>{{ $item->nom }}</td>
                                        <td>{{ $item->prenoms }}</td>
                                        <td>{{ $item->indicatif }}{{ $item->mobile }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>
                                            @if($item->abonnement_affiche)
                                                <div>
                                                    <span class="badge badge-{{ $item->abonnement_est_actif ? 'success' : 'secondary' }}">
                                                        {{ $item->abonnement_est_actif ? 'Actif' : 'Expiré/Inactif' }}
                                                    </span>
                                                </div>
                                                <strong>{{ $item->abonnement_affiche->forfait_usager->libelle ?? 'Forfait non renseigné' }}</strong>
                                                <div class="small text-muted">
                                                    Du {{ $item->abonnement_affiche->date_debut }} au {{ $item->abonnement_affiche->date_fin }}
                                                </div>
                                                @if((int) $item->abonnement_affiche->is_free === 1)
                                                    <div class="small text-info">Gratuit</div>
                                                @endif
                                            @else
                                                <span class="badge badge-warning">Aucun abonnement</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->commercial)
                                                <strong>{{ $item->commercial->nom }} {{ $item->commercial->prenoms }}</strong>
                                                <div class="small text-muted">{{ $item->commercial->mobile }}</div>
                                            @else
                                                <span class="text-muted">Non enregistré par un commercial</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-white badge bg-{{ $item->statut == 1 ? 'primary' : "danger" }} m-2">{{ $item->statut == 1 ? 'Activer' : "Desactiver" }}</span>
                                        </td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-usagers', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form> 
                                            <button 
                                                class="btn btn-toggle-status-usager btn-{{ $item->statut == 0 ? 'primary' : 'danger' }}" 
                                                data-id="{{ $item->id }}" 
                                                data-statut="{{ $item->statut }}"
                                                title="{{ $item->statut == 1 ? 'Désactiver cet usager' : 'Activer cet usager' }}">
                                                {{ $item->statut == 1 ? 'Désactiver' : 'Activer' }}
                                            </button>
                                            <a class="text-primary mr-2" href="{{ route('usager.show', ['id' => $item->id]) }}">
                                                <button class="btn btn-primary">Voir les détails</button>
                                            </a>

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
</div>



@include('layouts.footer')
