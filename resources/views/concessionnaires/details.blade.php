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
        
        <!-- Informations du concessionnaire -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0" style="color: #fff;">
                        <i class="nav-icon i-Shop"></i> {{ $concessionnaire->name }}
                    </h4>
                    <a href="{{ route('concessionnaires.liste') }}" class="btn btn-light btn-sm">
                        <i class="nav-icon i-Arrow-Left"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if($concessionnaire->logo)
                            <img src="https://concessionnaire.tooauto.com/{{ $concessionnaire->logo }}" 
                                 alt="Logo {{ $concessionnaire->name }}" 
                                 class="img-fluid rounded" 
                                 style="max-height: 150px; object-fit: cover;"
                                 onerror="this.style.display='none'">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="nav-icon i-Shop text-muted" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong><i class="nav-icon i-Map-Marker"></i> Adresse:</strong><br>
                                    <span class="text-muted">{{ $concessionnaire->adresse ?? 'Non renseignée' }}</span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="nav-icon i-Phone"></i> Contact:</strong><br>
                                    <span class="text-muted">{{ $concessionnaire->contact ?? 'Non renseigné' }}</span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="nav-icon i-Email"></i> Email:</strong><br>
                                    <span class="text-muted">{{ $concessionnaire->email ?? 'Non renseigné' }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong><i class="nav-icon i-Car"></i> Nombre de véhicules:</strong><br>
                                    <span class="badge badge-info badge-lg">{{ $concessionnaire->vehicules->count() }} véhicule(s)</span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="nav-icon i-Check"></i> Statut:</strong><br>
                                    @if($concessionnaire->statut == 1)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-danger">Inactif</span>
                                    @endif
                                </p>
                                @if($concessionnaire->description)
                                    <p class="mb-2">
                                        <strong><i class="nav-icon i-File-Text"></i> Description:</strong><br>
                                        <span class="text-muted">{{ $concessionnaire->description }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des véhicules -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="nav-icon i-Car"></i> Véhicules disponibles ({{ $concessionnaire->vehicules->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($concessionnaire->vehicules->isNotEmpty())
                    <div class="row">
                        @foreach($concessionnaire->vehicules as $vehicule)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card border h-100">
                                    <!-- Photo du véhicule -->
                                    @if($vehicule->getFirstPhoto())
                                        <div class="text-center p-3">
                                            <img src="https://concessionnaire.tooauto.com/{{ $vehicule->getFirstPhoto() }}" 
                                                 alt="{{ $vehicule->name }}" 
                                                 class="img-fluid rounded cursor-pointer" 
                                                 style="max-height: 200px; object-fit: cover; width: 100%; cursor: pointer;"
                                                 data-toggle="modal" 
                                                 data-target="#photosModal{{ $vehicule->id }}"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    @else
                                        <div class="text-center p-3">
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <i class="nav-icon i-Car text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="card-body">
                                        <!-- Informations du véhicule -->
                                        <h5 class="card-title">
                                            <strong>{{ $vehicule->name }}</strong>
                                        </h5>
                                        
                                        <div class="mb-3">
                                            @if($vehicule->marque)
                                                <span class="badge badge-primary">{{ $vehicule->marque->libelle }}</span>
                                            @endif
                                            <span class="badge badge-secondary">{{ $vehicule->modele }}</span>
                                        </div>
                                        
                                        <p class="mb-3">
                                            <strong class="text-success h5">{{ $vehicule->getFormattedPrice() }}</strong>
                                        </p>
                                        
                                        @if($vehicule->description)
                                            <p class="mb-3">
                                                <small class="text-muted">
                                                    {{ Str::limit($vehicule->description, 120) }}
                                                </small>
                                            </p>
                                        @endif
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted">
                                                <i class="nav-icon i-Photo"></i> 
                                                @if($vehicule->getPhotosCount() > 1)
                                                    <span class="text-primary" style="cursor: pointer;" 
                                                          data-toggle="modal" 
                                                          data-target="#photosModal{{ $vehicule->id }}">
                                                        {{ $vehicule->getPhotosCount() }} photo(s) - Cliquez pour voir toutes
                                                    </span>
                                                @else
                                                    {{ $vehicule->getPhotosCount() }} photo(s)
                                                @endif
                                            </small>
                                            @if($vehicule->garantie)
                                                <small class="text-info">
                                                    <i class="nav-icon i-Shield"></i> {{ $vehicule->garantie }}
                                                </small>
                                            @endif
                                        </div>

                                        @if($vehicule->fichier)
                                            <div class="mb-3">
                                                <a href="https://concessionnaire.tooauto.com/{{ $vehicule->fichier }}" 
                                                   target="_blank" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="nav-icon i-File"></i> Voir le fichier
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="nav-icon i-Car" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">Aucun véhicule disponible</h5>
                        <p class="text-muted">Ce concessionnaire n'a pas encore de véhicules enregistrés.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les photos des véhicules -->
@foreach($concessionnaire->vehicules as $vehicule)
    @if($vehicule->getPhotosCount() > 0)
        <div class="modal fade" id="photosModal{{ $vehicule->id }}" tabindex="-1" role="dialog" aria-labelledby="photosModalLabel{{ $vehicule->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="photosModalLabel{{ $vehicule->id }}">
                            <i class="nav-icon i-Photo"></i> Photos de {{ $vehicule->name }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @foreach($vehicule->getPhotosArray() as $index => $photo)
                                <div class="col-md-6 mb-3">
                                    <div class="text-center">
                                        <img src="https://concessionnaire.tooauto.com/{{ $photo }}" 
                                             alt="{{ $vehicule->name }} - Photo {{ $index + 1 }}" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="max-height: 300px; object-fit: cover; width: 100%;"
                                             onerror="this.style.display='none'">
                                        <p class="mt-2 mb-0">
                                            <small class="text-muted">Photo {{ $index + 1 }}</small>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($vehicule->getPhotosCount() > 1)
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="nav-icon i-Photo"></i> 
                                    {{ $vehicule->getPhotosCount() }} photo(s) disponible(s)
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="nav-icon i-Close-Window"></i> Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@include('layouts.footer')
