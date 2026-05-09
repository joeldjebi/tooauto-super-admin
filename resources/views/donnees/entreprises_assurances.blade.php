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
                <h4 class="card-title mb-3">Les entreprises d'assurance</h4>

                @if($entreprises_assurances->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Logo</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Situation géographique</th>
                                    <th scope="col">Téléphones</th>
                                    <th scope="col">Site</th>
                                    <th scope="col">Map</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entreprises_assurances as $key => $item)
                                    <div class="modal fade" id="editAssurance{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Modifier l'entreprise d'assurance</h5>
                                                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update-entreprise-assurance', ['id' => $item->id]) }}" method="post" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Nom</label>
                                                                    <input class="form-control" name="nom" type="text" value="{{ old('nom') ?? $item->nom }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Logo</label>
                                                                    <input class="form-control" name="logo" type="file" accept="image/*">
                                                                    @if($item->logo)
                                                                        <small class="text-muted">Logo actuel : {{ $item->logo }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label>Situation géographique</label>
                                                                    <input class="form-control" name="situation_geographique" type="text" value="{{ old('situation_geographique') ?? $item->situation_geographique }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Lien map</label>
                                                                    <input class="form-control" name="lien_map" type="url" value="{{ old('lien_map') ?? $item->lien_map }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Site internet</label>
                                                                    <input class="form-control" name="site_internet" type="url" value="{{ old('site_internet') ?? $item->site_internet }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <label>Téléphones</label>
                                                        <div class="phone-list">
                                                            @forelse(($item->telephones ?? []) as $telephone)
                                                                <div class="row phone-row mb-2">
                                                                    <div class="col-md-7">
                                                                        <input class="form-control" name="telephone_numbers[]" type="text" value="{{ $telephone['numero'] ?? '' }}" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control" name="telephone_types[]" required>
                                                                            <option value="fix" {{ ($telephone['type'] ?? '') == 'fix' ? 'selected' : '' }}>Fix</option>
                                                                            <option value="mobile" {{ ($telephone['type'] ?? '') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                                                                            <option value="whatsapp" {{ ($telephone['type'] ?? '') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <button type="button" class="btn btn-danger remove-phone">×</button>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="row phone-row mb-2">
                                                                    <div class="col-md-7">
                                                                        <input class="form-control" name="telephone_numbers[]" type="text" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control" name="telephone_types[]" required>
                                                                            <option value="mobile">Mobile</option>
                                                                            <option value="fix">Fix</option>
                                                                            <option value="whatsapp">WhatsApp</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-1">
                                                                        <button type="button" class="btn btn-danger remove-phone">×</button>
                                                                    </div>
                                                                </div>
                                                            @endforelse
                                                        </div>
                                                        <button type="button" class="btn btn-outline-primary btn-sm add-phone mb-3">Ajouter un numéro</button>

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
                                            @if($item->logo_url)
                                                <img src="{{ $item->logo_url }}" alt="{{ $item->nom }}" width="45" height="45" style="object-fit:contain;">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->nom }}</td>
                                        <td>{{ $item->situation_geographique ?? '-' }}</td>
                                        <td>
                                            @forelse(($item->telephones ?? []) as $telephone)
                                                <div>{{ ucfirst($telephone['type'] ?? '') }} : {{ $telephone['numero'] ?? '' }}</div>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if($item->site_internet)
                                                <a href="{{ $item->site_internet }}" target="_blank">Voir</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->lien_map)
                                                <a href="{{ $item->lien_map }}" target="_blank">Map</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#editAssurance{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-entreprise-assurance', ['id' => $item->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Supprimer cette entreprise d\'assurance ?')">
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
                    <p>Aucune entreprise d'assurance enregistrée.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12">
        <h4>Ajouter une entreprise d'assurance</h4>
        <div class="card mb-5">
            <div class="card-body">
                <form action="{{ route('store-entreprise-assurance') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Nom</label>
                        <input class="form-control" name="nom" type="text" value="{{ old('nom') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Logo</label>
                        <input class="form-control" name="logo" type="file" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Situation géographique</label>
                        <input class="form-control" name="situation_geographique" type="text" value="{{ old('situation_geographique') }}">
                    </div>
                    <div class="form-group">
                        <label>Lien map</label>
                        <input class="form-control" name="lien_map" type="url" value="{{ old('lien_map') }}">
                    </div>
                    <div class="form-group">
                        <label>Site internet</label>
                        <input class="form-control" name="site_internet" type="url" value="{{ old('site_internet') }}">
                    </div>

                    <label>Téléphones</label>
                    <div class="phone-list">
                        <div class="row phone-row mb-2">
                            <div class="col-md-7">
                                <input class="form-control" name="telephone_numbers[]" type="text" placeholder="Numéro" required>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" name="telephone_types[]" required>
                                    <option value="mobile">Mobile</option>
                                    <option value="fix">Fix</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-phone">×</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm add-phone mb-3">Ajouter un numéro</button>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary pd-x-20">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('add-phone')) {
            var container = event.target.previousElementSibling;
            var row = document.createElement('div');
            row.className = 'row phone-row mb-2';
            row.innerHTML = '<div class="col-md-7"><input class="form-control" name="telephone_numbers[]" type="text" placeholder="Numéro" required></div><div class="col-md-4"><select class="form-control" name="telephone_types[]" required><option value="mobile">Mobile</option><option value="fix">Fix</option><option value="whatsapp">WhatsApp</option></select></div><div class="col-md-1"><button type="button" class="btn btn-danger remove-phone">×</button></div>';
            container.appendChild(row);
        }

        if (event.target.classList.contains('remove-phone')) {
            var list = event.target.closest('.phone-list');
            var rows = list.querySelectorAll('.phone-row');

            if (rows.length > 1) {
                event.target.closest('.phone-row').remove();
            }
        }
    });
</script>

@include('layouts.footer')
