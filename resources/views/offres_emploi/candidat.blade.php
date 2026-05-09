@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')
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
        
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Candidatures reçues</h4>
                
                @if($candidats->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="candidats_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Candidat</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Téléphone</th>
                                    <th scope="col">Offre d'emploi</th>
                                    <th scope="col">Type d'offre</th>
                                    <th scope="col">Ville</th>
                                    <th scope="col">CV</th>
                                    <th scope="col">Date de candidature</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidats as $key => $candidat)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $candidat->nom }} {{ $candidat->prenoms }}</strong>
                                        </td>
                                        <td>
                                            <a href="mailto:{{ $candidat->email }}" class="text-primary">
                                                {{ $candidat->email }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="tel:{{ $candidat->mobile }}" class="text-success">
                                                {{ $candidat->mobile }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($candidat->offre_emploi)
                                                <span class="badge badge-info">
                                                    {{ Str::limit($candidat->offre_emploi->description, 30) }}
                                                </span>
                                            @else
                                                <span class="text-muted">Offre supprimée</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($candidat->offre_emploi && $candidat->offre_emploi->type_offre)
                                                <span class="badge badge-primary">
                                                    {{ $candidat->offre_emploi->type_offre->libelle }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($candidat->offre_emploi && $candidat->offre_emploi->ville)
                                                <span class="badge badge-secondary">
                                                    {{ $candidat->offre_emploi->ville->libelle }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($candidat->cv)
                                                <a href="{{ asset('uploads/cv/' . $candidat->cv) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Télécharger le CV">
                                                    <i class="fa fa-download"></i> CV
                                                </a>
                                            @else
                                                <span class="text-muted">Aucun CV</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $candidat->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <a class="text-info mr-2" href="{{ route('show-candidat', ['id' => $candidat->id]) }}" title="Voir les détails">
                                                <i class="nav-icon i-Eye font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-candidat', ['id' => $candidat->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?')" title="Supprimer">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune candidature reçue</h5>
                        <p class="text-muted">Les candidatures apparaîtront ici lorsqu'elles seront soumises.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

@section('scripts')
<script>
$(document).ready(function() {
    $('#candidats_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[ 8, "desc" ]], // Trier par date de candidature (colonne 8)
        "pageLength": 25,
        "responsive": true
    });
});
</script>
@endsection

