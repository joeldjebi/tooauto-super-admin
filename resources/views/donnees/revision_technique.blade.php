@include('layouts.header')
@include('layouts.menu')

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
                        <i class="nav-icon i-Settings"></i>
                        {{ $title }}
                    </h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRevisionModal" data-toggle="modal" data-target="#createRevisionModal" onclick="testModal()">
                        <i class="nav-icon i-Add"></i>
                        Ajouter une révision technique
                    </button>
                </div>
                
                <div class="card-body">
                    @if($revision_techniques && $revision_techniques->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="revisionsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Logo</th>
                                        <th>Ville</th>
                                        <th>Commune</th>
                                        <th>Adresse Map</th>
                                        <th>Adresse</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($revision_techniques as $revision)
                                        <tr>
                                            <td>{{ $revision->name }}</td>
                                            <td>
                                                @if($revision->logo)
                                                    <img src="{{ strpos($revision->logo, 'http') === 0 ? $revision->logo : '/images/revision_technique/' . $revision->logo }}" 
                                                         alt="Logo" 
                                                         class="rounded" 
                                                         width="50" 
                                                         height="50"
                                                         style="object-fit: cover;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    
                                                @else
                                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="nav-icon i-Settings text-white"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($revision->ville)
                                                    {{ $revision->ville->libelle }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($revision->commune)
                                                    {{ $revision->commune->nom }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($revision->adresse_map)
                                                    <a href="{{ $revision->adresse_map }}" target="_blank" class="text-decoration-none">
                                                        <i class="nav-icon i-Map-Marker"></i>Voir sur la carte
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($revision->adresse)
                                                    {{ $revision->adresse }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($revision->contact)
                                                    <a href="tel:{{ $revision->contact }}" class="text-decoration-none">
                                                        <i class="nav-icon i-Phone"></i>{{ $revision->contact }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($revision->email)
                                                    <a href="mailto:{{ $revision->email }}" class="text-decoration-none">
                                                        <i class="nav-icon i-Email"></i>{{ $revision->email }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $revision->statut ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $revision->statut ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="Modifier"
                                                            onclick="editRevision({{ $revision->id }})">
                                                        <i class="nav-icon i-Pen-2"></i>
                                                    </button>
                                                    
                                                    <form method="POST" action="{{ route('revision_technique.toggle', $revision->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $revision->statut ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                                title="{{ $revision->statut ? 'Désactiver' : 'Activer' }}"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir {{ $revision->statut ? 'désactiver' : 'activer' }} cette révision technique ?')">
                                                            <i class="nav-icon {{ $revision->statut ? 'i-Lock' : 'i-Unlock' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('revision_technique.destroy', $revision->id) }}" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Supprimer"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette révision technique ? Cette action est irréversible.')">
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
                            <i class="nav-icon i-Settings text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">Aucune révision technique trouvée</h5>
                            <p class="text-muted">Commencez par ajouter votre première révision technique.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRevisionModal" data-toggle="modal" data-target="#createRevisionModal" onclick="testModal()">
                                <i class="nav-icon i-Add"></i>
                                Ajouter une révision technique
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Création -->
<div class="modal fade" id="createRevisionModal" tabindex="-1" aria-labelledby="createRevisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('revision_technique.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createRevisionModalLabel">
                        <i class="nav-icon i-Add"></i>Ajouter une révision technique
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ville_id" class="form-label">Ville <span class="text-danger">*</span></label>
                                <select class="form-control @error('ville_id') is-invalid @enderror" 
                                        id="ville_id" name="ville_id" required>
                                    <option value="">Sélectionner une ville</option>
                                    @if(isset($villes))
                                        @foreach($villes as $ville)
                                            <option value="{{ $ville->id }}" {{ old('ville_id') == $ville->id ? 'selected' : '' }}>
                                                {{ $ville->libelle }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('ville_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="commune_id" class="form-label">Commune</label>
                                <select class="form-control @error('commune_id') is-invalid @enderror" 
                                        id="commune_id" name="commune_id">
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
                        <label for="adresse_map" class="form-label">Adresse Google Maps <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('adresse_map') is-invalid @enderror" 
                               id="adresse_map" name="adresse_map" value="{{ old('adresse_map') }}" required>
                        @error('adresse_map')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">URL complète de Google Maps</div>
                    </div>

                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('adresse') is-invalid @enderror" 
                               id="adresse" name="adresse" value="{{ old('adresse') }}" required>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('contact') is-invalid @enderror" 
                               id="contact" name="contact" value="{{ old('contact') }}" required maxlength="20">
                        @error('contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                               id="logo" name="logo" accept="image/*">
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Formats acceptés: JPEG, PNG, JPG, GIF, SVG (max 2MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="closeCreateModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="nav-icon i-Save"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'Édition -->
<div class="modal fade" id="editRevisionModal" tabindex="-1" aria-labelledby="editRevisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editRevisionForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editRevisionModalLabel">
                        <i class="nav-icon i-Pen-2"></i>Modifier la révision technique
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required value="{{ old('name') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_ville_id" class="form-label">Ville <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_ville_id" name="ville_id" required>
                                    <option value="">Sélectionner une ville</option>
                                    @if(isset($villes))
                                        @foreach($villes as $ville)
                                            <option value="{{ $ville->id }}">{{ $ville->libelle }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_commune_id" class="form-label">Commune</label>
                                <select class="form-control" id="edit_commune_id" name="commune_id">
                                    <option value="">Sélectionner une commune</option>
                                    @if(isset($communes))
                                        @foreach($communes as $commune)
                                            <option value="{{ $commune->id }}">{{ $commune->nom }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_adresse_map" class="form-label">Adresse Google Maps <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="edit_adresse_map" name="adresse_map" required>
                        <div class="form-text">URL complète de Google Maps</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_adresse" name="adresse" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact" class="form-label">Contact <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_contact" name="contact" required maxlength="20">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="edit_logo" name="logo" accept="image/*">
                        <div class="form-text">Formats acceptés: JPEG, PNG, JPG, GIF, SVG (max 2MB)</div>
                        <div id="current_logo" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="closeEditModal()">Fermer</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="nav-icon i-Save"></i>Modifier
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
    
    var modalElement = document.getElementById('createRevisionModal');
    if (!modalElement) {
        console.error('Modal createRevisionModal non trouvé !');
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
        $('#createRevisionModal').modal('show');
    }
    // Fallback: afficher le modal manuellement
    else {
        console.log('Fallback: affichage manuel du modal');
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        document.body.classList.add('modal-open');
    }
}

// Fonction pour éditer une révision technique
function editRevision(id) {
    // Mettre à jour l'action du formulaire
    document.getElementById('editRevisionForm').action = '/revision-technique-update/' + id;
    
    // Récupérer les données de la révision technique via AJAX
    $.ajax({
        url: '/revision-technique-edit/' + id,
        method: 'GET',
        success: function(response) {
            // Remplir le formulaire avec les données
            $('#edit_name').val(response.name);
            $('#edit_ville_id').val(response.ville_id);
            $('#edit_commune_id').val(response.commune_id);
            $('#edit_adresse_map').val(response.adresse_map);
            $('#edit_adresse').val(response.adresse);
            $('#edit_contact').val(response.contact);
            $('#edit_email').val(response.email);
            
            // Afficher le logo actuel si il existe
            if (response.logo) {
                var logoUrl = response.logo.startsWith('http') ? response.logo : '/images/revision_technique/' + response.logo;
                $('#current_logo').html('<img src="' + logoUrl + '" alt="Logo actuel" class="img-fluid" style="max-width: 100px;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"><div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; display: none;"><i class="nav-icon i-Settings text-white"></i></div>');
            } else {
                $('#current_logo').html('<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;"><i class="nav-icon i-Settings text-white"></i></div>');
            }
            
            // Ouvrir le modal
            var modalElement = document.getElementById('editRevisionModal');
            if (modalElement) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#editRevisionModal').modal('show');
                }
            }
        },
        error: function() {
            alert('Erreur lors du chargement des données de la révision technique.');
        }
    });
}

// Fonction pour fermer le modal d'édition
function closeEditModal() {
    var modalElement = document.getElementById('editRevisionModal');
    if (modalElement) {
        // Essayer Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            } else {
                modal = new bootstrap.Modal(modalElement);
                modal.hide();
            }
        }
        // Essayer jQuery Bootstrap
        else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#editRevisionModal').modal('hide');
        }
        // Fallback: fermeture manuelle
        else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            document.body.classList.remove('modal-open');
            // Supprimer le backdrop
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
}

// Fonction pour fermer le modal de création
function closeCreateModal() {
    var modalElement = document.getElementById('createRevisionModal');
    if (modalElement) {
        // Essayer Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            } else {
                modal = new bootstrap.Modal(modalElement);
                modal.hide();
            }
        }
        // Essayer jQuery Bootstrap
        else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#createRevisionModal').modal('hide');
        }
        // Fallback: fermeture manuelle
        else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            document.body.classList.remove('modal-open');
            // Supprimer le backdrop
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
}

// Initialisation de DataTable si disponible
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#revisionsTable').DataTable({
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
    
    // Réinitialiser les formulaires quand les modals se ferment
    $('#createRevisionModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.invalid-feedback').remove();
        $(this).find('.is-invalid').removeClass('is-invalid');
    });
    
    $('#editRevisionModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.invalid-feedback').remove();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $('#current_logo').empty();
    });
});
</script>

@include('layouts.footer')
