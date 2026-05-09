@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="container-fluid">
    <!-- Statistiques des incidents -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $incidents->count() }}</h4>
                            <p class="card-text">Total des incidents</p>
                        </div>
                        <div class="align-self-center">
                            <i class="nav-icon i-Alert-Circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $incidents->where('created_at', '>=', \Carbon\Carbon::today())->count() }}</h4>
                            <p class="card-text">Incidents aujourd'hui</p>
                        </div>
                        <div class="align-self-center">
                            <i class="nav-icon i-Calendar" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $incidents->where('created_at', '>=', \Carbon\Carbon::now()->startOfWeek())->count() }}</h4>
                            <p class="card-text">Cette semaine</p>
                        </div>
                        <div class="align-self-center">
                            <i class="nav-icon i-Clock" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des incidents -->
<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="nav-icon i-Alert-Circle"></i>
                        Liste des incidents
                    </h3>
                </div>
                <div class="card-body">
                    @if($incidents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Sapeur-Pompier</th>
                                        <th>Localisation</th>
                                        <th>Photos</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incidents as $incident)
                                        <tr>
                                            <td>{{ $incident->id }}</td>
                                            <td>
                                                <strong>{{ $incident->user->nom ?? 'N/A' }} {{ $incident->user->prenoms ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $incident->user->email ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $incident->sapeur_pompier->name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $incident->sapeur_pompier->email ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <i class="nav-icon i-Map-Marker"></i>
                                                <small>{{ $incident->latitude }}, {{ $incident->longitude }}</small>
                                            </td>
                                            <td>
                                                @if($incident->getPhotosCount() > 0)
                                                    <span class="badge badge-info">{{ $incident->getPhotosCount() }} photo(s)</span>
                                                @else
                                                    <span class="badge badge-secondary">Aucune photo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($incident->created_at)->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="showIncidentDetails({{ $incident->id }})">
                                                    <i class="nav-icon i-Eye"></i>
                                                    Voir détails
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="nav-icon i-Info-Circle" style="font-size: 2rem;"></i>
                            <h4>Aucun incident trouvé</h4>
                            <p>Il n'y a actuellement aucun incident enregistré pour ce sapeur-pompier.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher les détails de l'incident -->
<div class="modal fade" id="incidentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="incidentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="incidentDetailsModalLabel">
                    <i class="nav-icon i-Alert-Circle"></i>
                    Détails de l'incident
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="incidentDetailsContent">
                <!-- Le contenu sera chargé via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
function showIncidentDetails(incidentId) {
    // Afficher un loader
    document.getElementById('incidentDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des détails...</p>
        </div>
    `;
    
    // Afficher le modal
    $('#incidentDetailsModal').modal('show');
    
    // Charger les détails via AJAX
    fetch(`/incident-details/${incidentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('incidentDetailsContent').innerHTML = data.html;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('incidentDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="nav-icon i-Alert-Circle"></i>
                    Erreur lors du chargement des détails de l'incident.
                </div>
            `;
        });
}
</script>

@include('layouts.footer')
