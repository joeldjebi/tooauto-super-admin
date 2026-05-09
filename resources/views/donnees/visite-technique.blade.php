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
                        <i class="nav-icon i-Car"></i>
                        {{ $title }}
                    </h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVisiteModal" data-toggle="modal" data-target="#createVisiteModal" onclick="testModal()">
                        <i class="nav-icon i-Add"></i>
                        Ajouter une visite technique
                    </button>
                </div>
                
                <div class="card-body">
                    @if($visites && $visites->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="visitesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Logo</th>
                                        <th>Commune</th>
                                        <th>Adresse</th>
                                        <th>Contacts</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visites as $visite)
                                        <tr>
                                            <td>
                                                @if($visite->logo)
                                                    <img src="{{ asset('images/visite_technique/' . $visite->logo) }}" 
                                                         alt="Logo" 
                                                         class="rounded" 
                                                         width="50" 
                                                         height="50"
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="nav-icon i-Car text-white"></i>
                                                    </div>
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
                                                    <a href="{{ route('edit-visite-technique', $visite->id) }}" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Modifier">
                                                        <i class="nav-icon i-Pen-2"></i>
                                                    </a>
                                                    
                                                    <form method="POST" action="{{ route('toggle-status-visite-technique', $visite->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $visite->statut ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                                title="{{ $visite->statut ? 'Désactiver' : 'Activer' }}"
                                                                onclick="return confirm('Êtes-vous sûr de vouloir {{ $visite->statut ? 'désactiver' : 'activer' }} cette visite technique ?')">
                                                            <i class="nav-icon {{ $visite->statut ? 'i-Lock' : 'i-Unlock' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('delete-visite-technique', $visite->id) }}" style="display: inline;">
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
            <form action="{{ route('visite-technique.store') }}" method="POST" enctype="multipart/form-data">
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
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                  id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="contacts" class="form-label">Contacts</label>
                        <input type="text" class="form-control @error('contacts') is-invalid @enderror" 
                               id="contacts" name="contacts" value="{{ old('contacts') }}">
                        @error('contacts')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse_map" class="form-label">Adresse Google Maps</label>
                        <input type="url" class="form-control @error('adresse_map') is-invalid @enderror" 
                               id="adresse_map" name="adresse_map" value="{{ old('adresse_map') }}">
                        @error('adresse_map')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">URL complète de Google Maps</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                               id="logo" name="logo" accept="image/*">
                        @error('logo')
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
});
</script>

@include('layouts.footer')
