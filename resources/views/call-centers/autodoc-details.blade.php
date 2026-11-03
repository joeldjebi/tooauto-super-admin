@extends('call-centers.layout')

@section('lead', 'Affichage des fichiers signés depuis Wasabi.')

@section('header-actions')
    <a href="{{ $backUrl }}" class="btn btn-plain">
        @include('call-centers.partials.icon', ['name' => 'back'])
        {{ $backLabel }}
    </a>
@endsection

@section('content')
    @php
        $columnsLeft = [
            'Véhicule' => $autodoc->vehicule ?? null,
            'Type de document' => $autodoc->type_document ?? null,
        ];
        $columnsRight = [
            'Source' => $autodoc->provenance_source ?? null,
            'Date de création' => $autodoc->created_at ?? null,
        ];
    @endphp

    <div class="cc-card">
        <div class="cc-card-header">
            <div class="d-flex align-items-center" style="gap: 14px;">
                <span class="cc-tile cc-tile-lg cc-tone-yellow">
                    @include('call-centers.partials.icon', ['name' => 'autodocs'])
                </span>
                <div>
                    <h5 class="cc-card-title">Document automobile #{{ $autodoc->id }}</h5>
                    <p class="cc-card-subtitle">{{ $autodoc->type_document ?? 'Informations générales' }}</p>
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
                <h5 class="cc-card-title">Fichiers signés</h5>
                <p class="cc-card-subtitle">Liens temporaires générés depuis Wasabi.</p>
            </div>
            <span class="cc-count">{{ count($signedFiles) }} fichier(s)</span>
        </div>

        @if(count($signedFiles) > 0)
            <div class="cc-gallery">
                @foreach($signedFiles as $index => $file)
                    <a href="{{ $file['url'] }}" target="_blank" rel="noopener noreferrer" class="cc-media">
                        <div class="cc-media-thumb">
                            @if($file['is_image'])
                                <img src="{{ $file['url'] }}" alt="Document {{ $index + 1 }}" loading="lazy">
                            @else
                                @include('call-centers.partials.icon', ['name' => 'file'])
                            @endif
                        </div>
                        <div class="cc-media-body">
                            <div style="min-width: 0;">
                                <span>{{ $file['is_image'] ? 'Image' : 'Fichier' }} {{ $index + 1 }}</span>
                                @unless($file['is_image'])
                                    <small title="{{ $file['path'] }}">{{ $file['path'] }}</small>
                                @endunless
                            </div>
                            <span class="cc-open">Ouvrir</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="cc-empty-state">
                @include('call-centers.partials.icon', ['name' => 'empty'])
                <strong>Aucun fichier</strong>
                <span>Aucun fichier disponible pour ce document automobile.</span>
            </div>
        @endif
    </div>
@endsection
