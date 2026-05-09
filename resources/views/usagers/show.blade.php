@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
    </div>
</div>

<!-- Informations générales de l'usager -->
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="nav-icon i-User"></i> Détails de l'usager
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="nav-icon i-User"></i> Informations personnelles</h6>
                        <p><strong>Nom:</strong> {{ $usager->nom ?? 'N/A' }}</p>
                        <p><strong>Prénoms:</strong> {{ $usager->prenoms ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $usager->email ?? 'N/A' }}</p>
                        <p><strong>Téléphone:</strong> {{ $usager->indicatif ?? 'N/A' }} {{ $usager->mobile ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="nav-icon i-Calendar"></i> Informations temporelles</h6>
                        <p><strong>Date de création:</strong> {{ \Carbon\Carbon::parse($usager->created_at)->format('d/m/Y à H:i:s') }}</p>
                        <p><strong>Dernière modification:</strong> {{ \Carbon\Carbon::parse($usager->updated_at)->format('d/m/Y à H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">{{ $annonces->count() }}</h5>
                <p class="card-text">Annonces</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">{{ $alerts->count() }}</h5>
                <p class="card-text">Alertes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">{{ $vehicules->count() }}</h5>
                <p class="card-text">Véhicules</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">{{ $annonce_concessionnaires->count() }}</h5>
                <p class="card-text">Demandes concessionnaires</p>
            </div>
        </div>
    </div>
</div>

<!-- Annonces -->
<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="nav-icon i-File-Text"></i> Annonces ({{ $annonces->total() }})
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-8">
                                <form method="GET" action="{{ route('usager.show', $usager->id) }}" class="d-flex">
                                    <input type="hidden" name="vehicule_filter" value="{{ request('vehicule_filter') }}">
                                    <input type="hidden" name="alert_filter" value="{{ request('alert_filter') }}">
                                    <input type="hidden" name="concessionnaire_filter" value="{{ request('concessionnaire_filter') }}">
                                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                    <input type="text" 
                                           name="annonce_filter" 
                                           value="{{ request('annonce_filter') }}" 
                                           placeholder="Rechercher dans les annonces..." 
                                           class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-primary btn-sm ml-2">
                                        <i class="nav-icon i-Magnifying-Glass"></i>
                                    </button>
                                    @if(request('annonce_filter'))
                                        <a href="{{ route('usager.show', $usager->id) }}?vehicule_filter={{ request('vehicule_filter') }}&alert_filter={{ request('alert_filter') }}&concessionnaire_filter={{ request('concessionnaire_filter') }}&per_page={{ request('per_page', 10) }}" 
                                           class="btn btn-secondary btn-sm ml-1">
                                            <i class="nav-icon i-Close-Window"></i>
                                        </a>
                                    @endif
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form method="GET" action="{{ route('usager.show', $usager->id) }}" class="d-flex">
                                    <input type="hidden" name="annonce_filter" value="{{ request('annonce_filter') }}">
                                    <input type="hidden" name="vehicule_filter" value="{{ request('vehicule_filter') }}">
                                    <input type="hidden" name="alert_filter" value="{{ request('alert_filter') }}">
                                    <input type="hidden" name="concessionnaire_filter" value="{{ request('concessionnaire_filter') }}">
                                    <select name="per_page" onchange="this.form.submit()" class="form-control form-control-sm">
                                        <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5/page</option>
                                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10/page</option>
                                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25/page</option>
                                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50/page</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($annonces->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Libellé</th>
                                    <th>Image</th>
                                    <th>Marque/Modèle</th>
                                    <th>Mobile</th>
                                    <th>WhatsApp</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($annonces as $annonce)
                                    <tr>
                                        <td>{{ $annonce->id }}</td>
                                        <td>
                                            <strong>{{ $annonce->libelle }}</strong>
                                            @if($annonce->description)
                                                <br><small class="text-muted">{{ Str::limit($annonce->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($annonce->image)
                                                <img src="{{ asset('images/annonces/' . $annonce->image) }}" 
                                                     alt="Image annonce" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                     onerror="this.style.display='none'">
                                            @else
                                                <span class="text-muted">Aucune image</span>
                                            @endif
                                        </td>
                                        <td>{{ $annonce->modele ?? 'N/A' }}</td>
                                        <td>{{ $annonce->mobile ?? 'N/A' }}</td>
                                        <td>
                                            @if($annonce->is_whatsapp)
                                                <span class="badge badge-success">Oui</span>
                                            @else
                                                <span class="badge badge-secondary">Non</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($annonce->created_at)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($annonce->statut == 1)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination optimisée pour les annonces -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $annonces->firstItem() ?? 0 }} à {{ $annonces->lastItem() ?? 0 }} 
                            sur {{ $annonces->total() }} résultats
                        </div>
                        <div>
                            {{ $annonces->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="nav-icon i-File-Text" style="font-size: 3rem; color: #ccc;"></i>
                        <h6 class="mt-2 text-muted">Aucune annonce</h6>
                        @if(request('annonce_filter'))
                            <p class="text-muted">Aucun résultat pour "{{ request('annonce_filter') }}"</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Véhicules -->
<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="nav-icon i-Car"></i> Véhicules ({{ $vehicules->total() }})
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-8">
                                <form method="GET" action="{{ route('usager.show', $usager->id) }}" class="d-flex">
                                    <input type="hidden" name="annonce_filter" value="{{ request('annonce_filter') }}">
                                    <input type="hidden" name="alert_filter" value="{{ request('alert_filter') }}">
                                    <input type="hidden" name="concessionnaire_filter" value="{{ request('concessionnaire_filter') }}">
                                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                    <input type="text" 
                                           name="vehicule_filter" 
                                           value="{{ request('vehicule_filter') }}" 
                                           placeholder="Rechercher dans les véhicules..." 
                                           class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-primary btn-sm ml-2">
                                        <i class="nav-icon i-Magnifying-Glass"></i>
                                    </button>
                                    @if(request('vehicule_filter'))
                                        <a href="{{ route('usager.show', $usager->id) }}?annonce_filter={{ request('annonce_filter') }}&alert_filter={{ request('alert_filter') }}&concessionnaire_filter={{ request('concessionnaire_filter') }}&per_page={{ request('per_page', 10) }}" 
                                           class="btn btn-secondary btn-sm ml-1">
                                            <i class="nav-icon i-Close-Window"></i>
                                        </a>
                                    @endif
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form method="GET" action="{{ route('usager.show', $usager->id) }}" class="d-flex">
                                    <input type="hidden" name="annonce_filter" value="{{ request('annonce_filter') }}">
                                    <input type="hidden" name="vehicule_filter" value="{{ request('vehicule_filter') }}">
                                    <input type="hidden" name="alert_filter" value="{{ request('alert_filter') }}">
                                    <input type="hidden" name="concessionnaire_filter" value="{{ request('concessionnaire_filter') }}">
                                    <select name="per_page" onchange="this.form.submit()" class="form-control form-control-sm">
                                        <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5/page</option>
                                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10/page</option>
                                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25/page</option>
                                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50/page</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($vehicules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Matricule</th>
                                    <th>Carte Grise</th>
                                    <th>Marque/Modèle</th>
                                    <th>Couleur</th>
                                    <th>Photos</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicules as $vehicule)
                                    <tr>
                                        <td>{{ $vehicule->id }}</td>
                                        <td><strong>{{ $vehicule->matricule }}</strong></td>
                                        <td>{{ $vehicule->carte_grise }}</td>
                                        <td>{{ $vehicule->modele ?? 'N/A' }}</td>
                                        <td>{{ $vehicule->couleur ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $photos = json_decode($vehicule->photos, true);
                                            @endphp
                                            @if($photos && is_array($photos) && count($photos) > 0)
                                                <span class="badge badge-primary">{{ count($photos) }} photo(s)</span>
                                            @else
                                                <span class="text-muted">Aucune photo</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($vehicule->created_at)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($vehicule->statut == 1)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination optimisée pour les véhicules -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $vehicules->firstItem() ?? 0 }} à {{ $vehicules->lastItem() ?? 0 }} 
                            sur {{ $vehicules->total() }} résultats
                        </div>
                        <div>
                            {{ $vehicules->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="nav-icon i-Car" style="font-size: 3rem; color: #ccc;"></i>
                        <h6 class="mt-2 text-muted">Aucun véhicule</h6>
                        @if(request('vehicule_filter'))
                            <p class="text-muted">Aucun résultat pour "{{ request('vehicule_filter') }}"</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Alertes -->
<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="nav-icon i-Alert"></i> Alertes ({{ $alerts->total() }})
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('usager.show', $usager->id) }}" class="d-flex">
                            <input type="hidden" name="annonce_filter" value="{{ request('annonce_filter') }}">
                            <input type="hidden" name="vehicule_filter" value="{{ request('vehicule_filter') }}">
                            <input type="hidden" name="concessionnaire_filter" value="{{ request('concessionnaire_filter') }}">
                            <input type="text" 
                                   name="alert_filter" 
                                   value="{{ request('alert_filter') }}" 
                                   placeholder="Rechercher dans les alertes..." 
                                   class="form-control form-control-sm">
                            <button type="submit" class="btn btn-primary btn-sm ml-2">
                                <i class="nav-icon i-Magnifying-Glass"></i>
                            </button>
                            @if(request('alert_filter'))
                                <a href="{{ route('usager.show', $usager->id) }}?annonce_filter={{ request('annonce_filter') }}&vehicule_filter={{ request('vehicule_filter') }}&concessionnaire_filter={{ request('concessionnaire_filter') }}" 
                                   class="btn btn-secondary btn-sm ml-1">
                                    <i class="nav-icon i-Close-Window"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($alerts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Véhicule</th>
                                    <th>Type d'alerte</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Kilométrage</th>
                                    <th>Autres</th>
                                    <th>Date création</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alerts as $alert)
                                    <tr>
                                        <td>{{ $alert->id }}</td>
                                        <td>
                                            @if($alert->vehicule)
                                                <strong>{{ $alert->vehicule->matricule }}</strong>
                                            @else
                                                <span class="text-muted">Véhicule supprimé</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($alert->type_alert)
                                                <span class="badge badge-info">{{ $alert->type_alert->libelle }}</span>
                                            @else
                                                <span class="text-muted">Type inconnu (ID: {{ $alert->type_alert_id }})</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($alert->date_debut)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($alert->date_fin)->format('d/m/Y') }}</td>
                                        <td>{{ $alert->kilometrage ?? 'N/A' }}</td>
                                        <td>{{ $alert->autres ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($alert->created_at)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination pour les alertes -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $alerts->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="nav-icon i-Alert" style="font-size: 3rem; color: #ccc;"></i>
                        <h6 class="mt-2 text-muted">Aucune alerte</h6>
                        @if(request('alert_filter'))
                            <p class="text-muted">Aucun résultat pour "{{ request('alert_filter') }}"</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Demandes concessionnaires -->
<div class="row mt-4" style="margin-bottom: 30px;">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="nav-icon i-Shop"></i> Demandes concessionnaires ({{ $annonce_concessionnaires->total() }})
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('usager.show', $usager->id) }}" class="d-flex">
                            <input type="hidden" name="annonce_filter" value="{{ request('annonce_filter') }}">
                            <input type="hidden" name="vehicule_filter" value="{{ request('vehicule_filter') }}">
                            <input type="hidden" name="alert_filter" value="{{ request('alert_filter') }}">
                            <input type="text" 
                                   name="concessionnaire_filter" 
                                   value="{{ request('concessionnaire_filter') }}" 
                                   placeholder="Rechercher dans les demandes..." 
                                   class="form-control form-control-sm">
                            <button type="submit" class="btn btn-primary btn-sm ml-2">
                                <i class="nav-icon i-Magnifying-Glass"></i>
                            </button>
                            @if(request('concessionnaire_filter'))
                                <a href="{{ route('usager.show', $usager->id) }}?annonce_filter={{ request('annonce_filter') }}&vehicule_filter={{ request('vehicule_filter') }}&alert_filter={{ request('alert_filter') }}" 
                                   class="btn btn-secondary btn-sm ml-1">
                                    <i class="nav-icon i-Close-Window"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($annonce_concessionnaires->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type de demande</th>
                                    <th>Type de véhicule</th>
                                    <th>Marque</th>
                                    <th>Modèle</th>
                                    <th>Concessionnaire</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($annonce_concessionnaires as $demande)
                                    <tr>
                                        <td>{{ $demande->id }}</td>
                                        <td>
                                            @if($demande->typeDeDemande)
                                                <span class="badge badge-primary">{{ $demande->typeDeDemande->libelle }}</span>
                                            @else
                                                <span class="text-muted">Type inconnu (ID: {{ $demande->type_de_demande_id }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($demande->typeDeVehicule)
                                                <span class="badge badge-info">{{ $demande->typeDeVehicule->libelle }}</span>
                                            @else
                                                <span class="text-muted">Type inconnu (ID: {{ $demande->type_de_vehicule_id }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($demande->marque)
                                                <span class="badge badge-warning">{{ $demande->marque->libelle }}</span>
                                            @else
                                                <span class="text-muted">Marque inconnue (ID: {{ $demande->marque_id }})</span>
                                            @endif
                                        </td>
                                        <td>{{ $demande->modele ?? 'N/A' }}</td>
                                        <td>
                                            @if($demande->concessionnaire)
                                                <strong>{{ $demande->concessionnaire->name }}</strong>
                                            @else
                                                <span class="text-muted">Concessionnaire inconnu (ID: {{ $demande->concessionaire_id }})</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($demande->statut == 1)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination pour les demandes concessionnaires -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $annonce_concessionnaires->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="nav-icon i-Shop" style="font-size: 3rem; color: #ccc;"></i>
                        <h6 class="mt-2 text-muted">Aucune demande concessionnaire</h6>
                        @if(request('concessionnaire_filter'))
                            <p class="text-muted">Aucun résultat pour "{{ request('concessionnaire_filter') }}"</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')