@include('layouts.header')
@include('layouts.menu')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="nav-icon i-Eye"></i> Détails de la Promotion
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('promotions.edit', $promotion->id) }}" class="btn btn-warning">
                                <i class="nav-icon i-Edit"></i> Modifier
                            </a>
                            <a href="{{ route('promotions.index') }}" class="btn btn-secondary">
                                <i class="nav-icon i-Arrow-Left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Libellé</label>
                                        <p class="form-control-plaintext">{{ $promotion->libelle }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Mobile</label>
                                        <p class="form-control-plaintext">{{ $promotion->mobile }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date de début</label>
                                        <p class="form-control-plaintext">{{ $promotion->date_debut->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date de fin</label>
                                        <p class="form-control-plaintext">{{ $promotion->date_fin->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Établissement</label>
                                <p class="form-control-plaintext">{{ $promotion->etablissement->name ?? 'N/A' }}</p>
                            </div>

                            @if($promotion->description)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <div class="border rounded p-3 bg-light">
                                        {{ $promotion->description }}
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Statut</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge {{ $promotion->statut ? 'bg-success' : 'bg-danger' }} fs-6">
                                                {{ $promotion->statut ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Période</label>
                                        <p class="form-control-plaintext">
                                            @php
                                                $now = now();
                                                $debut = $promotion->date_debut;
                                                $fin = $promotion->date_fin;
                                                
                                                if ($now < $debut) {
                                                    $status = 'À venir';
                                                    $class = 'bg-info';
                                                } elseif ($now > $fin) {
                                                    $status = 'Expirée';
                                                    $class = 'bg-danger';
                                                } else {
                                                    $status = 'En cours';
                                                    $class = 'bg-success';
                                                }
                                            @endphp
                                            <span class="badge {{ $class }} fs-6">{{ $status }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            @if($promotion->image)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Image</label>
                                    <div class="text-center">
                                        <img src="{{ asset('storage/promotions/' . $promotion->image) }}" 
                                             alt="{{ $promotion->libelle }}" 
                                             class="img-fluid rounded shadow" 
                                             style="max-width: 100%; max-height: 300px;">
                                    </div>
                                </div>
                            @endif

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="nav-icon i-Info"></i> Informations système
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>ID:</strong> {{ $promotion->id }}</li>
                                        <li><strong>Créé le:</strong> {{ $promotion->created_at->format('d/m/Y H:i') }}</li>
                                        <li><strong>Modifié le:</strong> {{ $promotion->updated_at->format('d/m/Y H:i') }}</li>
                                        @if($promotion->createdBy)
                                            <li><strong>Créé par:</strong> {{ $promotion->createdBy->name }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="nav-icon i-Settings"></i> Actions
                                    </h6>
                                    <div class="d-grid gap-2">
                                        <form action="{{ route('promotions.toggle-status', $promotion->id) }}" 
                                              method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn {{ $promotion->statut ? 'btn-warning' : 'btn-success' }} w-100"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir {{ $promotion->statut ? 'désactiver' : 'activer' }} cette promotion ?')">
                                                <i class="nav-icon {{ $promotion->statut ? 'i-Close' : 'i-Check' }}"></i>
                                                {{ $promotion->statut ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('promotions.destroy', $promotion->id) }}" 
                                              method="POST"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette promotion ? Cette action est irréversible.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="nav-icon i-Delete"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.footer')
