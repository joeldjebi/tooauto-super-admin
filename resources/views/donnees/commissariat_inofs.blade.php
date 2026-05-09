@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-8 col-md-12">
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
                <h4 class="card-title mb-3">Commissariat infos</h4>
                @if($commissariat_inofs->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Catégorie</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Commune</th>
                                    <th scope="col">Contacts</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissariat_inofs as $key => $item)
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modifier commissariat info</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-commissariat-inofs', ['id' => $item->id]) }}" method="post">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label>Catégorie</label>
                                                            <select class="form-control" name="categorie">
                                                                <option value="agent_constat_accident" {{ $item->categorie == 'agent_constat_accident' ? 'selected' : '' }}>Agent constat accident</option>
                                                                <option value="commissariat_police" {{ $item->categorie == 'commissariat_police' ? 'selected' : '' }}>Commissariat police</option>
                                                                <option value="pc_radio" {{ $item->categorie == 'pc_radio' ? 'selected' : '' }}>PC Radio</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Nom</label>
                                                            <input class="form-control" name="nom" type="text" value="{{ old('nom') ?? $item->nom }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Commune</label>
                                                            <input class="form-control" name="commune" type="text" value="{{ old('commune') ?? $item->commune }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Ville</label>
                                                            <input class="form-control" name="ville" type="text" value="{{ old('ville') ?? $item->ville }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Situation géographique</label>
                                                            <input class="form-control" name="situation_geographique" type="text" value="{{ old('situation_geographique') ?? $item->situation_geographique }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Contacts</label>
                                                            <textarea class="form-control" name="contacts" rows="3">{{ old('contacts') ?? implode("\n", $item->contacts ?? []) }}</textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Statut</label>
                                                            <select class="form-control" name="statut">
                                                                <option value="1" {{ $item->statut ? 'selected' : '' }}>Actif</option>
                                                                <option value="0" {{ !$item->statut ? 'selected' : '' }}>Inactif</option>
                                                            </select>
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
                                        <td>{{ $item->categorie }}</td>
                                        <td>{{ $item->nom }}</td>
                                        <td>{{ $item->commune }}</td>
                                        <td>{{ implode(' / ', $item->contacts ?? []) }}</td>
                                        <td>{{ $item->statut ? 'Actif' : 'Inactif' }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-commissariat-inofs', ['id' => $item->id]) }}" method="POST" style="display:inline;">
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
                    <p>Aucun commissariat info enregistré !</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <h4>Ajouter commissariat info</h4>
        <div class="card mb-5">
            <div class="card-body">
                <form action="{{ route('store-commissariat-inofs') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select class="form-control" name="categorie">
                            <option value="agent_constat_accident">Agent constat accident</option>
                            <option value="commissariat_police">Commissariat police</option>
                            <option value="pc_radio">PC Radio</option>
                            <option value="sapeur_pompiers">Sapeur pompiers</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input class="form-control" name="nom" type="text">
                    </div>
                    <div class="form-group">
                        <label>Commune</label>
                        <input class="form-control" name="commune" type="text">
                    </div>
                    <div class="form-group">
                        <label>Ville</label>
                        <input class="form-control" name="ville" type="text" value="Abidjan">
                    </div>
                    <div class="form-group">
                        <label>Situation géographique</label>
                        <input class="form-control" name="situation_geographique" type="text">
                    </div>
                    <div class="form-group">
                        <label>Contacts</label>
                        <textarea class="form-control" name="contacts" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Statut</label>
                        <select class="form-control" name="statut">
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary pd-x-20">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
