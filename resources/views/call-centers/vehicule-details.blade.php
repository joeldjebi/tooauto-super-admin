@extends('call-centers.layout')

@section('content')
    <div class="cc-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Vehicule {{ $vehicule->matricule ?? '#' . $vehicule->id }}</h5>
                <p class="text-muted mb-0">Details et images du vehicule.</p>
            </div>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">{{ $backLabel }}</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Matricule :</strong> {{ $vehicule->matricule ?? '-' }}</p>
                <p><strong>Carte grise :</strong> {{ $vehicule->carte_grise ?? '-' }}</p>
                <p><strong>Utilisateur :</strong> {{ $vehicule->utilisateur ?: '-' }}</p>
                <p><strong>Marque :</strong> {{ $vehicule->marque ?? '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Type de vehicule :</strong> {{ $vehicule->type_vehicule ?? '-' }}</p>
                <p><strong>Carburant :</strong> {{ $vehicule->carburant ?? '-' }}</p>
                <p><strong>Modele :</strong> {{ $vehicule->modele ?? '-' }}</p>
                <p><strong>Couleur :</strong> {{ $vehicule->couleur ?? '-' }}</p>
            </div>
        </div>

        <h6 class="mb-3">Images Wasabi</h6>

        @if(count($photoUrls) > 0)
            <div class="row">
                @foreach($photoUrls as $index => $photoUrl)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <a href="{{ $photoUrl }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $photoUrl }}" alt="Photo vehicule {{ $index + 1 }}" class="card-img-top" style="height: 220px; object-fit: cover;">
                            </a>
                            <div class="card-body">
                                <p class="mb-2">Image {{ $index + 1 }}</p>
                                <a href="{{ $photoUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                    Ouvrir
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-light border mb-0">
                Aucune image disponible pour ce vehicule.
            </div>
        @endif
    </div>
@endsection
