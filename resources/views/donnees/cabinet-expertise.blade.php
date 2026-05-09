@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<!-- Messages Flash -->
@if(session('type') && session('message'))
    <div class="alert {{ session('type') }} alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="nav-icon i-User"></i>
                        {{ $title }}
                    </h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCabinetModal" data-toggle="modal" data-target="#createCabinetModal" onclick="testModal()">
                        <i class="nav-icon i-Add"></i>
                        Ajouter un cabinet
                    </button>
                </div>
                
                <div class="card-body">
                    @if($cabinets && $cabinets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="cabinetsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Photo</th>
                                        <th>Nom cabinet</th>
                                        <th>Nom complet</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>Commune</th>
                                        <th>Adresse</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cabinets as $cabinet)
                                        <tr>
                                            <td>
                                                @if($cabinet->photo)
                                                    <img src="{{ asset('images/cabinet_expertise/' . $cabinet->photo) }}" 
                                                         alt="Photo" 
                                                         class="rounded-circle" 
                                                         width="40" 
                                                         height="40"
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="nav-icon i-User text-white"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $cabinet->name }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $cabinet->nom }} {{ $cabinet->prenoms }}</strong>
                                                @if($cabinet->mobile_secondaire)
                                                    <br><small class="text-muted">Sec: {{ $cabinet->mobile_secondaire }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="tel:{{ $cabinet->mobile }}" class="text-decoration-none">
                                                    <i class="nav-icon i-Phone"></i>{{ $cabinet->mobile }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($cabinet->email)
                                                    <a href="mailto:{{ $cabinet->email }}" class="text-decoration-none">
                                                        <i class="nav-icon i-Email"></i>{{ $cabinet->email }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cabinet->commune)
                                                    {{ $cabinet->commune->nom }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cabinet->adresse)
                                                    <span title="{{ $cabinet->adresse }}">
                                                        {{ Str::limit($cabinet->adresse, 30) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $cabinet->statut ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $cabinet->statut ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('edit-cabinet-expertise', $cabinet->id) }}" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Modifier">
                                                        <i class="nav-icon i-Pen-2"></i>
                                                    </a>
                                                    
                                                    <form method="POST" action="{{ route('toggle-status-cabinet-expertise', $cabinet->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $cabinet->statut ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                                title="{{ $cabinet->statut ? 'Désactiver' : 'Activer' }}"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir {{ $cabinet->statut ? 'désactiver' : 'activer' }} ce cabinet ?')">
                                                            <i class="nav-icon {{ $cabinet->statut ? 'i-Lock' : 'i-Unlock' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('delete-cabinet-expertise', $cabinet->id) }}" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Supprimer"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cabinet ? Cette action est irréversible.')">
                                                            <i class="nav-icon i-Close-Window"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="nav-icon i-User text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">Aucun cabinet d'expertise trouvé</h5>
                            <p class="text-muted">Commencez par ajouter votre premier cabinet d'expertise.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCabinetModal" data-toggle="modal" data-target="#createCabinetModal" onclick="testModal()">
                                <i class="nav-icon i-Add"></i>
                                Ajouter un cabinet
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Création -->
<div class="modal fade" id="createCabinetModal" tabindex="-1" aria-labelledby="createCabinetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('cabinet-expertise.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createCabinetModalLabel">
                        <i class="nav-icon i-Add"></i>Ajouter un cabinet d'expertise
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom cabinet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" name="nom" value="{{ old('nom') }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenoms" class="form-label">Prénoms <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenoms') is-invalid @enderror" 
                                       id="prenoms" name="prenoms" value="{{ old('prenoms') }}" required>
                                @error('prenoms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('mobile') is-invalid @enderror" 
                                       id="mobile" name="mobile" value="{{ old('mobile') }}" required>
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mobile_secondaire" class="form-label">Mobile secondaire</label>
                                <input type="tel" class="form-control @error('mobile_secondaire') is-invalid @enderror" 
                                       id="mobile_secondaire" name="mobile_secondaire" value="{{ old('mobile_secondaire') }}">
                                @error('mobile_secondaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="commune_id" class="form-label">Commune <span class="text-danger">*</span></label>
                                <select class="form-control @error('commune_id') is-invalid @enderror" 
                                        id="commune_id" name="commune_id" required>
                                    <option value="">Sélectionner une commune</option>
                                    @if(isset($communes))
                                        @foreach($communes as $commune)
                                            <option value="{{ $commune->id }}" {{ old('commune_id') == $commune->id ? 'selected' : '' }}>
                                                {{ $commune->nom }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('commune_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                  id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="text" class="form-control @error('longitude') is-invalid @enderror" 
                                       id="longitude" name="longitude" value="{{ old('longitude') }}">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control @error('latitude') is-invalid @enderror" 
                                       id="latitude" name="latitude" value="{{ old('latitude') }}">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                               id="photo" name="photo" accept="image/*">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Formats acceptés: JPEG, PNG, JPG, GIF (max 10MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="nav-icon i-Save"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fonction de test pour le modal
function testModal() {
    console.log('Bouton cliqué !');
    
    var modalElement = document.getElementById('createCabinetModal');
    if (!modalElement) {
        console.error('Modal createCabinetModal non trouvé !');
        alert('Erreur: Modal non trouvé !');
        return;
    }
    
    console.log('Modal trouvé, tentative d\'ouverture...');
    
    // Essayer d'ouvrir le modal avec Bootstrap 5
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        console.log('Bootstrap 5 détecté');
        var modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
    // Si Bootstrap 5 ne fonctionne pas, essayer avec jQuery
    else if (typeof $ !== 'undefined' && $.fn.modal) {
        console.log('jQuery Bootstrap détecté');
        $('#createCabinetModal').modal('show');
    }
    // Fallback: afficher le modal manuellement
    else {
        console.log('Fallback: affichage manuel du modal');
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        document.body.classList.add('modal-open');
    }
}

// Fonction pour charger les données d'édition (plus nécessaire - redirection directe via lien)

// Initialisation de DataTable si disponible
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#cabinetsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            },
            "pageLength": 25,
            "order": [[1, "asc"]]
        });
    }
    
    // Gestion des messages flash
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

@include('layouts.footer')
