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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Détails de la candidature</h4>
                    <a href="{{ route('index-candidat') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
                
                <div class="row">
                    <!-- Informations du candidat -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fa fa-user"></i> Informations du candidat</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Nom :</strong></div>
                                    <div class="col-sm-8">{{ $candidat->nom }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Prénoms :</strong></div>
                                    <div class="col-sm-8">{{ $candidat->prenoms }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Email :</strong></div>
                                    <div class="col-sm-8">
                                        <a href="mailto:{{ $candidat->email }}" class="text-primary">
                                            {{ $candidat->email }}
                                        </a>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Téléphone :</strong></div>
                                    <div class="col-sm-8">
                                        <a href="tel:{{ $candidat->mobile }}" class="text-success">
                                            {{ $candidat->mobile }}
                                        </a>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Date de candidature :</strong></div>
                                    <div class="col-sm-8">
                                        <span class="badge badge-info">
                                            {{ $candidat->created_at->format('d/m/Y à H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>CV :</strong></div>
                                    <div class="col-sm-8">
                                        @if($candidat->cv)
                                            <a href="{{ asset('uploads/cv/' . $candidat->cv) }}" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fa fa-download"></i> Télécharger le CV
                                            </a>
                                        @else
                                            <span class="text-muted">Aucun CV fourni</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informations de l'offre -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fa fa-briefcase"></i> Offre d'emploi</h5>
                            </div>
                            <div class="card-body">
                                @if($candidat->offre_emploi)
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Description :</strong></div>
                                        <div class="col-sm-8">{{ $candidat->offre_emploi->description }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Type d'offre :</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge badge-primary">
                                                {{ $candidat->offre_emploi->type_offre->libelle ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Ville :</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge badge-secondary">
                                                {{ $candidat->offre_emploi->ville->libelle ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Type de contrat :</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge badge-info">
                                                {{ $candidat->offre_emploi->type_contrat->libelle ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Expérience :</strong></div>
                                        <div class="col-sm-8">{{ $candidat->offre_emploi->experience }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Salaire :</strong></div>
                                        <div class="col-sm-8">{{ $candidat->offre_emploi->salaire }}</div>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        Cette offre d'emploi a été supprimée.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lettre de motivation -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fa fa-file-text"></i> Lettre de motivation</h5>
                            </div>
                            <div class="card-body">
                                @if($candidat->lm)
                                    <div class="bg-light p-3 rounded">
                                        {!! nl2br(e($candidat->lm)) !!}
                                    </div>
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="fa fa-file-text-o fa-2x mb-2"></i>
                                        <p>Aucune lettre de motivation fournie</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <a href="mailto:{{ $candidat->email }}" class="btn btn-primary mr-2">
                                    <i class="fa fa-envelope"></i> Envoyer un email
                                </a>
                                <a href="tel:{{ $candidat->mobile }}" class="btn btn-success mr-2">
                                    <i class="fa fa-phone"></i> Appeler
                                </a>
                                @if($candidat->cv)
                                    <a href="{{ asset('uploads/cv/' . $candidat->cv) }}" target="_blank" class="btn btn-info mr-2">
                                        <i class="fa fa-download"></i> Télécharger le CV
                                    </a>
                                @endif
                                <form action="{{ route('delete-candidat', ['id' => $candidat->id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?')">
                                        <i class="fa fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

