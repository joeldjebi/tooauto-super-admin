@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createProfessionnelModal">Ajouter un professionnel</button>

        @if(session()->has('message'))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card text-left">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <h4 class="card-title mb-2">Liste des professionnels</h4>
                    <span class="badge badge-info px-3 py-2">{{ $professionnels->total() }} professionnel(s)</span>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Rechercher par nom, prénom, mobile, email, rôle..."
                                value="{{ $filters['search'] ?? '' }}"
                            >
                        </div>
                        <div class="col-md-3 col-lg-2 mt-3 mt-md-0 d-flex">
                            <button type="submit" class="btn btn-primary btn-block mr-2">Filtrer</button>
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-block">Reset</a>
                        </div>
                    </div>
                </form>

                @if($professionnels->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Prénoms</th>
                                    <th>Rôle</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Etablissements liés</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($professionnels as $key => $item)
                                    <div class="modal fade" id="editProfessionnelModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="editProfessionnelModalLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editProfessionnelModalLabel{{ $item->id }}">Modifier un professionnel</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('professionnels.update', $item->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-12 mb-3">
                                                                <label>Nom</label>
                                                                <input type="text" class="form-control" name="nom" value="{{ $item->nom }}" required>
                                                            </div>
                                                            <div class="col-12 mb-3">
                                                                <label>Prénoms</label>
                                                                <input type="text" class="form-control" name="prenoms" value="{{ $item->prenoms }}" required>
                                                            </div>
                                                            <div class="col-12 mb-3">
                                                                <label>Rôle</label>
                                                                <input type="text" class="form-control" name="role" value="{{ $item->role }}" required>
                                                            </div>
                                                            <div class="col-12 mb-3">
                                                                <label>Mobile</label>
                                                                <input type="text" class="form-control" name="mobile" value="{{ $item->mobile }}" required>
                                                            </div>
                                                            <div class="col-12 mb-3">
                                                                <label>Email</label>
                                                                <input type="email" class="form-control" name="email" value="{{ $item->email }}">
                                                            </div>
                                                            <div class="col-12 mb-3">
                                                                <label>Statut</label>
                                                                <select name="statut" class="form-control">
                                                                    <option value="1" {{ (int) $item->statut === 1 ? 'selected' : '' }}>Actif</option>
                                                                    <option value="0" {{ (int) $item->statut === 0 ? 'selected' : '' }}>Inactif</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ $professionnels->firstItem() + $key }}</td>
                                        <td>{{ $item->nom }}</td>
                                        <td>{{ $item->prenoms }}</td>
                                        <td>{{ $item->role }}</td>
                                        <td>{{ $item->mobile }}</td>
                                        <td>{{ $item->email ?: 'Non défini' }}</td>
                                        <td>{{ (int) $item->statut === 1 ? 'Actif' : 'Inactif' }}</td>
                                        <td>
                                            @if($item->etablissements->isNotEmpty())
                                                {{ $item->etablissements->pluck('name')->implode(', ') }}
                                            @else
                                                Aucun établissement lié
                                            @endif
                                        </td>
                                        <td>{{ optional($item->created_at)->format('d/m/Y H:i') ?: 'Non définie' }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#editProfessionnelModal{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>

                                            <form action="{{ route('professionnels.destroy', $item->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form>

                                            @if ((int) $item->statut === 1)
                                                <form action="{{ route('professionnels.desactiver', $item->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">Désactiver</button>
                                                </form>
                                            @else
                                                <form action="{{ route('professionnels.activer', $item->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Activer</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                        <p class="mb-2 mb-md-0">
                            Affichage de {{ $professionnels->firstItem() }} à {{ $professionnels->lastItem() }} sur {{ $professionnels->total() }} professionnels
                        </p>
                        <div>
                            {{ $professionnels->links() }}
                        </div>
                    </div>
                @else
                    <p>Aucun professionnel trouvé.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createProfessionnelModal" tabindex="-1" role="dialog" aria-labelledby="createProfessionnelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProfessionnelModalLabel">Ajouter un professionnel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('professionnels.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label>Nom</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label>Prénoms</label>
                            <input type="text" class="form-control" name="prenoms" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label>Rôle</label>
                            <input type="text" class="form-control" name="role" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label>Mobile</label>
                            <input type="text" class="form-control" name="mobile" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-12 mb-3">
                            <label>Statut</label>
                            <select name="statut" class="form-control">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.footer')
