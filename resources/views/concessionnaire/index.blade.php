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
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Les concessionnaires</h4>
                @if($concessionnaires->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="concessionnaires_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Logo</th>
                                        <th scope="col">Nom</th>
                                        <th scope="col">Propriétaire</th>
                                        <th scope="col">Adresse</th>
                                        <th scope="col">Contact</th>
                                        <th scope="col">Email</th>
                                    <th scope="col">Ville</th>
                                    <th scope="col">Pays</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($concessionnaires as $key => $concessionnaire)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if($concessionnaire->logo)
                                                <img src="https://concessionnaire.tooauto.com/concessionnaire/logo/{{ $concessionnaire->logo }}" 
                                                     alt="Logo" 
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                            @else
                                                <div style="width: 50px; height: 50px; background-color: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="nav-icon i-Image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $concessionnaire->name ?? "N/A" }}</strong>
                                            @if($concessionnaire->is_whatsapp)
                                                <span class="badge badge-success ml-1">
                                                    <i class="nav-icon i-Whatsapp font-weight-bold"></i> WhatsApp
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($concessionnaire->userConcessionnaire)
                                                <div>
                                                    <strong>{{ $concessionnaire->userConcessionnaire->nom_complet }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="nav-icon i-Email font-weight-bold"></i> {{ $concessionnaire->userConcessionnaire->email }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="nav-icon i-Phone font-weight-bold"></i> {{ $concessionnaire->userConcessionnaire->telephone_complet }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">Aucun propriétaire</span>
                                            @endif
                                        </td>
                                        <td>{{ $concessionnaire->adresse ?? "N/A" }}</td>
                                        <td>
                                            @if($concessionnaire->contact)
                                                <a href="tel:{{ $concessionnaire->contact }}" class="text-primary">
                                                    <i class="nav-icon i-Phone font-weight-bold"></i> {{ $concessionnaire->contact }}
                                                </a>
                                            @endif
                                            @if($concessionnaire->mobile_fix)
                                                <br><small class="text-muted">{{ $concessionnaire->mobile_fix }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($concessionnaire->email)
                                                <a href="mailto:{{ $concessionnaire->email }}" class="text-primary">
                                                    <i class="nav-icon i-Email font-weight-bold"></i> {{ $concessionnaire->email }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{ $concessionnaire->ville->libelle ?? "N/A" }}
                                            @if($concessionnaire->commune)
                                                <br><small class="text-muted">{{ $concessionnaire->commune->nom }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $concessionnaire->pays->libelle ?? "N/A" }}</td>
                                        <td>
                                            @if($concessionnaire->statut)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        data-toggle="modal" 
                                                        data-target="#viewModal{{ $concessionnaire->id }}"
                                                        title="Voir les détails">
                                                    <i class="nav-icon i-Eye font-weight-bold"></i>
                                                </button>
                                                <a href="{{ route('concessionnaires.annonces', $concessionnaire->id) }}" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Voir les annonces">
                                                    <i class="nav-icon i-Bar-Chart font-weight-bold"></i>
                                                </a>
                                                <a href="{{ route('concessionnaires.vehicules', $concessionnaire->id) }}" 
                                                   class="btn btn-sm btn-success" 
                                                   title="Voir les véhicules">
                                                    <i class="nav-icon i-Car font-weight-bold"></i>
                                                </a>
                                                <a href="{{ route('concessionnaires.rdv', $concessionnaire->id) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Voir les rendez-vous">
                                                    <i class="nav-icon i-Calendar font-weight-bold"></i>
                                                </a>
                                                {{-- <button type="button" class="btn btn-sm btn-warning" 
                                                        data-toggle="modal" 
                                                        data-target="#editModal{{ $concessionnaire->id }}"
                                                        title="Modifier">
                                                    <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                                </button> --}}
                                                {{-- <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete({{ $concessionnaire->id }})"
                                                        title="Supprimer">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button> --}}
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal pour voir les détails -->
                                    <div class="modal fade" id="viewModal{{ $concessionnaire->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Détails du concessionnaire</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            @if($concessionnaire->logo)
                                                                <img src="https://concessionnaire.tooauto.com/concessionnaire/logo/{{ $concessionnaire->logo }}" 
                                                                     alt="Logo" 
                                                                     class="img-fluid rounded">
                                                            @endif
                                                        </div>
                                                        <div class="col-md-8">
                                                            <h5>{{ $concessionnaire->name }}</h5>
                                                            <p><strong>Adresse:</strong> {{ $concessionnaire->adresse ?? "N/A" }}</p>
                                                            <p><strong>Contact:</strong> {{ $concessionnaire->contact ?? "N/A" }}</p>
                                                            <p><strong>Email:</strong> {{ $concessionnaire->email ?? "N/A" }}</p>
                                                            <p><strong>Localisation:</strong> {{ $concessionnaire->ville->libelle ?? "N/A" }}, {{ $concessionnaire->pays->libelle ?? "N/A" }}</p>
                                                            @if($concessionnaire->description)
                                                                <p><strong>Description:</strong> {{ $concessionnaire->description }}</p>
                                                            @endif
                                                            @if($concessionnaire->adresse_map)
                                                                <p><strong>Adresse Google Maps:</strong> 
                                                                    <a href="{{ $concessionnaire->adresse_map }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-map-marker-alt"></i> Voir sur la carte
                                                                    </a>
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
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
                        <i class="fas fa-info-circle"></i> Aucun concessionnaire enregistré !
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Script pour la suppression -->
<script>
function confirmDelete(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce concessionnaire ?')) {
        // Créer un formulaire de suppression
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("concessionnaires") }}/' + id;
        
        // Ajouter le token CSRF
        var csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Ajouter la méthode DELETE
        var methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@include('layouts.footer')
