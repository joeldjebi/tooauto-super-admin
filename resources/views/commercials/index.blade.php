@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')


<div class="row">
    <div class="col-lg-12 col-md-12">
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#exampleModal">Ajouter un commercial</button>
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
                <h4 class="card-title mb-3">Les commerciaux</h4>
                @if($commercials->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénoms</th>
                                    <th scope="col">Mobile</th>
                                    <th scope="col">Code de parrainage</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Date de création</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commercials as $key => $item)
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Enregistrer un commercial</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('commercial.update', ['id' => $item->id]) }}" method="post">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="mobile">Nom</label>
                                                                    <div class="input-group">
                                                                            <input type="text" class="form-control" name="nom" value="{{ $item->nom }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="mobile">Prénoms</label>
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control" name="prenoms" value="{{ $item->prenoms }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="mobile">Mobile</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" name="mobile" value="{{ $item->mobile }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary">Enregistrer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->nom }}</td>
                                        <td>{{ $item->prenoms }}</td>
                                        <td>{{ $item->mobile }}</td>
                                        <td>{{ $item->parrain->code }}</td>
                                        <td>{{ $item->statut == 1 ? 'Actif' : 'Inactif' }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('commercial.destroy', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form> 
                                            {{-- pour activer --}}
                                            @if ($item->statut == 0)
                                                <form action="{{ route('commercial.activer', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">
                                                        Activer
                                                    </button>
                                                </form> 
                                            @endif
                                            {{-- pour desactiver --}}
                                            @if ($item->statut == 1)
                                                <form action="{{ route('commercial.desactiver', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">
                                                        Désactiver
                                                    </button>
                                                </form> 
                                            @endif
                                            <a href="{{ route('commercial.filleuls', ['code' => $item->parrain->code]) }}" class="btn btn-info ml-1">
                                                Filleuls
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p>Aucun commercial enregistrer !</p>
                @endif
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Enregistrer un commercial</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{ route('commercial.store') }}" method="post">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="mobile">Nom</label>
                        <div class="input-group">
                                <input type="text" class="form-control" name="nom">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="mobile">Prénoms</label>
                        <div class="input-group">
                                <input type="text" class="form-control" name="prenoms">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>Numéro de téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text">+225</span>
                            <input type="text" class="form-control" name="mobile" required>
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