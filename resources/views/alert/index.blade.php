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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="nav-icon i-Alert"></i> Les alertes ({{ $alertes->total() }})
                    </h4>
                    @if($alertes->isNotEmpty())
                        <form method="POST" action="{{ route('delete-all-alerts') }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer TOUTES les alertes ? Cette action est irréversible et supprimera {{ $alertes->total() }} alerte(s).')">
                                <i class="nav-icon i-Close-Window"></i>
                                Supprimer toutes les alertes ({{ $alertes->total() }})
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Filtres optimisés -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="nav-icon i-Filter-2"></i> Filtres de recherche
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('index-alerte') }}" class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label small text-muted">Utilisateur</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="nav-icon i-User"></i>
                                    </span>
                                    <input type="text" 
                                           name="user_filter" 
                                           value="{{ request('user_filter') }}" 
                                           placeholder="Rechercher par utilisateur..." 
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label small text-muted">Véhicule</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="nav-icon i-Car"></i>
                                    </span>
                                    <input type="text" 
                                           name="vehicule_filter" 
                                           value="{{ request('vehicule_filter') }}" 
                                           placeholder="Rechercher par véhicule..." 
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label class="form-label small text-muted">Type d'alerte</label>
                                <select name="type_alert_filter" class="form-select form-select-sm">
                                    <option value="">Tous les types</option>
                                    @foreach($types_alerts as $type)
                                        <option value="{{ $type->libelle }}" {{ request('type_alert_filter') == $type->libelle ? 'selected' : '' }}>
                                            {{ $type->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label class="form-label small text-muted">Date début</label>
                                <input type="date" 
                                       name="date_debut_filter" 
                                       value="{{ request('date_debut_filter') }}" 
                                       class="form-control form-control-sm">
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label class="form-label small text-muted">Date fin</label>
                                <input type="date" 
                                       name="date_fin_filter" 
                                       value="{{ request('date_fin_filter') }}" 
                                       class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="nav-icon i-Magnifying-Glass me-1"></i>
                                        Rechercher
                                    </button>
                                    <a href="{{ route('index-alerte') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="nav-icon i-Close-Window me-1"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sélecteur de pagination -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('index-alerte') }}" class="d-flex align-items-center">
                            <input type="hidden" name="user_filter" value="{{ request('user_filter') }}">
                            <input type="hidden" name="vehicule_filter" value="{{ request('vehicule_filter') }}">
                            <input type="hidden" name="type_alert_filter" value="{{ request('type_alert_filter') }}">
                            <input type="hidden" name="date_debut_filter" value="{{ request('date_debut_filter') }}">
                            <input type="hidden" name="date_fin_filter" value="{{ request('date_fin_filter') }}">
                            <label class="form-label me-2 mb-0">Éléments par page:</label>
                            <select name="per_page" onchange="this.form.submit()" class="form-control form-control-sm" style="width: auto;">
                                <option value="5" {{ request('per_page', 15) == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                            </select>
                        </form>
                    </div>
                </div>
                @if($alertes->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Véhicule</th>
                                    <th scope="col">Type d'alerte</th>
                                    <th scope="col">Date de début</th>
                                    <th scope="col">Date de fin</th>
                                    <th scope="col">Kilométrage</th>
                                    <th scope="col">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alertes as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>
                                            @if($item->user)
                                                <strong>{{ $item->user->nom ?? 'N/A' }} {{ $item->user->prenoms ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $item->user->email ?? 'N/A' }}</small>
                                            @else
                                                <span class="text-muted">Utilisateur supprimé</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->vehicule)
                                                <strong>{{ $item->vehicule->matricule ?? 'N/A' }}</strong>
                                                @if($item->vehicule->modele)
                                                    <br><small class="text-muted">{{ $item->vehicule->modele }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Véhicule supprimé</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->type_alert)
                                                <span class="badge badge-info">{{ $item->type_alert->libelle }}</span>
                                            @else
                                                <span class="text-muted">Type inconnu</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($item->date_debut)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->date_fin)->format('d/m/Y') }}</td>
                                        <td>{{ $item->kilometrage ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $now = now();
                                                $dateFin = \Carbon\Carbon::parse($item->date_fin);
                                                $dateDebut = \Carbon\Carbon::parse($item->date_debut);
                                            @endphp
                                            @if($now->gt($dateFin))
                                                <span class="badge badge-danger">Expirée</span>
                                            @elseif($now->between($dateDebut, $dateFin))
                                                <span class="badge badge-warning">Active</span>
                                            @else
                                                <span class="badge badge-success">À venir</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination optimisée -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $alertes->firstItem() ?? 0 }} à {{ $alertes->lastItem() ?? 0 }} 
                            sur {{ $alertes->total() }} résultats
                        </div>
                        <div>
                            {{ $alertes->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="nav-icon i-Alert" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">Aucune alerte trouvée</h5>
                        <p class="text-muted">
                            @if(request()->hasAny(['user_filter', 'vehicule_filter', 'type_alert_filter', 'date_debut_filter', 'date_fin_filter']))
                                Aucun résultat pour les filtres appliqués.
                    @else
                                Aucune alerte n'a été enregistrée pour le moment.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>





@include('layouts.footer')