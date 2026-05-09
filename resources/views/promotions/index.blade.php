@include('layouts.header')
@include('layouts.menu')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="nav-icon i-Megaphone-2"></i> Gestion des Promotions
                        </h4>
                        <a href="{{ route('promotions.create') }}" class="btn btn-primary">
                            <i class="nav-icon i-Add"></i> Nouvelle Promotion
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtres optimisés -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="nav-icon i-Filter-2"></i> Filtres de recherche
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('promotions.index') }}" class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label small text-muted">Établissement</label>
                                    <select name="etablissement_filter" class="form-select form-select-sm">
                                        <option value="">Tous les établissements</option>
                                        @foreach($etablissements as $etablissement)
                                            <option value="{{ $etablissement->id }}" {{ request('etablissement_filter') == $etablissement->id ? 'selected' : '' }}>
                                                {{ $etablissement->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label small text-muted">Statut</label>
                                    <select name="statut_filter" class="form-select form-select-sm">
                                        <option value="">Tous les statuts</option>
                                        <option value="1" {{ request('statut_filter') == '1' ? 'selected' : '' }}>Actif</option>
                                        <option value="0" {{ request('statut_filter') == '0' ? 'selected' : '' }}>Inactif</option>
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
                                        <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="nav-icon i-Close-Window me-1"></i>
                                            Réinitialiser
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table des promotions -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Libellé</th>
                                    <th>Établissement</th>
                                    <th>Mobile</th>
                                    <th>Période</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promotions as $promotion)
                                    <tr>
                                        <td>{{ $promotion->id }}</td>
                                        <td>
                                            @if($promotion->image)
                                                <img src="{{ asset('storage/promotions/' . $promotion->image) }}" 
                                                     alt="{{ $promotion->libelle }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="nav-icon i-Image"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $promotion->libelle }}</strong>
                                            @if($promotion->description)
                                                <br><small class="text-muted">{{ Str::limit($promotion->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $promotion->etablissement->name ?? 'N/A' }}</td>
                                        <td>{{ $promotion->mobile }}</td>
                                        <td>
                                            <small>
                                                <strong>Début:</strong> {{ $promotion->date_debut->format('d/m/Y') }}<br>
                                                <strong>Fin:</strong> {{ $promotion->date_fin->format('d/m/Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $promotion->statut ? 'bg-success' : 'bg-danger' }}">
                                                {{ $promotion->statut ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('promotions.show', $promotion->id) }}" 
                                                   class="btn btn-info btn-sm" title="Voir">
                                                    <i class="nav-icon i-Eye"></i>
                                                </a>
                                                <a href="{{ route('promotions.edit', $promotion->id) }}" 
                                                   class="btn btn-warning btn-sm" title="Modifier">
                                                    <i class="nav-icon i-Edit"></i>
                                                </a>
                                                <form action="{{ route('promotions.toggle-status', $promotion->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn {{ $promotion->statut ? 'btn-secondary' : 'btn-success' }} btn-sm"
                                                            title="{{ $promotion->statut ? 'Désactiver' : 'Activer' }}"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir {{ $promotion->statut ? 'désactiver' : 'activer' }} cette promotion ?')">
                                                        <i class="nav-icon {{ $promotion->statut ? 'i-Close' : 'i-Check' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('promotions.destroy', $promotion->id) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                                        <i class="nav-icon i-Delete"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="nav-icon i-Megaphone-2" style="font-size: 3rem;"></i>
                                                <p class="mt-2">Aucune promotion trouvée</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($promotions->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $promotions->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.footer')
