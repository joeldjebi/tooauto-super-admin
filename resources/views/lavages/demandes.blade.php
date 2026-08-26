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
        $name = trim(($lavage->first_name ?? '') . ' ' . ($lavage->last_name ?? ''));
        $name = $name ?: trim(($lavage->nom ?? '') . ' ' . ($lavage->prenoms ?? ''));
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

    $logoUrl = function ($logo) {
        if (empty($logo)) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://', '/'])) {
            return $logo;
        }

        return asset('station_de_lavage/logo/' . $logo);
    };

    $commercialName = function ($lavage) {
        $name = trim(($lavage->commercial_nom ?? '') . ' ' . ($lavage->commercial_prenoms ?? ''));

        return $name ?: 'Non défini';
    };

    $requestDate = function ($lavage) use ($dateColumn) {
        return $dateColumn ? ($lavage->{$dateColumn} ?? null) : ($lavage->created_at ?? null);
    };

    $fieldLabels = [
        'nom' => 'Nom',
        'prenoms' => 'Prénoms',
        'first_name' => 'Prénom',
        'last_name' => 'Nom',
        'role' => 'Rôle',
        'name' => 'Nom',
        'mobile' => 'Mobile',
        'telephone' => 'Téléphone',
        'contact' => 'Contact',
        'email' => 'Email',
        'adresse' => 'Adresse',
        'adresse_map' => 'Adresse map',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude',
        'logo' => 'Logo',
        'statut' => 'Statut',
        'password' => 'Mot de passe',
    ];
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
                                    <th>Logo</th>
                                    <th>Contact établissement</th>
                                    <th>Commercial</th>
                                    <th>Adresse</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($demandes as $index => $lavage)
                                    @php
                                        $modalId = 'editLavage' . ($lavage->lavage_id ?? $lavage->id) . 'Station' . ($lavage->station_id ?? '0');
                                        $deleteModalId = 'deleteLavage' . ($lavage->lavage_id ?? $lavage->id) . 'Station' . ($lavage->station_id ?? '0');
                                        $stationLogoUrl = $logoUrl($lavage->station_logo ?? null);
                                    @endphp
                                    <tr>
                                        <td>{{ $demandes->firstItem() + $index }}</td>
                                        <td>{{ $formatDate($requestDate($lavage)) }}</td>
                                        <td>{{ $adminName($lavage) }}</td>
                                        <td>{{ $adminContact($lavage) }}</td>
                                        <td>{{ $stationName($lavage) }}</td>
                                        <td>
                                            @if($stationLogoUrl)
                                                <img src="{{ $stationLogoUrl }}" alt="{{ $stationName($lavage) }}" class="img-thumbnail" style="width: 54px; height: 54px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">Aucun</span>
                                            @endif
                                        </td>
                                        <td>{{ $stationContact($lavage) }}</td>
                                        <td>{{ $commercialName($lavage) }}</td>
                                        <td>{{ $lavage->station_adresse ?? $lavage->adresse ?? 'Non défini' }}</td>
                                        <td>{{ $statuts[(string) ($lavage->statut ?? '')] ?? ($lavage->statut ?? $lavage->station_statut ?? 'Non défini') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm mb-1" data-toggle="modal" data-target="#{{ $modalId }}">
                                                Modifier
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm mb-1" data-toggle="modal" data-target="#{{ $deleteModalId }}">
                                                Supprimer
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('lavages.update', ['id' => $lavage->lavage_id ?? $lavage->id]) }}" method="post" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="station_id" value="{{ $lavage->station_id ?? '' }}">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Modifier le lavage</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if(!empty($lavageEditableColumns))
                                                            <h5 class="mb-3">Infos admin lavage</h5>
                                                            <div class="row">
                                                                @foreach($lavageEditableColumns as $column)
                                                                    @if($column !== 'password')
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>{{ $fieldLabels[$column] ?? ucfirst($column) }}</label>
                                                                                @if($column === 'statut')
                                                                                    <select name="lavage[{{ $column }}]" class="form-control">
                                                                                        <option value="1" {{ (string) ($lavage->{$column} ?? '') === '1' ? 'selected' : '' }}>Actif</option>
                                                                                        <option value="0" {{ (string) ($lavage->{$column} ?? '') === '0' ? 'selected' : '' }}>Inactif</option>
                                                                                    </select>
                                                                                @else
                                                                                    <input type="{{ $column === 'email' ? 'email' : 'text' }}" name="lavage[{{ $column }}]" class="form-control" value="{{ $lavage->{$column} ?? '' }}">
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        @if(in_array('password', $lavageEditableColumns ?? [], true))
                                                            <h5 class="mb-3 mt-2">Infos d'authentification</h5>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Mot de passe</label>
                                                                        <input type="password" name="lavage[password]" class="form-control" placeholder="Laisser vide pour conserver l'actuel">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if(!empty($stationEditableColumns))
                                                            <h5 class="mb-3 mt-2">Infos établissement lavage</h5>
                                                            <div class="row">
                                                                @foreach($stationEditableColumns as $column)
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>{{ $fieldLabels[$column] ?? ucfirst($column) }}</label>
                                                                            @if($column === 'statut')
                                                                                <select name="station[{{ $column }}]" class="form-control">
                                                                                    <option value="1" {{ (string) ($lavage->{'station_' . $column} ?? '') === '1' ? 'selected' : '' }}>Actif</option>
                                                                                    <option value="0" {{ (string) ($lavage->{'station_' . $column} ?? '') === '0' ? 'selected' : '' }}>Inactif</option>
                                                                                </select>
                                                                            @elseif($column === 'logo')
                                                                                @if($stationLogoUrl)
                                                                                    <div class="mb-2">
                                                                                        <img src="{{ $stationLogoUrl }}" alt="{{ $stationName($lavage) }}" class="img-thumbnail" style="width: 90px; height: 90px; object-fit: cover;">
                                                                                    </div>
                                                                                @endif
                                                                                <input type="file" name="station[{{ $column }}]" class="form-control" accept="image/*">
                                                                            @else
                                                                                <input type="text" name="station[{{ $column }}]" class="form-control" value="{{ $lavage->{'station_' . $column} ?? '' }}">
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="{{ $deleteModalId }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Supprimer le lavage</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-0">Voulez-vous vraiment supprimer ce lavage ?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <form action="{{ route('lavages.destroy', ['id' => $lavage->lavage_id ?? $lavage->id]) }}" method="post" class="mb-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
