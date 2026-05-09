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
                <h4 class="card-title mb-3">Les QR codes générés</h4>
                @if($qrcodes->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Code QR</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Date d'assignation</th>
                                    <th scope="col">Date de création</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($qrcodes as $key => $item)
                                    @if(!$item->is_assigned)
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modifier le QR code</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-qrcode-generate', ['id' => $item->id]) }}" method="post">
                                                        @csrf
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Code QR</label>
                                                                <input class="form-control" name="qrcode" type="text" value="{{ old("qrcode")?? $item->qrcode }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Assigné</label>
                                                                <select class="form-control" name="is_assigned">
                                                                    <option value="0" {{ $item->is_assigned == 0 ? 'selected' : '' }}>Non assigné</option>
                                                                    <option value="1" {{ $item->is_assigned == 1 ? 'selected' : '' }}>Assigné</option>
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
                                    @endif
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><strong>{{ $item->qrcode }}</strong></td>
                                        <td>
                                            @if($item->is_assigned)
                                                <span class="badge badge-success">Assigné</span>
                                            @else
                                                <span class="badge badge-secondary">Non assigné</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->assigned_at ? $item->assigned_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if(!$item->is_assigned)
                                                <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                    <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                                </a>
                                                <form action="{{ route('delete-qrcode-generate', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete">
                                                        <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted" title="Ce QR code est assigné et ne peut pas être modifié ou supprimé">
                                                    <i class="nav-icon i-Lock font-weight-bold"></i>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p>Aucun QR code généré !</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <h4>Générer des QR codes</h4>
        <div class="card mb-5">
            <div class="card-body">
                <div class="d-flex flex-column">
                    <form action="{{ route('store-qrcode-generate') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="">Nombre de QR codes à générer</label>
                            <input class="form-control" name="nombre" type="number" min="1" max="1000" required placeholder="Ex: 10">
                            <small class="form-text text-muted">Le nombre saisi ne sera pas enregistré, seulement les QR codes générés.</small>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary pd-x-20">Générer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@include('layouts.footer')

