@include('layouts.header')
@include('layouts.menu')

{{-- @include('layouts.fileariane') --}}

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
        
        <!-- Informations du concessionnaire -->
        <div class="card text-left mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        @if($concessionnaire->logo)
                            <img src="https://concessionnaire.tooauto.com/concessionnaire/logo/{{ $concessionnaire->logo }}" 
                                 alt="Logo" 
                                 class="img-fluid rounded" 
                                 style="max-height: 100px;">
                        @else
                            <div style="width: 100px; height: 100px; background-color: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                <i class="nav-icon i-Image text-muted" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h4 class="card-title mb-2">{{ $concessionnaire->name }}</h4>
                        <p class="text-muted mb-1">
                            <i class="nav-icon i-Map-Marker font-weight-bold"></i> 
                            {{ $concessionnaire->adresse ?? "N/A" }}
                        </p>
                        <p class="text-muted mb-1">
                            <i class="nav-icon i-Building font-weight-bold"></i> 
                            {{ $concessionnaire->ville->libelle ?? "N/A" }}, {{ $concessionnaire->pays->libelle ?? "N/A" }}
                        </p>
                        @if($concessionnaire->contact)
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Phone font-weight-bold"></i> 
                                <a href="tel:{{ $concessionnaire->contact }}" class="text-primary">{{ $concessionnaire->contact }}</a>
                            </p>
                        @endif
                        @if($concessionnaire->email)
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Email font-weight-bold"></i> 
                                <a href="mailto:{{ $concessionnaire->email }}" class="text-primary">{{ $concessionnaire->email }}</a>
                            </p>
                        @endif
                        @if($concessionnaire->userConcessionnaire)
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="nav-icon i-User font-weight-bold"></i> Propriétaire
                            </h6>
                            <p class="text-muted mb-1">
                                <strong>{{ $concessionnaire->userConcessionnaire->nom_complet }}</strong>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Email font-weight-bold"></i> 
                                <a href="mailto:{{ $concessionnaire->userConcessionnaire->email }}" class="text-primary">{{ $concessionnaire->userConcessionnaire->email }}</a>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Phone font-weight-bold"></i> 
                                <a href="tel:{{ $concessionnaire->userConcessionnaire->telephone_complet }}" class="text-primary">{{ $concessionnaire->userConcessionnaire->telephone_complet }}</a>
                            </p>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="{{ route('concessionnaires.index') }}" class="btn btn-secondary">
                            <i class="nav-icon i-Arrow-Left font-weight-bold"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des véhicules -->
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="nav-icon i-Car font-weight-bold"></i> Véhicules du concessionnaire
                    <span class="badge badge-success ml-2">{{ $vehicules->count() }}</span>
                </h4>
                
                @if($vehicules->isNotEmpty())
                    <div class="row">
                        @foreach($vehicules as $vehicule)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <!-- Image du véhicule -->
                                    <div class="card-img-top" style="height: 200px; overflow: hidden; position: relative;">
                                        @if($vehicule->premiere_photo)
                                            <img src="https://concessionnaire.tooauto.com/{{ $vehicule->premiere_photo }}" 
                                                 alt="{{ $vehicule->name }}" 
                                                 class="img-fluid" 
                                                 style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div style="width: 100%; height: 100%; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                <i class="nav-icon i-Car text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <!-- Badge nombre de photos -->
                                        @if($vehicule->photos && count($vehicule->photos) > 1)
                                            <span class="badge badge-info" style="position: absolute; top: 10px; right: 10px;">
                                                <i class="nav-icon i-Image font-weight-bold"></i> {{ count($vehicule->photos) }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="card-body d-flex flex-column">
                                        <!-- Titre et marque -->
                                        <h5 class="card-title">
                                            {{ $vehicule->name }}
                                            <br><small class="text-muted">{{ $vehicule->marque->libelle ?? "N/A" }} - {{ $vehicule->modele }}</small>
                                        </h5>
                                        
                                        <!-- Prix -->
                                        <div class="mb-2">
                                            <span class="badge badge-success" style="font-size: 1.1rem;">
                                                <i class="nav-icon i-Money font-weight-bold"></i> {{ $vehicule->prix_formate }}
                                            </span>
                                        </div>
                                        
                                        <!-- Description -->
                                        <p class="card-text flex-grow-1">
                                            {{ Str::limit($vehicule->description, 100) }}
                                        </p>
                                        
                                        <!-- Garantie -->
                                        @if($vehicule->garantie)
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="nav-icon i-Shield font-weight-bold"></i> 
                                                    <strong>Garantie:</strong> {{ $vehicule->garantie }}
                                                </small>
                                            </div>
                                        @endif
                                        
                                        <!-- Date de création -->
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="nav-icon i-Calendar font-weight-bold"></i> 
                                                Ajouté le {{ $vehicule->created_at ? $vehicule->created_at->format('d/m/Y') : "N/A" }}
                                            </small>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="mt-auto">
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-toggle="modal" 
                                                    data-target="#viewModal{{ $vehicule->id }}"
                                                    title="Voir les détails">
                                                <i class="nav-icon i-Eye font-weight-bold"></i> Détails
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal pour voir les détails du véhicule -->
                            <div class="modal fade" id="viewModal{{ $vehicule->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $vehicule->name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <!-- Images du véhicule -->
                                                <div class="col-md-6">
                                                    @if($vehicule->photos && count($vehicule->photos) > 0)
                                                        <div id="carousel{{ $vehicule->id }}" class="carousel slide" data-ride="carousel">
                                                            <div class="carousel-inner">
                                                                @foreach($vehicule->photos as $index => $photo)
                                                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                                        <img src="https://concessionnaire.tooauto.com/{{ $photo }}" 
                                                                             class="d-block w-100" 
                                                                             alt="Photo {{ $index + 1 }}"
                                                                             style="height: 300px; object-fit: cover;">
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            @if(count($vehicule->photos) > 1)
                                                                <a class="carousel-control-prev" href="#carousel{{ $vehicule->id }}" role="button" data-slide="prev">
                                                                    <span class="carousel-control-prev-icon"></span>
                                                                </a>
                                                                <a class="carousel-control-next" href="#carousel{{ $vehicule->id }}" role="button" data-slide="next">
                                                                    <span class="carousel-control-next-icon"></span>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div style="height: 300px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                                            <i class="nav-icon i-Car text-muted" style="font-size: 4rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Informations du véhicule -->
                                                <div class="col-md-6">
                                                    <h6><strong>Marque:</strong> {{ $vehicule->marque->libelle ?? "N/A" }}</h6>
                                                    <h6><strong>Modèle:</strong> {{ $vehicule->modele }}</h6>
                                                    <h6><strong>Prix:</strong> 
                                                        <span class="badge badge-success">{{ $vehicule->prix_formate }}</span>
                                                    </h6>
                                                    
                                                    @if($vehicule->garantie)
                                                        <h6><strong>Garantie:</strong> {{ $vehicule->garantie }}</h6>
                                                    @endif
                                                    
                                                    <h6><strong>Description:</strong></h6>
                                                    <p class="text-muted">{{ $vehicule->description }}</p>
                                                    
                                                    @if($vehicule->fichier)
                                                        <h6><strong>Fichier:</strong></h6>
                                                        <a href="https://concessionnaire.tooauto.com/{{ $vehicule->fichier }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="nav-icon i-Download font-weight-bold"></i> Télécharger
                                                        </a>
                                                    @endif
                                                    
                                                    <hr>
                                                    <small class="text-muted">
                                                        <i class="nav-icon i-Calendar font-weight-bold"></i> 
                                                        Ajouté le {{ $vehicule->created_at ? $vehicule->created_at->format('d/m/Y à H:i') : "N/A" }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="nav-icon i-Information font-weight-bold"></i> 
                        Aucun véhicule trouvé pour ce concessionnaire !
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
