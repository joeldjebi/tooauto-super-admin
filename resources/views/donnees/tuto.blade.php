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
                <h4 class="card-title mb-3">Les tutos</h4>

                @if($tutos->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Libellé</th>
                                    <th scope="col">Catégorie</th>
                                    <th scope="col">URL</th>
                                    <th scope="col">Date création</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tutos as $key => $tuto)
                                    <div class="modal fade" id="tuto{{ $tuto->id }}" tabindex="-1" role="dialog" aria-labelledby="tutoModalLabel{{ $tuto->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="tutoModalLabel{{ $tuto->id }}">Modifier le tuto</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-tuto', ['id' => $tuto->id]) }}" method="post">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label>Libellé</label>
                                                            <input class="form-control" name="libelle" type="text" value="{{ old('libelle') ?? $tuto->libelle }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>URL du tuto</label>
                                                            <input class="form-control" name="url_tuto" type="url" value="{{ old('url_tuto') ?? $tuto->url_tuto }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Catégorie</label>
                                                            <select class="form-control" name="categorie_tuto_id" required>
                                                                @foreach($categorie_tutos as $categorie)
                                                                    <option value="{{ $categorie->id }}" {{ $categorie->id == $tuto->categorie_tuto_id ? 'selected' : '' }}>
                                                                        {{ $categorie->libelle }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
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
                                        <td>{{ $tuto->libelle }}</td>
                                        <td>{{ $tuto->categorie_tuto->libelle ?? 'Non renseignée' }}</td>
                                        <td>
                                            <a href="{{ $tuto->url_tuto }}" target="_blank" class="btn btn-info btn-sm">
                                                Voir le tuto
                                            </a>
                                        </td>
                                        <td>{{ $tuto->created_at ? \Carbon\Carbon::parse($tuto->created_at)->format('d/m/Y H:i') : '' }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#tuto{{ $tuto->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-tuto', ['id' => $tuto->id]) }}" method="POST" style="display:inline;">
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
                    <p>Aucun tuto enregistré !</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12">
        <h4>Ajouter un tuto</h4>
        <div class="card mb-5">
            <div class="card-body">
                <form action="{{ route('store-tuto') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label>Libellé</label>
                        <input class="form-control" name="libelle" type="text" value="{{ old('libelle') }}" required>
                    </div>
                    <div class="form-group">
                        <label>URL du tuto</label>
                        <input class="form-control" name="url_tuto" type="url" value="{{ old('url_tuto') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Catégorie existante</label>
                        <select class="form-control" name="categorie_tuto_id">
                            <option value="">Choisir une catégorie</option>
                            @foreach($categorie_tutos as $categorie)
                                <option value="{{ $categorie->id }}" {{ (string) old('categorie_tuto_id') === (string) $categorie->id ? 'selected' : '' }}>
                                    {{ $categorie->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nouvelle catégorie</label>
                        <input class="form-control" name="categorie_tuto_libelle" type="text" value="{{ old('categorie_tuto_libelle') }}" placeholder="À remplir seulement si la catégorie n'existe pas">
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary pd-x-20">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <h4>Catégories de tutos</h4>
        <div class="card mb-5">
            <div class="card-body">
                <form action="{{ route('store-categorie-tuto') }}" method="post" class="mb-4">
                    @csrf
                    <div class="form-group">
                        <label>Libellé</label>
                        <input class="form-control" name="libelle" type="text" value="{{ old('libelle') }}" required>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary pd-x-20">Ajouter</button>
                    </div>
                </form>

                @if($categorie_tutos->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Libellé</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categorie_tutos as $key => $categorie)
                                    <div class="modal fade" id="categorieTuto{{ $categorie->id }}" tabindex="-1" role="dialog" aria-labelledby="categorieTutoModalLabel{{ $categorie->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="categorieTutoModalLabel{{ $categorie->id }}">Modifier la catégorie</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-categorie-tuto', ['id' => $categorie->id]) }}" method="post">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label>Libellé</label>
                                                            <input class="form-control" name="libelle" type="text" value="{{ old('libelle') ?? $categorie->libelle }}" required>
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
                                        <td>{{ $categorie->libelle }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#categorieTuto{{ $categorie->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-categorie-tuto', ['id' => $categorie->id]) }}" method="POST" style="display:inline;">
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
                    <p>Aucune catégorie enregistrée !</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
