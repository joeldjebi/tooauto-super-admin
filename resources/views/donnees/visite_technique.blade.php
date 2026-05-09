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
                        <i class="nav-icon i-Car"></i>
                        {{ $title }}
                    </h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVisiteModal" data-toggle="modal" data-target="#createVisiteModal" onclick="testModal()">
                        <i class="nav-icon i-Add"></i>
                        Ajouter une visite technique
                    </button>
                </div>

                <div class="card-body">
                    @if($visite_techniques && $visite_techniques->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="visitesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Logo</th>
                                        <th>Ville</th>
                                        <th>Commune</th>
                                        <th>Adresse</th>
                                        <th>Contacts</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visite_techniques as $visite)
                                        <tr>
                                            <td>{{ $visite->name }}</td>
                                            <td>
                                                @if($visite->logo)
                                                    <img src="{{ $visite->logo }}"
                                                         alt="Logo"
                                                         class="rounded"
                                                         width="50"
                                                         height="50"
                                                         style="object-fit: cover;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px; display: none;">
                                                        <i class="nav-icon i-Car text-white"></i>
                                                    </div>
                                                @else
                                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px;">
                                                        <i class="nav-icon i-Car text-white"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($visite->ville)
                                                    {{ $visite->ville->libelle }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($visite->commune)
                                                    {{ $visite->commune->nom }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($visite->adresse)
                                                    <span title="{{ $visite->adresse }}">
                                                        {{ Str::limit($visite->adresse, 40) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($visite->contacts)
                                                    <span title="{{ $visite->contacts }}">
                                                        {{ Str::limit($visite->contacts, 20) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($visite->email)
                                                    <a href="mailto:{{ $visite->email }}" class="text-decoration-none">
                                                        <i class="nav-icon i-Email"></i>{{ $visite->email }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $visite->statut ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $visite->statut ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="Modifier"
                                                            onclick="editVisite({{ $visite->id }})">
                                                        <i class="nav-icon i-Pen-2"></i>
                                                    </button>

                                                    <form method="POST" action="{{ route('visite_technique.toggle', $visite->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm {{ $visite->statut ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                                title="{{ $visite->statut ? 'Désactiver' : 'Activer' }}"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir {{ $visite->statut ? 'désactiver' : 'activer' }} cette visite technique ?')">
                                                            <i class="nav-icon {{ $visite->statut ? 'i-Lock' : 'i-Unlock' }}"></i>
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('visite_technique.destroy', $visite->id) }}" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Supprimer"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette visite technique ? Cette action est irréversible.')">
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
                            <i class="nav-icon i-Car text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">Aucune visite technique trouvée</h5>
                            <p class="text-muted">Commencez par ajouter votre première visite technique.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVisiteModal" data-toggle="modal" data-target="#createVisiteModal" onclick="testModal()">
                                <i class="nav-icon i-Add"></i>
                                Ajouter une visite technique
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Création -->
<div class="modal fade" id="createVisiteModal" tabindex="-1" aria-labelledby="createVisiteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('visite_technique.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createVisiteModalLabel">
                        <i class="nav-icon i-Add"></i>Ajouter une visite technique
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
                                <select required class="form-control @error('commune_id') is-invalid @enderror"
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
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea required class="form-control @error('adresse') is-invalid @enderror"
                                  id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="contacts" class="form-label">Contacts</label>
                        <input required type="text" class="form-control @error('contacts') is-invalid @enderror"
                               id="contacts" name="contacts" value="{{ old('contacts') }}">
                        @error('contacts')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input required type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="adresse_map" class="form-label">Adresse Google Maps</label>
                        <input required type="url" class="form-control @error('adresse_map') is-invalid @enderror"
                               id="adresse_map" name="adresse_map" value="{{ old('adresse_map') }}">
                        @error('adresse_map')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">URL complète de Google Maps</div>
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input required type="file" class="form-control @error('logo') is-invalid @enderror"
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
<div class="modal fade" id="editVisiteModal" tabindex="-1" aria-labelledby="editVisiteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editVisiteForm" action="#" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editVisiteModalLabel">
                        <i class="nav-icon i-Pen-2"></i>Modifier la visite technique
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
                        <label for="edit_adresse" class="form-label">Adresse</label>
                        <textarea required class="form-control" id="edit_adresse" name="adresse" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_contacts" class="form-label">Contacts</label>
                        <input required type="text" class="form-control" id="edit_contacts" name="contacts">
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input required type="email" class="form-control" id="edit_email" name="email">
                    </div>

                    <div class="mb-3">
                        <label for="edit_adresse_map" class="form-label">Adresse Google Maps</label>
                        <input required type="url" class="form-control" id="edit_adresse_map" name="adresse_map">
                        <div class="form-text">URL complète de Google Maps</div>
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

    var modalElement = document.getElementById('createVisiteModal');
    if (!modalElement) {
        console.error('Modal createVisiteModal non trouvé !');
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
        $('#createVisiteModal').modal('show');
    }
    // Fallback: afficher le modal manuellement
    else {
        console.log('Fallback: affichage manuel du modal');
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        document.body.classList.add('modal-open');
    }
}

// Fonction pour éditer une visite technique
function editVisite(id) {
    // Mettre à jour l'action du formulaire
    document.getElementById('editVisiteForm').action = '/visite-technique-update/' + id;

    // Récupérer les données de la visite technique via AJAX
    $.ajax({
        url: '/visite-technique-edit/' + id,
        method: 'GET',
        success: function(response) {
            // Remplir le formulaire avec les données
            $('#edit_name').val(response.name);
            $('#edit_ville_id').val(response.ville_id);
            $('#edit_commune_id').val(response.commune_id);
            $('#edit_adresse').val(response.adresse);
            $('#edit_contacts').val(response.contacts);
            $('#edit_email').val(response.email);
            $('#edit_adresse_map').val(response.adresse_map);

            // Afficher le logo actuel si il existe
            if (response.logo) {
                $('#current_logo').html('<img src="' + response.logo + '" alt="Logo actuel" class="img-fluid" style="max-width: 100px;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"><div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; display: none;"><i class="nav-icon i-Car text-white"></i></div>');
            } else {
                $('#current_logo').html('<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;"><i class="nav-icon i-Car text-white"></i></div>');
            }

            // Ouvrir le modal
            var modalElement = document.getElementById('editVisiteModal');
            if (modalElement) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#editVisiteModal').modal('show');
                }
            }
        },
        error: function() {
            alert('Erreur lors du chargement des données de la visite technique.');
        }
    });
}

// Fonction pour fermer le modal d'édition
function closeEditModal() {
    var modalElement = document.getElementById('editVisiteModal');
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
            $('#editVisiteModal').modal('hide');
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
    var modalElement = document.getElementById('createVisiteModal');
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
            $('#createVisiteModal').modal('hide');
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
        $('#visitesTable').DataTable({
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
    $('#createVisiteModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.invalid-feedback').remove();
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    $('#editVisiteModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.invalid-feedback').remove();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $('#current_logo').empty();
    });
});
</script>

@include('layouts.footer')
