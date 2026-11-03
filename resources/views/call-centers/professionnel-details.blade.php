@extends('call-centers.layout')

@section('lead', 'Informations du professionnel et de ses etablissements.')

@section('header-actions')
    <a href="{{ $backUrl }}" class="btn btn-plain">
        @include('call-centers.partials.icon', ['name' => 'back'])
        {{ $backLabel }}
    </a>
@endsection

@section('content')
    @php
        $fullName = trim(($professionnel->nom ?? '') . ' ' . ($professionnel->prenoms ?? ''));
        $initials = collect(explode(' ', $fullName))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
        $isActive = isset($professionnel->statut) ? (string) $professionnel->statut === '1' : null;
        $fields = [
            'Nom' => $professionnel->nom ?? null,
            'Prenoms' => $professionnel->prenoms ?? null,
            'Role' => $professionnel->role ?? null,
            'Email' => $professionnel->email ?? null,
            'Mobile' => $professionnel->mobile ?? null,
            'Telephone' => $professionnel->telephone ?? null,
            'Date creation' => $professionnel->created_at ?? null,
        ];
    @endphp

    <style>
        .cc-avatar {
            align-items: center;
            background: linear-gradient(160deg, #a1a1a6, #7c7c80);
            border-radius: 50%;
            color: #fff;
            display: flex;
            flex-shrink: 0;
            font-size: 22px;
            font-weight: 600;
            height: 64px;
            justify-content: center;
            letter-spacing: 0;
            width: 64px;
        }
        .cc-profile-head {
            align-items: center;
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }
        .cc-profile-head h5 {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 6px;
        }
    </style>

    <div class="row">
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="cc-card h-100">
                <div class="cc-profile-head">
                    <div class="cc-avatar">{{ $initials !== '' ? $initials : '?' }}</div>
                    <div style="min-width: 0;">
                        <h5>{{ $fullName !== '' ? $fullName : 'Professionnel' }}</h5>
                        @if($isActive !== null)
                            <span class="badge {{ $isActive ? 'badge-success' : 'badge-light' }}">
                                <span class="cc-dot"></span>{{ $isActive ? 'Actif' : 'Inactif' }}
                            </span>
                        @endif
                    </div>
                </div>

                <dl class="cc-grouped">
                    @foreach ($fields as $label => $value)
                        <div class="cc-grouped-row">
                            <dt>{{ $label }}</dt>
                            <dd>
                                @if(!filled($value))
                                    <span class="cc-muted">&mdash;</span>
                                @elseif($label === 'Email')
                                    <a href="mailto:{{ $value }}">{{ $value }}</a>
                                @elseif(in_array($label, ['Mobile', 'Telephone'], true))
                                    <a href="tel:{{ preg_replace('/\s+/', '', $value) }}">{{ $value }}</a>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="cc-card h-100">
                <div class="cc-card-header">
                    <div>
                        <h5 class="cc-card-title">Etablissements</h5>
                        <p class="cc-card-subtitle">Etablissements rattaches a ce professionnel.</p>
                    </div>
                    <span class="cc-count">{{ $etablissements->count() }} element(s)</span>
                </div>

                @if($etablissements->isNotEmpty())
                    <div class="cc-table-wrap">
                        <table class="table table-hover cc-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Contact</th>
                                    <th>Adresse</th>
                                    <th>Type</th>
                                    <th>Ville</th>
                                    <th>Commune</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($etablissements as $etablissement)
                                    @php
                                        $etabActive = isset($etablissement->statut) ? (string) $etablissement->statut === '1' : null;
                                    @endphp
                                    <tr>
                                        <td data-label="Nom"><strong style="font-weight: 600;">{{ $etablissement->name ?? '-' }}</strong></td>
                                        <td data-label="Contact">{{ $etablissement->mobile ?? $etablissement->mobile_fix ?? $etablissement->email ?? '-' }}</td>
                                        <td data-label="Adresse">{{ \Illuminate\Support\Str::limit((string) ($etablissement->adresse ?? '-'), 60) }}</td>
                                        <td data-label="Type">{{ $etablissement->type_etablissement ?? $etablissement->categorie ?? '-' }}</td>
                                        <td data-label="Ville">{{ $etablissement->ville ?? '-' }}</td>
                                        <td data-label="Commune">{{ $etablissement->commune ?? '-' }}</td>
                                        <td data-label="Statut">
                                            @if($etabActive === null)
                                                <span class="cc-muted">&mdash;</span>
                                            @else
                                                <span class="badge {{ $etabActive ? 'badge-success' : 'badge-light' }}">
                                                    <span class="cc-dot"></span>{{ $etabActive ? 'Oui' : 'Non' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="cc-empty-state">
                        @include('call-centers.partials.icon', ['name' => 'etablissements'])
                        <strong>Aucun etablissement</strong>
                        <span>Aucun etablissement lie a ce professionnel.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
