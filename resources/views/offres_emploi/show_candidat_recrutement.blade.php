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
                <h4 class="card-title mb-3">Détails du candidat</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Informations personnelles</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Nom:</strong> {{ $candidat->nom }}</p>
                                <p><strong>Prénoms:</strong> {{ $candidat->prenoms }}</p>
                                <p><strong>Email:</strong> <a href="mailto:{{ $candidat->email }}">{{ $candidat->email }}</a></p>
                                <p><strong>Mobile:</strong> <a href="tel:{{ $candidat->mobile }}">{{ $candidat->mobile }}</a></p>
                                <p><strong>Commune:</strong> {{ $candidat->commune }}</p>
                                <p><strong>Poste:</strong> 
                                    @if($candidat->poste)
                                        <span class="badge badge-info">{{ $candidat->poste }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </p>
                                <p><strong>Date de candidature:</strong> {{ $candidat->created_at ? $candidat->created_at->format('d/m/Y à H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Offre d'emploi</h5>
                            </div>
                            <div class="card-body">
                                @if($candidat->offre)
                                    <p><strong>Titre:</strong> {{ $candidat->offre->titre }}</p>
                                    <p><strong>Catégorie:</strong> <span class="badge badge-primary">{{ $candidat->offre->categorie }}</span></p>
                                    <p><strong>Ordre:</strong> {{ $candidat->offre->ordre }}</p>
                                @else
                                    <p class="text-muted">Aucune offre associée</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Documents</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h6>CV</h6>
                                            @if($candidat->cv)
                                                <a href="https://tooauto.com/public/uploads/cv/{{ $candidat->cv }}" target="_blank" class="btn btn-success btn-block">
                                                    <i class="fa fa-download"></i> Télécharger le CV
                                                </a>
                                            @else
                                                <p class="text-muted">Aucun CV disponible</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h6>Lettre de motivation</h6>
                                            @if($candidat->lm)
                                                <a href="https://tooauto.com/public/uploads/lm/{{ $candidat->lm }}" target="_blank" class="btn btn-info btn-block">
                                                    <i class="fa fa-download"></i> Télécharger la LM
                                                </a>
                                            @else
                                                <p class="text-muted">Aucune lettre de motivation</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h6>Photo</h6>
                                            @if($candidat->photo)
                                                <a href="https://tooauto.com/public/uploads/photos/{{ $candidat->photo }}" target="_blank" class="btn btn-warning btn-block">
                                                    <i class="fa fa-image"></i> Voir la photo
                                                </a>
                                                <br>
                                                <img src="https://tooauto.com/{{ $candidat->photo }}" alt="Photo du candidat" class="img-thumbnail mt-2" style="max-width: 200px;">
                                            @else
                                                <p class="text-muted">Aucune photo disponible</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="{{ route('index-candidats-recrutement') }}" class="btn btn-secondary pd-x-20">Retour à la liste</a>
                    <form action="{{ route('delete-candidat-recrutement', ['id' => $candidat->id]) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger pd-x-20 ml-2" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce candidat ?')">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

