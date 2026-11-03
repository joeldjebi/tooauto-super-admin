@extends('call-centers.layout')

@section('lead', 'Détails et images du véhicule.')

@section('header-actions')
    <a href="{{ $backUrl }}" class="btn btn-plain">
        @include('call-centers.partials.icon', ['name' => 'back'])
        {{ $backLabel }}
    </a>
@endsection

@section('content')
    @php
        $columnsLeft = [
            'Matricule' => $vehicule->matricule ?? null,
            'Carte grise' => $vehicule->carte_grise ?? null,
            'Utilisateur' => $vehicule->utilisateur ?: null,
            'Marque' => $vehicule->marque ?? null,
        ];
        $columnsRight = [
            'Type de véhicule' => $vehicule->type_vehicule ?? null,
            'Carburant' => $vehicule->carburant ?? null,
            'Modèle' => $vehicule->modele ?? null,
            'Couleur' => $vehicule->couleur ?? null,
        ];
    @endphp

    <div class="cc-card">
        <div class="cc-card-header">
            <div class="d-flex align-items-center" style="gap: 14px;">
                <span class="cc-tile cc-tile-lg cc-tone-teal">
                    @include('call-centers.partials.icon', ['name' => 'vehicules'])
                </span>
                <div>
                    <h5 class="cc-card-title">Véhicule {{ $vehicule->matricule ?? '#' . $vehicule->id }}</h5>
                    <p class="cc-card-subtitle">{{ trim(($vehicule->marque ?? '') . ' ' . ($vehicule->modele ?? '')) ?: 'Informations générales' }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach ([$columnsLeft, $columnsRight] as $group)
                <div class="col-md-6 mb-3 mb-md-0">
                    <dl class="cc-grouped">
                        @foreach ($group as $label => $value)
                            <div class="cc-grouped-row">
                                <dt>{{ $label }}</dt>
                                <dd>{!! filled($value) ? e($value) : '<span class="cc-muted">&mdash;</span>' !!}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </div>

    <div class="cc-card">
        <div class="cc-card-header">
            <div>
                <h5 class="cc-card-title">Photos</h5>
                <p class="cc-card-subtitle">Images stockées sur Wasabi.</p>
            </div>
            <span class="cc-count">{{ count($photoUrls) }} image(s)</span>
        </div>

        @if(count($photoUrls) > 0)
            <div class="cc-gallery">
                @foreach($photoUrls as $index => $photoUrl)
                    <a href="{{ $photoUrl }}" target="_blank" rel="noopener noreferrer" class="cc-media">
                        <div class="cc-media-thumb">
                            <img src="{{ $photoUrl }}" alt="Photo véhicule {{ $index + 1 }}" loading="lazy">
                        </div>
                        <div class="cc-media-body">
                            <span>Image {{ $index + 1 }}</span>
                            <span class="cc-open">Ouvrir</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="cc-empty-state">
                @include('call-centers.partials.icon', ['name' => 'empty'])
                <strong>Aucune image</strong>
                <span>Aucune image disponible pour ce véhicule.</span>
            </div>
        @endif
    </div>
@endsection
