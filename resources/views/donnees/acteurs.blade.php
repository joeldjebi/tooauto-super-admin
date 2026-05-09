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
                <h4 class="card-title mb-3">Les acteurs</h4>
                @if($acteurs->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="acteurs_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Logo</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Site web</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acteurs as $key => $item)
                                    <!-- Modal de modification -->
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modifier l'acteur</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-acteur', ['id' => $item->id]) }}" method="post" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Nom</label>
                                                                <input class="form-control" name="name" type="text" value="{{ old("name") ?? $item->name }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Site web</label>
                                                                <input class="form-control" name="siteweb" type="url" value="{{ old("siteweb") ?? $item->siteweb }}" placeholder="https://example.com">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Logo</label>
                                                                @if($item->logo)
                                                                    <div class="mb-2">
                                                                        <img src="{{ asset('images/acteurs/' . $item->logo) }}" alt="Logo actuel" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                                                                    </div>
                                                                @endif
                                                                <input class="form-control" name="logo" type="file" accept="image/*">
                                                                <small class="form-text text-muted">Laisser vide pour conserver l'image actuelle</small>
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
                                        <td>
                                            @if($item->logo)
                                                <img src="{{ asset('images/acteurs/' . $item->logo) }}" alt="{{ $item->name }}" style="max-width: 50px; max-height: 50px;" class="img-thumbnail">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            @if($item->siteweb)
                                                <a href="{{ $item->siteweb }}" target="_blank" rel="noopener noreferrer">
                                                    {{ $item->siteweb }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-acteur', ['id' => $item->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet acteur ?')">
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
                    <p>Aucun acteur enregistré !</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <h4>Ajouter un acteur</h4>
        <div class="card mb-5">
            <div class="card-body">
                <div class="d-flex flex-column">
                    <form action="{{ route('store-acteur') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="">Nom <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" type="text" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="">Site web</label>
                            <input class="form-control" name="siteweb" type="url" value="{{ old('siteweb') }}" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label for="">Logo</label>
                            <input class="form-control" name="logo" type="file" accept="image/*">
                            <small class="form-text text-muted">Formats acceptés: JPEG, PNG, JPG, GIF, SVG (max 2MB)</small>
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

<script>
$(document).ready(function() {
    $('#acteurs_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        }
    });
});
</script>

@include('layouts.footer')

