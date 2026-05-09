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

        <!-- Liste des annonces -->
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="nav-icon i-List font-weight-bold"></i> Annonces du concessionnaire
                    <span class="badge badge-primary ml-2">{{ $annonces->count() }}</span>
                </h4>
                
                @if($annonces->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="annonces_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Type de demande</th>
                                    <th scope="col">Type de véhicule</th>
                                    <th scope="col">Marque</th>
                                    <th scope="col">Modèle</th>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Date de création</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($annonces as $key => $annonce)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $annonce->typeDeDemande->libelle ?? "N/A" }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ $annonce->typeDeVehicule->libelle ?? "N/A" }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $annonce->marque->libelle ?? "N/A" }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $annonce->modele ?? "N/A" }}</strong>
                                        </td>
                                        <td>
                                            @if($annonce->user)
                                                <div>
                                                    <strong>{{ $annonce->user->name ?? $annonce->user->nom ?? "N/A" }}</strong>
                                                    @if($annonce->user->email)
                                                        <br><small class="text-muted">
                                                            <i class="nav-icon i-Email font-weight-bold"></i> {{ $annonce->user->email }}
                                                        </small>
                                                    @endif
                                                    @if($annonce->user->mobile)
                                                        <br><small class="text-muted">
                                                            <i class="nav-icon i-Phone font-weight-bold"></i> {{ $annonce->user->mobile }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Utilisateur supprimé</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($annonce->statut)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="nav-icon i-Calendar font-weight-bold"></i> 
                                                {{ $annonce->created_at ? $annonce->created_at->format('d/m/Y H:i') : "N/A" }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="nav-icon i-Information font-weight-bold"></i> 
                        Aucune annonce trouvée pour ce concessionnaire !
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Script pour DataTables -->
<script>
$(document).ready(function() {
    $('#annonces_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "pageLength": 25,
        "order": [[ 0, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [7] }
        ]
    });
});
</script>

@include('layouts.footer')
