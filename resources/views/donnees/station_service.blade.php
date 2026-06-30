@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')


<div class="row">
    <div class="col-lg-12 col-md-12">
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#exampleModal">
            Ajouter une station service    
        </button>
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
                <h4 class="card-title mb-3">Les stations service</h4>
                <form method="GET" action="{{ route('index-station_service') }}" class="mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="station_electrique">Station électrique</label>
                            <select class="form-control" name="station_electrique" id="station_electrique">
                                <option value="">Toutes</option>
                                <option value="1" {{ (string) $station_electrique_filter === '1' ? 'selected' : '' }}>Oui</option>
                                <option value="0" {{ (string) $station_electrique_filter === '0' ? 'selected' : '' }}>Non</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filtrer</button>
                            <a href="{{ route('index-station_service') }}" class="btn btn-secondary">Réinitialiser</a>
                        </div>
                    </div>
                </form>
                @if($station_services->isNotEmpty() )
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="language_option_table"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Borne éléctrique</th>
                                <th scope="col">Station électrique</th>
                                <th scope="col">Ville</th>
                                <th scope="col">Commune</th>
                                <th scope="col">Adresse E-mail</th>
                                <th scope="col">Mobile</th>
                                <th scope="col">Situation géographique</th>
                                <th scope="col">Adresse map</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($station_services as $key => $item)
                            <!-- Modal -->
                            <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Modifier une station service
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('update-station_service', ['id' => $item->id]) }}"
                                                method="post">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="mobile">Nom </label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" name="name"
                                                                    value="{{ $item->name }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="mobile">Contact</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" name="mobile"
                                                                    value="{{ $item->mobile }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email">Adresse E-mail</label>
                                                            <div class="input-group">
                                                                <input readonly type="text" class="form-control"
                                                                    name="email" value="{{ $item->email }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="ville">Ville</label>
                                                            <div class="input-group">
                                                                <select class="form-control" name="ville_id" id="">
                                                                    @foreach ($villes as $ville)
                                                                    <option value="{{ $ville->id }}"
                                                                        {{ $ville->id == $item->ville_id ? 'selected' : '' }}>
                                                                        {{ $ville->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="commune">Commune</label>
                                                            <div class="input-group">
                                                                <select class="form-control" name="commune_id" id="">
                                                                    @foreach ($communes as $commune)
                                                                    <option value="{{ $commune->id }}"
                                                                        {{ $commune->id == $item->commune_id ? 'selected' : '' }}>
                                                                        {{ $commune->nom }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email">Situation géographique</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" name="adresse"
                                                                    value="{{ $item->adresse }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email">Adresse map</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control"
                                                                    name="adresse_map" value="{{ $item->adresse_map }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="borne_electrique">Borne élèctrique ?</label>
                                                            <div class="input-group">
                                                                <select class="form-control" name="borne_electrique" id="">
                                                                    <option value="0" {{ $item->borne_electrique == 0 ? 'selected' : '' }}>Non</option>
                                                                    <option value="1" {{ $item->borne_electrique == 1 ? 'selected' : '' }}>Oui</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="station_electrique">Station électrique ?</label>
                                                            <div class="input-group">
                                                                <select class="form-control" name="station_electrique" id="">
                                                                    <option value="0" {{ $item->station_electrique == 0 ? 'selected' : '' }}>Non</option>
                                                                    <option value="1" {{ $item->station_electrique == 1 ? 'selected' : '' }}>Oui</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->name ?? "" }}</td>
                                <td>{{ $item->borne_electrique == 1 ? "OUI" : "NON" }}</td>
                                <td>{{ $item->station_electrique == 1 ? "OUI" : "NON" }}</td>
                                <td>{{ $item->ville->libelle ?? "" }}</td>
                                <td>{{ $item->commune->nom ?? "" }}</td>
                                <td>{{ $item->email ?? "" }}</td>
                                <td>{{ $item->mobile ?? "" }}</td>
                                <td>{{ $item->adresse ?? "" }}</td>
                                <td>
                                    <a href="{{ $item->adresse_map }}" target="_blank" class="btn btn-info">
                                        MAP
                                    </a>
                                </td>
                                <td>
                                    <a class="text-success mr-2" href="#" data-toggle="modal"
                                        data-target="#id{{ $item->id }}">
                                        <i class="nav-icon i-Eye font-weight-bold"></i>
                                    </a>
                                    <a class="text-success mr-2" href="#" data-toggle="modal"
                                        data-target="#id{{ $item->id }}">
                                        <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                    </a>
                                    <form action="{{ route('destroy-station_service', ['id' => $item->id]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-danger"
                                            style="background:none; border:none; cursor:pointer;" id="delete">
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
                <p>Aucune station service enregistrée !</p>
                @endif
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enregistrer une station service</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('store-station_service') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="mobile">Nom</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="name">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="mobile">Adresse E-mail</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="email">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Numéro de téléphone</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="mobile" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Situation géographique</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="adresse" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Adresse map</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="adresse_map" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Ville</label>
                                <div class="input-group">
                                    <select class="form-control" name="ville_id" required>
                                        @foreach ($villes as $item)
                                        <option value="{{ $item->id }}">{{ $item->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Commune</label>
                                <div class="input-group">
                                    <select class="form-control" name="commune_id" required>
                                        @foreach ($communes as $item)
                                        <option value="{{ $item->id }}">{{ $item->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="borne_electrique">Borne élèctrique ?</label>
                                <div class="input-group">
                                    <select class="form-control" name="borne_electrique" id="">
                                        <option value="0">Non</option>
                                        <option value="1">Oui</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="station_electrique">Station électrique ?</label>
                                <div class="input-group">
                                    <select class="form-control" name="station_electrique" id="">
                                        <option value="0">Non</option>
                                        <option value="1">Oui</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


@include('layouts.footer')
