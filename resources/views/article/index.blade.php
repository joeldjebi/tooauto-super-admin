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
                <h4 class="card-title mb-3">Les articles</h4>
                @php($filters = $filters ?? [])
                <form method="GET" action="{{ route('index-article') }}" class="mb-4">
                    <input type="hidden" name="per_page" value="{{ $per_page ?? 50 }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Recherche</label>
                            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Libellé ou établissement">
                        </div>
                        <div class="col-md-3">
                            <label>Établissement</label>
                            <input type="text" name="etablissement" class="form-control" value="{{ $filters['etablissement'] ?? '' }}" placeholder="Nom établissement">
                        </div>
                        <div class="col-md-2">
                            <label>Prix min</label>
                            <input type="number" name="prix_min" class="form-control" value="{{ $filters['prix_min'] ?? '' }}" min="0">
                        </div>
                        <div class="col-md-2">
                            <label>Prix max</label>
                            <input type="number" name="prix_max" class="form-control" value="{{ $filters['prix_max'] ?? '' }}" min="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                            <a href="{{ route('index-article') }}" class="btn btn-light">Réinitialiser</a>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">Page {{ $articles->currentPage() }} - {{ $articles->count() }} article(s) affiché(s)</div>
                    <form method="GET" action="{{ route('index-article') }}" class="d-flex align-items-center">
                        @foreach(($filters ?? []) as $name => $value)
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
                @if($articles->count() > 0 )
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">image</th>
                                    <th scope="col">Libelle</th>
                                    <th scope="col">Prix</th>
                                    <th scope="col">Établissement</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($articles as $key => $item)
                                @php
                                    $montant = $item->amount;
                                    $montant_formate = number_format($montant, 0, ',', ' '); // Ajoute un espace comme séparateur de milliers
                                    $montant_final = $montant_formate . ' FCFA'; // Ajoute la devise
                                @endphp
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Détails de l'article</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            </div>
                                            <div class="modal-body">
                                                <h4 class="card-title mb-3">Description de l'article</h4>
                                                <p>{!! html_entity_decode($item->description) !!}</p>

                                                <h4 class="card-title mb-3">Photo de l'article</h4>
                                                <p>
                                                    <img 
                                                        width="250" 
                                                        height="250" 
                                                        src="{{ config('app.url_etablissement_pro') }}/articles/image/{{ $item->image }}" 
                                                        alt="{{ $item->libelle }}"
                                                    >
                                                </p>
                                                
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ (($articles->currentPage() - 1) * $articles->perPage()) + $key + 1 }}</td>
                                        <td>
                                            <img 
                                                width="50" 
                                                height="50" 
                                                src="{{ config('app.url_etablissement_pro') }}/articles/image/{{ $item->image }}" 
                                                alt="{{ $item->libelle }}"
                                            >
                                        </td>
                                        <td>{{ $item->libelle }}</td>
                                        <td>{{ $montant_final }}</td>
                                        <td>{{ $item->etablissement->name ?? "" }}</td>
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
                        {{ $articles->links() }}
                    </div>
                    @else
                        <p>Aucun article enregistré !</p>
                @endif
            </div>
        </div>
    </div>
</div>





@include('layouts.footer')
