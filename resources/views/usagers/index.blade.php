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
                <form method="GET" action="{{ route('index-usagers') }}" class="mb-4">
                    <input type="hidden" name="per_page" value="{{ $per_page ?? 50 }}">
                    <div class="row">
                        @php
                            $filterOptions = [
                                '' => 'Tous',
                                'with' => 'Avec',
                                'without' => 'Sans',
                            ];
                            $filters = $presence_filters ?? [];
                        @endphp
                        <div class="col-md-2">
                            <label>Véhicule</label>
                            <select name="vehicule_filter" class="form-control">
                                @foreach($filterOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($filters['vehicule_filter'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Type d'alerte</label>
                            <select name="alerte_filter" class="form-control">
                                @foreach($filterOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($filters['alerte_filter'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Abonnement actif</label>
                            <select name="abonnement_filter" class="form-control">
                                @foreach($filterOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($filters['abonnement_filter'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Annonce</label>
                            <select name="annonce_filter" class="form-control">
                                @foreach($filterOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($filters['annonce_filter'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Document</label>
                            <select name="document_filter" class="form-control">
                                @foreach($filterOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($filters['document_filter'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                            <a href="{{ route('index-usagers') }}" class="btn btn-light">Réinitialiser</a>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">
                        Page {{ $usagers->currentPage() }} - {{ $usagers->count() }} usager(s) affiché(s)
                    </div>
                    <form method="GET" action="{{ route('index-usagers') }}" class="d-flex align-items-center">
                        @foreach(($presence_filters ?? []) as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <label class="mb-0 mr-2">Par page</label>
                        <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()" style="width: auto;">
                            @foreach([25, 50, 100] as $option)
                                <option value="{{ $option }}" {{ ($per_page ?? 50) == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @if($usagers->count() > 0 )
                    <form id="bulk-forfait-form" action="{{ route('usagers.bulk-change-forfait') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-5">
                                <label>Forfait à attribuer</label>
                                <select name="forfait_id" class="form-control" required>
                                    <option value="">Choisir un forfait</option>
                                    @foreach($forfait_usagers as $forfait)
                                        <option value="{{ $forfait->id }}">
                                            {{ $forfait->libelle }} - {{ number_format($forfait->prix, 0, ',', ' ') }} FCFA - {{ $forfait->duree }} mois(s)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted mb-2"><span id="selected-users-count">0</span> usager(s) sélectionné(s)</div>
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Attribuer ce forfait aux usagers sélectionnés ?')">
                                    Attribuer globalement
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 50px;"><input type="checkbox" id="select-all-usagers"></th>
                                    <th scope="col">#</th>
                                    {{-- <th scope="col">Avatar</th> --}}
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénoms</th>
                                    <th scope="col">Mobile</th>
                                    <th scope="col">E-mail</th>
                                    <th scope="col">Véhicules</th>
                                    <th scope="col">Alertes</th>
                                    <th scope="col">Abonnement</th>
                                    <th scope="col">Annonces</th>
                                    <th scope="col">Documents</th>
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
                                        <td>
                                            <input type="checkbox" name="user_ids[]" value="{{ $item->id }}" form="bulk-forfait-form" class="js-usager-checkbox">
                                        </td>
                                        <td>{{ (($usagers->currentPage() - 1) * $usagers->perPage()) + $key + 1 }}</td>
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
                                            <span class="badge badge-{{ $item->vehicules_count > 0 ? 'success' : 'secondary' }}">
                                                {{ $item->vehicules_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $item->alerts_count > 0 ? 'success' : 'secondary' }}">
                                                {{ $item->alerts_count }}
                                            </span>
                                        </td>
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
                                            <span class="badge badge-{{ $item->annonces_count > 0 ? 'success' : 'secondary' }}">
                                                {{ $item->annonces_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $item->autodocs_count > 0 ? 'success' : 'secondary' }}">
                                                {{ $item->autodocs_count }}
                                            </span>
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
                    <div class="mt-3">
                        {{ $usagers->links() }}
                    </div>
                    @else
                        <p>Aucun usager trouvé.</p>
                @endif
            </div>
        </div>
    </div>
</div>


<script>
    (function () {
        var selectAll = document.getElementById('select-all-usagers');
        var count = document.getElementById('selected-users-count');
        var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.js-usager-checkbox'));

        function refreshCount() {
            var selected = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
            if (count) {
                count.textContent = selected;
            }
            if (selectAll) {
                selectAll.checked = selected > 0 && selected === checkboxes.length;
                selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
                refreshCount();
            });
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', refreshCount);
        });
        refreshCount();
    })();
</script>
@include('layouts.footer')
