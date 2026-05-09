@include('layouts.header')
@include('layouts.menu')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="nav-icon i-Add"></i> Créer une Nouvelle Promotion
                        </h4>
                        <a href="{{ route('promotions.index') }}" class="btn btn-secondary">
                            <i class="nav-icon i-Arrow-Left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('promotions.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
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
                                                   value="{{ old('libelle') }}" 
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
                                                   value="{{ old('mobile') }}" 
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
                                                   value="{{ old('date_debut') }}" 
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
                                                   value="{{ old('date_fin') }}" 
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
                                                    {{ old('etablissement_id') == $etablissement->id ? 'selected' : '' }}>
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
                                              placeholder="Description de la promotion...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">
                                        Image <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*" 
                                           required>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Formats acceptés: JPEG, PNG, JPG, GIF (max: 2MB)
                                    </div>
                                </div>

                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="nav-icon i-Info"></i> Informations
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="nav-icon i-Check text-success"></i> La promotion sera active par défaut</li>
                                            <li><i class="nav-icon i-Check text-success"></i> L'image sera redimensionnée automatiquement</li>
                                            <li><i class="nav-icon i-Check text-success"></i> Vous pourrez modifier le statut plus tard</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="nav-icon i-Check"></i> Créer la Promotion
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
