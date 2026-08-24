@include('layouts.header')
@include('layouts.menu')

@php
    $statuts = [
        'en_attente' => 'En attente',
        'attribuee' => 'Attribuée',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
        '1' => 'Actif',
        '0' => 'Inactif',
    ];

    $formatDate = function ($value) {
        if (empty($value)) {
            return 'Non défini';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $adminName = function ($lavage) {
        $name = trim(($lavage->nom ?? '') . ' ' . ($lavage->prenoms ?? ''));
        $name = $name ?: ($lavage->name ?? '');

        return $name ?: 'Non défini';
    };

    $adminContact = function ($lavage) {
        return $lavage->mobile
            ?? $lavage->telephone
            ?? $lavage->contact
            ?? $lavage->email
            ?? 'Non défini';
    };

    $stationName = function ($lavage) {
        return $lavage->station_name ?? $lavage->station_nom ?? 'Non défini';
    };

    $stationContact = function ($lavage) {
        return $lavage->station_contact
            ?? $lavage->station_mobile
            ?? $lavage->station_telephone
            ?? 'Non défini';
    };

    $commercialName = function ($lavage) {
        $name = trim(($lavage->commercial_nom ?? '') . ' ' . ($lavage->commercial_prenoms ?? ''));

        return $name ?: 'Non défini';
    };

    $requestDate = function ($lavage) use ($dateColumn) {
        return $dateColumn ? ($lavage->{$dateColumn} ?? null) : ($lavage->created_at ?? null);
    };
@endphp

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
        @endif

        <div class="card text-left mb-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Liste des lavages</h4>

                <form method="GET" action="{{ route('demande-lavages.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label>Recherche</label>
                            <input type="text" name="search" class="form-control" placeholder="Admin, contact, établissement, adresse..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label>Commercial</label>
                            <select name="commercial_id" class="form-control">
                                <option value="">Tous les commerciaux</option>
                                @foreach($commercials as $commercial)
                                    <option value="{{ $commercial->id }}" {{ (string) ($filters['commercial_id'] ?? '') === (string) $commercial->id ? 'selected' : '' }}>
                                        {{ $commercial->nom }} {{ $commercial->prenoms }} - {{ $commercial->mobile }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2 mb-3">
                            <label>Établissement lavage</label>
                            <select name="prestataire_lavage_id" class="form-control">
                                <option value="">Tous</option>
                                @foreach($prestataires as $prestataire)
                                    <option value="{{ $prestataire->id }}" {{ (string) ($filters['prestataire_lavage_id'] ?? '') === (string) $prestataire->id ? 'selected' : '' }}>
                                        {{ $prestataire->name ?? $prestataire->nom ?? ('Station #' . $prestataire->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2 mb-3">
                            <label>Statut</label>
                            <select name="statut" class="form-control">
                                <option value="">Tous</option>
                                @foreach($statuts as $value => $label)
                                    <option value="{{ $value }}" {{ (string) ($filters['statut'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2 mb-3">
                            <label>Date début</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-6 col-lg-2 mb-3">
                            <label>Date fin</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3 d-flex">
                            <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                            <a href="{{ route('demande-lavages.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card text-left">
            <div class="card-body">
                @if($demandes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Admin lavage</th>
                                    <th>Contact admin</th>
                                    <th>Établissement lavage</th>
                                    <th>Contact établissement</th>
                                    <th>Commercial</th>
                                    <th>Adresse</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($demandes as $index => $lavage)
                                    <tr>
                                        <td>{{ $demandes->firstItem() + $index }}</td>
                                        <td>{{ $formatDate($requestDate($lavage)) }}</td>
                                        <td>{{ $adminName($lavage) }}</td>
                                        <td>{{ $adminContact($lavage) }}</td>
                                        <td>{{ $stationName($lavage) }}</td>
                                        <td>{{ $stationContact($lavage) }}</td>
                                        <td>{{ $commercialName($lavage) }}</td>
                                        <td>{{ $lavage->station_adresse ?? $lavage->adresse ?? 'Non défini' }}</td>
                                        <td>{{ $statuts[(string) ($lavage->statut ?? '')] ?? ($lavage->statut ?? $lavage->station_statut ?? 'Non défini') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                        <p class="mb-2 mb-md-0">
                            Affichage de {{ $demandes->firstItem() }} à {{ $demandes->lastItem() }} sur {{ $demandes->total() }} lavage(s)
                        </p>
                        <div>
                            {{ $demandes->links() }}
                        </div>
                    </div>
                @else
                    <p>Aucun lavage trouvé.</p>
                @endif

                <a href="{{ route('index-commercial') }}" class="btn btn-secondary mt-3">Retour aux commerciaux</a>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
