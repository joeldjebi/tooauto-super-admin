@include('layouts.header')
@include('layouts.menu')
<style>
    .promotion-page {
        background: #f6f8fb;
        min-height: calc(100vh - 80px);
        padding-bottom: 32px;
    }

    .promotion-page .page-title {
        color: #1f2937;
        font-size: 1.35rem;
        font-weight: 700;
    }

    .promotion-panel {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .promotion-section-title {
        color: #374151;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .image-preview-box {
        align-items: center;
        background: #eef2f7;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        min-height: 220px;
        overflow: hidden;
    }

    .image-preview-box img {
        display: none;
        height: 100%;
        max-height: 260px;
        object-fit: cover;
        width: 100%;
    }

    .image-preview-box.has-image img {
        display: block;
    }

    .image-preview-box.has-image .empty-preview {
        display: none;
    }

    .empty-preview {
        color: #64748b;
        text-align: center;
    }

    .etablissement-result {
        color: #64748b;
        font-size: .82rem;
        margin-top: 6px;
    }
</style>
<div class="container-fluid promotion-page">
    <div class="row">
        <div class="col-12">
            <div class="card promotion-panel">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title mb-0">
                            <i class="nav-icon i-Add"></i> Créer une Nouvelle Promotion
                        </h4>
                        <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">
                            <i class="nav-icon i-Arrow-Left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('promotions.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="promotion-section-title mb-3">Informations de la promotion</div>
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
                                                   placeholder="Ex: Réduction vidange"
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
                                                   placeholder="Ex: 0700000000"
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
                                    <label for="etablissement_search" class="form-label">
                                        Rechercher un établissement
                                    </label>
                                    <input type="search"
                                           class="form-control"
                                           id="etablissement_search"
                                           placeholder="Tapez le nom d'un établissement...">
                                    <div id="etablissement_result" class="etablissement-result">
                                        {{ $etablissements->count() }} établissement(s) disponible(s)
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
                                                    data-search="{{ Str::lower($etablissement->name) }}"
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

                            <div class="col-lg-4">
                                <div class="promotion-section-title mb-3">Image et statut</div>
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
                                        Formats acceptés : JPEG, PNG, JPG, GIF (max : 2 Mo). L'image sera stockée sur Wasabi.
                                    </div>
                                </div>

                                <div class="image-preview-box mb-3" id="image_preview_box">
                                    <img id="image_preview" src="" alt="Aperçu de l'image">
                                    <div class="empty-preview">
                                        <i class="nav-icon i-Image" style="font-size: 2rem;"></i>
                                        <div class="mt-2">Aperçu de l'image</div>
                                    </div>
                                </div>

                                <div class="border rounded p-3 bg-light">
                                    <h6 class="mb-2">
                                        <i class="nav-icon i-Info"></i> Informations
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="nav-icon i-Check text-success"></i> La promotion sera active par défaut</li>
                                        <li><i class="nav-icon i-Check text-success"></i> Vous pourrez modifier le statut plus tard</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">
                                        <i class="nav-icon i-Close"></i> Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="nav-icon i-Check"></i> Créer la Promotion
                                    </button>
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
    const etablissementSearch = document.getElementById('etablissement_search');
    const etablissementSelect = document.getElementById('etablissement_id');
    const etablissementResult = document.getElementById('etablissement_result');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image_preview');
    const imagePreviewBox = document.getElementById('image_preview_box');

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

    etablissementSearch.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        let visibleCount = 0;

        Array.from(etablissementSelect.options).forEach(function(option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const isVisible = option.dataset.search.includes(query);
            option.hidden = !isVisible;

            if (isVisible) {
                visibleCount++;
            }
        });

        if (etablissementSelect.selectedOptions[0] && etablissementSelect.selectedOptions[0].hidden) {
            etablissementSelect.value = '';
        }

        etablissementResult.textContent = visibleCount + ' établissement(s) trouvé(s)';
    });

    imageInput.addEventListener('change', function() {
        const file = this.files && this.files[0];

        if (!file) {
            imagePreview.src = '';
            imagePreviewBox.classList.remove('has-image');
            return;
        }

        imagePreview.src = URL.createObjectURL(file);
        imagePreviewBox.classList.add('has-image');
    });
});
</script>
@include('layouts.footer')
