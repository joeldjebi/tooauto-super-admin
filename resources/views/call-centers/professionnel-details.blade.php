@extends('call-centers.layout')

@section('content')
    <div class="cc-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <p class="text-muted mb-0">Informations du professionnel et de ses etablissements.</p>
            </div>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">{{ $backLabel }}</a>
        </div>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="border rounded p-3 h-100">
                    <h6 class="mb-3">Professionnel</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 35%;">Nom</th>
                                    <td>{{ $professionnel->nom ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Prenoms</th>
                                    <td>{{ $professionnel->prenoms ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Role</th>
                                    <td>{{ $professionnel->role ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $professionnel->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile</th>
                                    <td>{{ $professionnel->mobile ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Telephone</th>
                                    <td>{{ $professionnel->telephone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>{{ isset($professionnel->statut) ? ((string) $professionnel->statut === '1' ? 'Oui' : 'Non') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Date creation</th>
                                    <td>{{ $professionnel->created_at ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-4">
                <div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Etablissements</h6>
                        <span class="badge badge-light">{{ $etablissements->count() }} element(s)</span>
                    </div>

                    @if($etablissements->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
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
                                        <tr>
                                            <td>{{ $etablissement->name ?? '-' }}</td>
                                            <td>{{ $etablissement->mobile ?? $etablissement->mobile_fix ?? $etablissement->email ?? '-' }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit((string) ($etablissement->adresse ?? '-'), 60) }}</td>
                                            <td>{{ $etablissement->type_etablissement ?? $etablissement->categorie ?? '-' }}</td>
                                            <td>{{ $etablissement->ville ?? '-' }}</td>
                                            <td>{{ $etablissement->commune ?? '-' }}</td>
                                            <td>{{ isset($etablissement->statut) ? ((string) $etablissement->statut === '1' ? 'Oui' : 'Non') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            Aucun etablissement lie a ce professionnel.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
