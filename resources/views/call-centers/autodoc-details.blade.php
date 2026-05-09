@extends('call-centers.layout')

@section('content')
    <div class="cc-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Document automobile #{{ $autodoc->id }}</h5>
                <p class="text-muted mb-0">Affichage des fichiers signes depuis Wasabi.</p>
            </div>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">{{ $backLabel }}</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Vehicule :</strong> {{ $autodoc->vehicule ?? '-' }}</p>
                <p><strong>Type document :</strong> {{ $autodoc->type_document ?? '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Source :</strong> {{ $autodoc->provenance_source ?? '-' }}</p>
                <p><strong>Date creation :</strong> {{ $autodoc->created_at ?? '-' }}</p>
            </div>
        </div>

        <h6 class="mb-3">Fichiers signes Wasabi</h6>

        @if(count($signedFiles) > 0)
            <div class="row">
                @foreach($signedFiles as $index => $file)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            @if($file['is_image'])
                                <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ $file['url'] }}" alt="Document {{ $index + 1 }}" class="card-img-top" style="height: 220px; object-fit: cover;">
                                </a>
                            @else
                                <div class="card-body d-flex flex-column justify-content-center align-items-start" style="min-height: 220px;">
                                    <h6 class="mb-2">Fichier {{ $index + 1 }}</h6>
                                    <p class="text-muted small mb-3">{{ $file['path'] }}</p>
                                    <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                        Ouvrir le fichier
                                    </a>
                                </div>
                            @endif
                            @if($file['is_image'])
                                <div class="card-body">
                                    <p class="mb-2">Image {{ $index + 1 }}</p>
                                    <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                        Ouvrir
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-light border mb-0">
                Aucun fichier disponible pour ce document automobile.
            </div>
        @endif
    </div>
@endsection
