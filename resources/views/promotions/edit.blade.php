@include('layouts.header')
@include('layouts.menu')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="nav-icon i-Edit"></i> Modifier la Promotion
                        </h4>
                        <a href="{{ route('promotions.index') }}" class="btn btn-secondary">
                            <i class="nav-icon i-Arrow-Left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('promotions.update', $promotion->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="libelle" class="form-label">
                                                Libellé <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('libelle') is-invalid @enderror" 
                                                   id="libelle" 
                                                   name="libelle" 
                                                   value="{{ old('libelle', $promotion->libelle) }}" 
                                                   required>
                                            @error('libelle')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mobile" class="form-label">
                                                Mobile <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('mobile') is-invalid @enderror" 
                                                   id="mobile" 
                                                   name="mobile" 
                                                   value="{{ old('mobile', $promotion->mobile) }}" 
                                                   required>
                                            @error('mobile')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_debut" class="form-label">
                                                Date de début <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control @error('date_debut') is-invalid @enderror" 
                                                   id="date_debut" 
                                                   name="date_debut" 
                                                   value="{{ old('date_debut', $promotion->date_debut->format('Y-m-d')) }}" 
                                                   required>
                                            @error('date_debut')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_fin" class="form-label">
                                                Date de fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control @error('date_fin') is-invalid @enderror" 
                                                   id="date_fin" 
                                                   name="date_fin" 
                                                   value="{{ old('date_fin', $promotion->date_fin->format('Y-m-d')) }}" 
                                                   required>
                                            @error('date_fin')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="etablissement_id" class="form-label">
                                        Établissement <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('etablissement_id') is-invalid @enderror" 
                                            id="etablissement_id" 
                                            name="etablissement_id" 
                                            required>
                                        <option value="">Sélectionner un établissement</option>
                                        @foreach($etablissements as $etablissement)
                                            <option value="{{ $etablissement->id }}" 
                                                    {{ old('etablissement_id', $promotion->etablissement_id) == $etablissement->id ? 'selected' : '' }}>
                                                {{ $etablissement->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('etablissement_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              placeholder="Description de la promotion...">{{ old('description', $promotion->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Laissez vide pour conserver l'image actuelle
                                    </div>
                                </div>

                                @if($promotion->image_url)
                                    <div class="mb-3">
                                        <label class="form-label">Image actuelle</label>
                                        <div class="text-center">
                                            <img src="{{ $promotion->image_url }}" 
                                                 alt="{{ $promotion->libelle }}" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 200px; max-height: 200px;">
                                        </div>
                                    </div>
                                @endif

                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="nav-icon i-Info"></i> Informations
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><strong>Statut actuel:</strong> 
                                                <span class="badge {{ $promotion->statut ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $promotion->statut ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </li>
                                            <li><strong>Créé le:</strong> {{ $promotion->created_at->format('d/m/Y H:i') }}</li>
                                            <li><strong>Modifié le:</strong> {{ $promotion->updated_at->format('d/m/Y H:i') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="nav-icon i-Check"></i> Mettre à jour
                                    </button>
                                    <a href="{{ route('promotions.index') }}" class="btn btn-secondary">
                                        <i class="nav-icon i-Close"></i> Annuler
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation des dates
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    
    dateDebut.addEventListener('change', function() {
        if (this.value) {
            dateFin.min = this.value;
        }
    });
    
    dateFin.addEventListener('change', function() {
        if (this.value && dateDebut.value && this.value <= dateDebut.value) {
            alert('La date de fin doit être postérieure à la date de début');
            this.value = '';
        }
    });
});
</script>
@include('layouts.footer')
