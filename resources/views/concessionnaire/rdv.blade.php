@include('layouts.header')
@include('layouts.menu')

{{-- @include('layouts.fileariane') --}}

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- Informations du concessionnaire -->
        <div class="card text-left mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        @if($concessionnaire->logo)
                            <img src="https://concessionnaire.tooauto.com/concessionnaire/logo/{{ $concessionnaire->logo }}" 
                                 alt="Logo" 
                                 class="img-fluid rounded" 
                                 style="max-height: 100px;">
                        @else
                            <div style="width: 100px; height: 100px; background-color: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                <i class="nav-icon i-Image text-muted" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h4 class="card-title mb-2">{{ $concessionnaire->name }}</h4>
                        <p class="text-muted mb-1">
                            <i class="nav-icon i-Map-Marker font-weight-bold"></i> 
                            {{ $concessionnaire->adresse ?? "N/A" }}
                        </p>
                        <p class="text-muted mb-1">
                            <i class="nav-icon i-Building font-weight-bold"></i> 
                            {{ $concessionnaire->ville->libelle ?? "N/A" }}, {{ $concessionnaire->pays->libelle ?? "N/A" }}
                        </p>
                        @if($concessionnaire->contact)
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Phone font-weight-bold"></i> 
                                <a href="tel:{{ $concessionnaire->contact }}" class="text-primary">{{ $concessionnaire->contact }}</a>
                            </p>
                        @endif
                        @if($concessionnaire->email)
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Email font-weight-bold"></i> 
                                <a href="mailto:{{ $concessionnaire->email }}" class="text-primary">{{ $concessionnaire->email }}</a>
                            </p>
                        @endif
                        @if($concessionnaire->userConcessionnaire)
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="nav-icon i-User font-weight-bold"></i> Propriétaire
                            </h6>
                            <p class="text-muted mb-1">
                                <strong>{{ $concessionnaire->userConcessionnaire->nom_complet }}</strong>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Email font-weight-bold"></i> 
                                <a href="mailto:{{ $concessionnaire->userConcessionnaire->email }}" class="text-primary">{{ $concessionnaire->userConcessionnaire->email }}</a>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="nav-icon i-Phone font-weight-bold"></i> 
                                <a href="tel:{{ $concessionnaire->userConcessionnaire->telephone_complet }}" class="text-primary">{{ $concessionnaire->userConcessionnaire->telephone_complet }}</a>
                            </p>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="{{ route('concessionnaires.index') }}" class="btn btn-secondary">
                            <i class="nav-icon i-Arrow-Left font-weight-bold"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des RDV -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary">{{ $stats['total'] }}</h5>
                        <p class="card-text">Total RDV</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-warning">{{ $stats['en_attente'] }}</h5>
                        <p class="card-text">En attente</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-success">{{ $stats['acceptes'] }}</h5>
                        <p class="card-text">Acceptés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-danger">{{ $stats['annules'] }}</h5>
                        <p class="card-text">Annulés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">{{ $stats['indisponibles'] }}</h5>
                        <p class="card-text">Indisponibles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des RDV -->
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="nav-icon i-Calendar font-weight-bold"></i> Rendez-vous du concessionnaire
                    <span class="badge badge-warning ml-2">{{ $rdvs->count() }}</span>
                </h4>
                
                @if($rdvs->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="rdv_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Heure</th>
                                    <th scope="col">Client</th>
                                    <th scope="col">Gestionnaire</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Réponse</th>
                                    <th scope="col">Date de création</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rdvs as $key => $rdv)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $rdv->jour }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <i class="nav-icon i-Clock font-weight-bold"></i> {{ $rdv->heure }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($rdv->user)
                                                <div>
                                                    <strong>{{ $rdv->user->name ?? $rdv->user->nom ?? "N/A" }}</strong>
                                                    @if($rdv->user->email)
                                                        <br><small class="text-muted">
                                                            <i class="nav-icon i-Email font-weight-bold"></i> {{ $rdv->user->email }}
                                                        </small>
                                                    @endif
                                                    @if($rdv->user->mobile)
                                                        <br><small class="text-muted">
                                                            <i class="nav-icon i-Phone font-weight-bold"></i> {{ $rdv->user->mobile }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Utilisateur supprimé</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rdv->gestionnaireDeFlotte)
                                                <div>
                                                    <strong>{{ $rdv->gestionnaireDeFlotte->name ?? $rdv->gestionnaireDeFlotte->nom ?? "N/A" }}</strong>
                                                    @if($rdv->gestionnaireDeFlotte->email)
                                                        <br><small class="text-muted">
                                                            <i class="nav-icon i-Email font-weight-bold"></i> {{ $rdv->gestionnaireDeFlotte->email }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $rdv->statut_formate['class'] }}">
                                                {{ $rdv->statut_formate['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($rdv->reponse_concessionnaire)
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-toggle="modal" 
                                                        data-target="#reponseModal{{ $rdv->id }}"
                                                        title="Voir la réponse">
                                                    <i class="nav-icon i-Eye font-weight-bold"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="nav-icon i-Calendar font-weight-bold"></i> 
                                                {{ $rdv->created_at ? $rdv->created_at->format('d/m/Y H:i') : "N/A" }}
                                            </small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-toggle="modal" 
                                                    data-target="#viewModal{{ $rdv->id }}"
                                                    title="Voir les détails">
                                                <i class="nav-icon i-Eye font-weight-bold"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal pour voir la réponse -->
                                    @if($rdv->reponse_concessionnaire)
                                        <div class="modal fade" id="reponseModal{{ $rdv->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Réponse du concessionnaire</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>{{ $rdv->reponse_concessionnaire }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Modal pour voir les détails du RDV -->
                                    <div class="modal fade" id="viewModal{{ $rdv->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Détails du rendez-vous</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6><strong>Date et heure:</strong></h6>
                                                            <p>{{ $rdv->date_time_formate }}</p>
                                                            
                                                            <h6><strong>Statut:</strong></h6>
                                                            <span class="badge {{ $rdv->statut_formate['class'] }}">
                                                                {{ $rdv->statut_formate['text'] }}
                                                            </span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6><strong>Concessionnaire:</strong></h6>
                                                            <p>{{ $rdv->concessionnaire->name ?? "N/A" }}</p>
                                                            
                                                            <h6><strong>Date de création:</strong></h6>
                                                            <p>{{ $rdv->created_at ? $rdv->created_at->format('d/m/Y à H:i') : "N/A" }}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6><strong>Client:</strong></h6>
                                                            @if($rdv->user)
                                                                <p><strong>Nom:</strong> {{ $rdv->user->name ?? $rdv->user->nom ?? "N/A" }}</p>
                                                                @if($rdv->user->email)
                                                                    <p><strong>Email:</strong> {{ $rdv->user->email }}</p>
                                                                @endif
                                                                @if($rdv->user->mobile)
                                                                    <p><strong>Téléphone:</strong> {{ $rdv->user->mobile }}</p>
                                                                @endif
                                                            @else
                                                                <p class="text-muted">Utilisateur supprimé</p>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6><strong>Gestionnaire de flotte:</strong></h6>
                                                            @if($rdv->gestionnaireDeFlotte)
                                                                <p><strong>Nom:</strong> {{ $rdv->gestionnaireDeFlotte->name ?? $rdv->gestionnaireDeFlotte->nom ?? "N/A" }}</p>
                                                                @if($rdv->gestionnaireDeFlotte->email)
                                                                    <p><strong>Email:</strong> {{ $rdv->gestionnaireDeFlotte->email }}</p>
                                                                @endif
                                                            @else
                                                                <p class="text-muted">-</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    @if($rdv->reponse_concessionnaire)
                                                        <hr>
                                                        <h6><strong>Réponse du concessionnaire:</strong></h6>
                                                        <div class="alert alert-info">
                                                            {{ $rdv->reponse_concessionnaire }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="nav-icon i-Information font-weight-bold"></i> 
                        Aucun rendez-vous trouvé pour ce concessionnaire !
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Script pour DataTables -->
<script>
$(document).ready(function() {
    $('#rdv_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "pageLength": 25,
        "order": [[ 7, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ]
    });
});
</script>

@include('layouts.footer')
