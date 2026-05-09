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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="nav-icon i-Shop"></i> Liste des concessionnaires
                    </h4>
                    <div class="text-muted">
                        {{ $concessionnaires->count() }} concessionnaire(s) trouvé(s)
                    </div>
                </div>

                @if($concessionnaires->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Logo</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Adresse</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Véhicules</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($concessionnaires as $concessionnaire)
                                    <tr>
                                        <td>{{ $concessionnaire->id }}</td>
                                        <td>
                                            @if($concessionnaire->logo)
                                                <img src="https://concessionnaire.tooauto.com/{{ $concessionnaire->logo }}" 
                                                     alt="Logo {{ $concessionnaire->name }}" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;"
                                                     onerror="this.style.display='none'">
                                            @else
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="nav-icon i-Shop text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $concessionnaire->name }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $concessionnaire->adresse ?? 'Non renseignée' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $concessionnaire->contact ?? 'Non renseigné' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $concessionnaire->email ?? 'Non renseigné' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $concessionnaire->vehicules_count }} véhicule(s)
                                            </span>
                                        </td>
                                        <td>
                                            @if($concessionnaire->statut == 1)
                                                <span class="badge badge-success">Actif</span>
                                            @else
                                                <span class="badge badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('concessionnaires.details', $concessionnaire->id) }}" 
                                               class="btn btn-primary btn-sm" 
                                               title="Voir les détails">
                                                <i class="nav-icon i-Eye"></i> Voir détails
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="nav-icon i-Shop" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">Aucun concessionnaire trouvé</h5>
                        <p class="text-muted">Aucun concessionnaire n'est enregistré pour le moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
