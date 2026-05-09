@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Liste des candidats</h4>
                
                @if($candidats->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="candidats_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénoms</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Mobile</th>
                                    <th scope="col">Commune</th>
                                    <th scope="col">Poste</th>
                                    <th scope="col">Offre</th>
                                    <th scope="col">CV</th>
                                    <th scope="col">Lettre de motivation</th>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Date de candidature</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidats as $key => $candidat)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><strong>{{ $candidat->nom }}</strong></td>
                                        <td>{{ $candidat->prenoms }}</td>
                                        <td>{{ $candidat->email }}</td>
                                        <td>{{ $candidat->mobile }}</td>
                                        <td>{{ $candidat->commune }}</td>
                                        <td>
                                            @if($candidat->poste)
                                                <span class="badge badge-info">{{ $candidat->poste }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($candidat->offre)
                                                <span class="badge badge-primary">{{ $candidat->offre->titre }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($candidat->cv)
                                                <a href="https://tooauto.com/public/uploads/cv/{{ $candidat->cv }}" target="_blank" class="btn btn-sm btn-success" title="Télécharger le CV">
                                                    <i class="fa fa-download"></i> CV
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($candidat->lm)
                                                <a href="https://tooauto.com/public/uploads/lm/{{ $candidat->lm }}" target="_blank" class="btn btn-sm btn-info" title="Télécharger la lettre de motivation">
                                                    <i class="fa fa-download"></i> LM
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($candidat->photo)
                                                <a href="https://tooauto.com/public/uploads/photos/{{ $candidat->photo }}" target="_blank" class="btn btn-sm btn-warning" title="Voir la photo">
                                                    <i class="fa fa-image"></i> Photo
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $candidat->created_at ? $candidat->created_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="{{ route('show-candidat-recrutement', ['id' => $candidat->id]) }}" title="Voir les détails">
                                                <i class="nav-icon i-Eye font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-candidat-recrutement', ['id' => $candidat->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce candidat ?')" title="Supprimer">
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
                    <div class="alert alert-info">
                        <p>Aucun candidat enregistré pour le moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
$(document).ready(function() {
    $('#candidats_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "order": [[11, "desc"]], // Trier par date de candidature par défaut
        "pageLength": 25
    });
});
</script>

