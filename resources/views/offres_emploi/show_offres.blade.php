@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        <a href="{{ route('create-offre-detail') }}" class="btn btn-primary mb-3">Créer une offre</a>
        
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Liste des offres d'emploi recrutement</h4>
                
                @if($offres->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="offres_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Ordre</th>
                                    <th scope="col">Titre</th>
                                    <th scope="col">Catégorie</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Missions</th>
                                    <th scope="col">Profil recherché</th>
                                    <th scope="col">Compétences</th>
                                    <th scope="col">Pré-requis</th>
                                    <th scope="col">Date de création</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offres as $key => $offre)
                                    @php
                                        $missions = explode('; ', $offre->missions ?? '');
                                        $missions = array_filter(array_map('trim', $missions), function($m) { return !empty($m); });
                                        
                                        $profils = explode('; ', $offre->profil_rechercher ?? '');
                                        $profils = array_filter(array_map('trim', $profils), function($p) { return !empty($p); });
                                        
                                        $competences = explode('; ', $offre->competence_requises ?? '');
                                        $competences = array_filter(array_map('trim', $competences), function($c) { return !empty($c); });
                                        
                                        $prerequis = [];
                                        if ($offre->prerequis) {
                                            $prerequis = explode('; ', $offre->prerequis);
                                            $prerequis = array_filter(array_map('trim', $prerequis), function($pr) { return !empty($pr); });
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $offre->ordre }}</td>
                                        <td><strong>{{ $offre->titre }}</strong></td>
                                        <td>
                                            <span class="badge badge-primary">{{ $offre->categorie }}</span>
                                        </td>
                                        <td>{{ Str::limit($offre->description, 100) }}</td>
                                        <td>
                                            @if(count($missions) > 0)
                                                <span class="badge badge-info">{{ count($missions) }} mission(s)</span>
                                            @else
                                                <span class="text-muted">Aucune</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(count($profils) > 0)
                                                <span class="badge badge-warning">{{ count($profils) }} profil(s)</span>
                                            @else
                                                <span class="text-muted">Aucun</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(count($competences) > 0)
                                                <span class="badge badge-success">{{ count($competences) }} compétence(s)</span>
                                            @else
                                                <span class="text-muted">Aucune</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(count($prerequis) > 0)
                                                <span class="badge badge-secondary">{{ count($prerequis) }} pré-requis</span>
                                            @else
                                                <span class="text-muted">Aucun</span>
                                            @endif
                                        </td>
                                        <td>{{ $offre->created_at ? $offre->created_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#viewOffre{{ $offre->id }}" title="Voir les détails">
                                                <i class="nav-icon i-Eye font-weight-bold"></i>
                                            </a>
                                            <a class="text-primary mr-2" href="{{ route('edit-offre-recrutement', ['id' => $offre->id]) }}" title="Modifier">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-offre-recrutement', ['id' => $offre->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')" title="Supprimer">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal pour voir les détails -->
                                    <div class="modal fade" id="viewOffre{{ $offre->id }}" tabindex="-1" role="dialog" aria-labelledby="viewOffreLabel{{ $offre->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewOffreLabel{{ $offre->id }}">Détails de l'offre: {{ $offre->titre }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <strong>Ordre:</strong> {{ $offre->ordre }}
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Catégorie:</strong> <span class="badge badge-primary">{{ $offre->categorie }}</span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <p>{{ $offre->description }}</p>
                                                    </div>
                                                    @if(count($missions) > 0)
                                                    <div class="mb-3">
                                                        <strong>Missions principales:</strong>
                                                        <ul>
                                                            @foreach($missions as $mission)
                                                            <li>{{ $mission }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                    @if(count($profils) > 0)
                                                    <div class="mb-3">
                                                        <strong>Profil recherché:</strong>
                                                        <ul>
                                                            @foreach($profils as $profil)
                                                            <li>{{ $profil }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                    @if(count($competences) > 0)
                                                    <div class="mb-3">
                                                        <strong>Compétences requises:</strong>
                                                        <ul>
                                                            @foreach($competences as $competence)
                                                            <li>{{ $competence }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                    @if(count($prerequis) > 0)
                                                    <div class="mb-3">
                                                        <strong>Pré-requis:</strong>
                                                        <ul>
                                                            @foreach($prerequis as $prereq)
                                                            <li><strong>{{ $prereq }}</strong></li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endif
                                                    <div class="mb-3">
                                                        <strong>Date de création:</strong> {{ $offre->created_at ? $offre->created_at->format('d/m/Y H:i') : '-' }}
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
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
                        <p>Aucune offre d'emploi enregistrée pour le moment.</p>
                        <a href="{{ route('create-offre-detail') }}" class="btn btn-primary">Créer une offre</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
$(document).ready(function() {
    $('#offres_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[1, "asc"]], // Trier par ordre par défaut
        "pageLength": 25
    });
});
</script>
