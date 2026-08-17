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
                <h4 class="card-title mb-3">Les vehicules</h4>
                @php
                    $filters = $filters ?? [];
                    $perPageOptions = [25, 50, 100];
                    $currentPerPage = $per_page ?? 50;
                @endphp
                <form method="GET" action="{{ route('index-vehicule') }}" class="mb-4">
                    <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Véhicule</label>
                            <select name="vehicule_id" class="form-control js-select-filter-ajax" data-placeholder="Tous les véhicules" data-url="{{ route('vehicule.filter-options') }}">
                                <option value=""></option>
                                @if($selected_vehicule_filter_option)
                                    <option value="{{ $selected_vehicule_filter_option->id }}" selected>
                                        {{ ($selected_vehicule_filter_option->matricule ?: 'Sans matricule') . ' - ' . ($selected_vehicule_filter_option->carte_grise ?: 'Sans carte grise') . ' - ' . (trim(($selected_vehicule_filter_option->user->nom ?? '') . ' ' . ($selected_vehicule_filter_option->user->prenoms ?? '')) ?: 'Usager non renseigné') }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Usager</label>
                            <select name="user_id" class="form-control js-select-filter-ajax" data-placeholder="Tous les usagers" data-url="{{ route('usager-vehicule.filter-options') }}">
                                <option value=""></option>
                                @if($selected_usager_filter_option)
                                    <option value="{{ $selected_usager_filter_option->id }}" selected>
                                        {{ (trim(($selected_usager_filter_option->nom ?? '') . ' ' . ($selected_usager_filter_option->prenoms ?? '')) ?: 'Sans nom') . ($selected_usager_filter_option->mobile ? ' - ' . $selected_usager_filter_option->mobile : '') }}
                                    </option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Marque</label>
                            <select name="marque_id" class="form-control js-select-filter" data-placeholder="Toutes les marques">
                                <option value="">Toutes les marques</option>
                                @foreach($marque_filter_options as $marqueOption)
                                    <option value="{{ $marqueOption->id }}" {{ (string) ($filters['marque_id'] ?? '') === (string) $marqueOption->id ? 'selected' : '' }}>{{ $marqueOption->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Type véhicule</label>
                            <select name="type_de_vehicule_id" class="form-control js-select-filter" data-placeholder="Tous les types">
                                <option value="">Tous les types</option>
                                @foreach($type_vehicule_filter_options as $typeOption)
                                    <option value="{{ $typeOption->id }}" {{ (string) ($filters['type_de_vehicule_id'] ?? '') === (string) $typeOption->id ? 'selected' : '' }}>{{ $typeOption->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Carburant</label>
                            <select name="type_de_carburant_id" class="form-control js-select-filter" data-placeholder="Tous les carburants">
                                <option value="">Tous les carburants</option>
                                @foreach($carburant_filter_options as $carburantOption)
                                    <option value="{{ $carburantOption->id }}" {{ (string) ($filters['type_de_carburant_id'] ?? '') === (string) $carburantOption->id ? 'selected' : '' }}>{{ $carburantOption->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('index-vehicule') }}" class="btn btn-light btn-sm">Réinitialiser</a>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">Page {{ $vehicules->currentPage() }} - {{ $vehicules->count() }} véhicule(s) affiché(s)</div>
                    <form method="GET" action="{{ route('index-vehicule') }}" class="d-flex align-items-center">
                        @foreach($filters as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <label class="mb-0 mr-2">Par page</label>
                        <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()" style="width: auto;">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}" {{ $currentPerPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @if($vehicules->count() > 0 )
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Carte grise</th>
                                    <th scope="col">Matricule</th>
                                    <th scope="col">Nom et prénoms usager</th>
                                    <th scope="col">Contact de l'usager</th>
                                    <th scope="col">Type de vehicule</th>
                                    <th scope="col">Marque</th>
                                    <th scope="col">Type de carburant</th>
                                    <th scope="col">Couleur</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicules as $key => $item)
                                @php
                                    $jsonImages = $item->photos;
                                    $imagePaths = json_decode($jsonImages, true);

                                @endphp
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Photos du véhicule</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>
                                                @if (is_array($imagePaths))
                                                    @foreach ($imagePaths as $path)
                                                        <img src="{{ config('app.url_api_usager') }}/{{ $path }}" alt="Image" style="width:150px; margin:10px;">
                                                    @endforeach
                                                @else
                                                    <p>Erreur : Impossible de décoder le JSON.</p>
                                                @endif
                                                
                                                </p>
                                                
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ (($vehicules->currentPage() - 1) * $vehicules->perPage()) + $key + 1 }}</td>
                                        <td>{{ $item->carte_grise }}</td>
                                        <td>{{ $item->matricule }}</td>
                                        <td>{{ $item->user->nom ?? "" }} {{ $item->user->prenoms ?? "" }}</td>
                                        <td>{{ $item->user->mobile ?? "" }}</td>
                                        <td>{{ $item->typeDeVehicule->libelle ?? '' }}</td>
                                        <td>{{ $item->marque->libelle ?? '' }}</td>
                                        <td>{{ $item->typeDeCarburant->libelle ?? '' }}</td>
                                        <td>{{ $item->couleur }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Eye font-weight-bold"></i>
                                            </a>
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $vehicules->links() }}
                    </div>
                    @else
                        <p>Aucun véhicule enregistré !</p>
                @endif
            </div>
        </div>
    </div>
</div>





@include('layouts.footer')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('.js-select-filter').select2({
            allowClear: true,
            width: '100%',
            placeholder: function () {
                return $(this).data('placeholder');
            }
        });

        $('.js-select-filter-ajax').select2({
            allowClear: true,
            width: '100%',
            minimumInputLength: 1,
            placeholder: function () {
                return $(this).data('placeholder');
            },
            ajax: {
                delay: 250,
                dataType: 'json',
                url: function () {
                    return $(this).data('url');
                },
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    return data;
                }
            }
        });
    });
</script>
