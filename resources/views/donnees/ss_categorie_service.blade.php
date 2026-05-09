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
                    <h4 class="card-title mb-3">Les SS-catégories de services</h4>
                    @if($ss_categorie_services->isNotEmpty())
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
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ss_categorie_services as $key => $item)
                                        <div class="modal fade" id="ssId{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Modifier la SS-catégorie</h5>
                                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('update-ss-categorie-service', ['id' => $item->id]) }}" method="post" enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="form-group">
                                                                <label for="">Libellé</label>
                                                                <input class="form-control" name="libelle" type="text" value="{{ old('libelle') ?? $item->libelle }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Image</label>
                                                                <input class="form-control" name="image" type="file">
                                                                @if (!empty($item->image))
                                                                    <img src="{{ $item->image }}" alt="" width="80">
                                                                @endif
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Activé pour les pros</label>
                                                                <select class="form-control" name="is_pro">
                                                                    <option value="1" {{ $item->is_pro == 1 ? 'selected' : '' }}>Oui</option>
                                                                    <option value="0" {{ $item->is_pro == 0 ? 'selected' : '' }}>Non</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Statut</label>
                                                                <select class="form-control" name="statut">
                                                                    <option value="1" {{ $item->statut == 1 ? 'selected' : '' }}>Activé</option>
                                                                    <option value="0" {{ $item->statut == 0 ? 'selected' : '' }}>Désactivé</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Usager/Pro ou Les deux</label>
                                                                <select class="form-control" name="pro_or_usager">
                                                                    <option value="0" {{ $item->pro_or_usager == 0 ? 'selected' : '' }}>Usager</option>
                                                                    <option value="1" {{ $item->pro_or_usager == 1 ? 'selected' : '' }}>Pro</option>
                                                                    <option value="2" {{ $item->pro_or_usager == 2 ? 'selected' : '' }}>Usager et Pro</option>
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
                                            <td>
                                                <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#ssId{{ $item->id }}">
                                                    <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                                </a>
                                                <form action="{{ route('delete-ss-categorie-service', ['id' => $item->id]) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette SS-catégorie ?')">
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
                        <p>Aucune SS-catégorie enregistrée.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <h4>Ajouter une SS-catégorie de service</h4>
            <div class="card mb-5">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <form action="{{ route('store-ss-categorie-service') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="">Libellé</label>
                                <input class="form-control" name="libelle" type="text">
                            </div>
                            <div class="form-group">
                                <label for="">Activé pour les pros</label>
                                <select class="form-control" name="is_pro">
                                    <option value="1">Oui</option>
                                    <option value="0" selected>Non</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Statut</label>
                                <select class="form-control" name="statut">
                                    <option value="1" selected>Activé</option>
                                    <option value="0">Désactivé</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Usager/Pro ou Les deux</label>
                                <select class="form-control" name="pro_or_usager">
                                    <option value="0" selected>Usager</option>
                                    <option value="1">Pro</option>
                                    <option value="2">Usager et Pro</option>
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
        </div>
    </div>

@include('layouts.footer')
