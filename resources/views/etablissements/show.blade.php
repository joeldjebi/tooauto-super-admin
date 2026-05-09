@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

@php
    $commercial = optional(optional($etablissement->parrain)->commercial);
    $professionnel = optional($etablissement->professionnel);
    $coverUrl = $etablissement->cover_url ?: $etablissement->logo_url;
    $statutLabel = $etablissement->statut ? 'Actif' : 'Inactif';
    $statutClass = $etablissement->statut ? 'success' : 'danger';
    $whatsappLabel = $etablissement->is_whatsapp ? 'Oui' : 'Non';
    $serviceMobileLabel = $etablissement->service_mobile ? 'Oui' : 'Non';
    $isCommercialLabel = ($etablissement->code_parrain && $commercial->id) ? 'Oui' : 'Non';
    $logoSourceLabel = (int) $etablissement->logo_where_is_create === 1 ? 'Mobile' : 'Web';
    $coverSourceLabel = (int) $etablissement->cover_where_is_create === 1 ? 'Mobile' : 'Web';
    $logoCreateByLabel = (int) $etablissement->logo_create_by === 2 ? 'Commercial' : 'Etablissement';
    $coverCreateByLabel = (int) $etablissement->cover_create_by === 2 ? 'Commercial' : 'Etablissement';
    $commercialName = trim(($commercial->nom ?? '') . ' ' . ($commercial->prenoms ?? ''));
    $professionnelName = trim(($professionnel->nom ?? '') . ' ' . ($professionnel->prenoms ?? ''));
    $googleMapsUrl = $etablissement->latitude && $etablissement->longitude
        ? "https://www.google.com/maps?q={$etablissement->latitude},{$etablissement->longitude}"
        : null;
    $selectedPrestations = old('type_de_prestations', json_decode($etablissement->type_de_prestations ?? '[]', true) ?: []);
@endphp

<div class="row">
    <div class="col-12">
        @if(session()->has('message'))
            <div class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card overflow-hidden">
            <div style="height: 280px; background:
                linear-gradient(135deg, rgba(15,23,42,.88), rgba(30,64,175,.65)),
                url('{{ $coverUrl }}') center/cover no-repeat;">
            </div>

            <div class="card-body" style="margin-top: -90px;">
                <div class="d-flex flex-column flex-lg-row align-items-lg-end">
                    <div class="mr-lg-4 mb-3 mb-lg-0">
                        <div class="bg-white rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 160px; height: 160px; padding: 12px;">
                            @if($etablissement->logo_url)
                                <img src="{{ $etablissement->logo_url }}" alt="{{ $etablissement->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            @else
                                <span class="text-muted text-center">Aucun logo</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-white flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center mb-2">
                            <h2 class="mb-2 mr-3 text-white">{{ $etablissement->name }}</h2>
                            <span class="badge badge-{{ $statutClass }} px-3 py-2">{{ $statutLabel }}</span>
                        </div>
                        <p class="mb-2">{{ $etablissement->typeEtablissement->libelle ?? 'Type non défini' }}</p>
                        <div class="d-flex flex-wrap">
                            <span class="badge badge-light text-dark mr-2 mb-2">Code parrain: {{ $etablissement->code_parrain ?? 'Non défini' }}</span>
                            <span class="badge badge-light text-dark mr-2 mb-2">Créé le {{ optional($etablissement->created_at)->format('d/m/Y H:i') ?? 'Non défini' }}</span>
                            <span class="badge badge-light text-dark mr-2 mb-2">Mis à jour le {{ optional($etablissement->updated_at)->format('d/m/Y H:i') ?? 'Non défini' }}</span>
                        </div>
                    </div>

                    <div class="mt-3 mt-lg-0">
                        <a href="{{ route('index-etablissements') }}" class="btn btn-light mr-2">Retour à la liste</a>
                        <a href="{{ route('show-etablissement-articles', $etablissement->id) }}" class="btn btn-info mr-2">
                            Articles ({{ $articlesCount }})
                        </a>
                        <a href="{{ route('show-etablissement-annonces', $etablissement->id) }}" class="btn btn-warning mr-2">
                            Annonces ({{ $annoncesCount }})
                        </a>
                        @if($googleMapsUrl)
                            <a href="{{ $googleMapsUrl }}" target="_blank" class="btn btn-primary">Voir sur la carte</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Presentation</h4>
                <p class="mb-0">{{ $etablissement->description ?: "Aucune description renseignée." }}</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Prestations</h4>
                @if(!empty($etablissement->types_prestations_libelles))
                    <div>
                        @foreach($etablissement->types_prestations_libelles as $libelle)
                            <span class="badge badge-info mr-2 mb-2 px-3 py-2">{{ $libelle }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="mb-0 text-muted">Aucune prestation renseignée.</p>
                @endif

                <hr>

                <p class="mb-0">
                    <strong>Valeur brute `type_de_prestations` :</strong>
                    <span>{{ $etablissement->type_de_prestations ?: 'Non définie' }}</span>
                </p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Articles récents</h4>
                    <a href="{{ route('show-etablissement-articles', $etablissement->id) }}" class="btn btn-sm btn-outline-info">Voir tous</a>
                </div>
                @if($etablissement->articles->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Libellé</th>
                                    <th>Prix</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($etablissement->articles as $article)
                                    <tr>
                                        <td style="width: 70px;">
                                            @if($article->image_url)
                                                <img src="{{ $article->image_url }}" alt="{{ $article->libelle }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $article->libelle }}</strong>
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 80) }}</div>
                                        </td>
                                        <td>{{ $article->amount ? number_format((float) $article->amount, 0, ',', ' ') . ' FCFA' : 'Non défini' }}</td>
                                        <td>{{ optional($article->created_at)->format('d/m/Y') ?: 'Non définie' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mb-0 text-muted">Aucun article rattaché à cet établissement.</p>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Annonces récentes</h4>
                    <a href="{{ route('show-etablissement-annonces', $etablissement->id) }}" class="btn btn-sm btn-outline-warning">Voir toutes</a>
                </div>
                @if($etablissement->annonces->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Annonce</th>
                                    <th>Marque</th>
                                    <th>Visible</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($etablissement->annonces as $annonce)
                                    <tr>
                                        <td style="width: 70px;">
                                            @if($annonce->image_url)
                                                <img src="{{ $annonce->image_url }}" alt="{{ $annonce->libelle ?? 'Annonce' }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $annonce->libelle ?? 'Annonce #' . $annonce->id }}</strong>
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($annonce->description ?? ''), 80) ?: 'Aucune description' }}</div>
                                        </td>
                                        <td>{{ optional($annonce->marque)->libelle ?? 'Non définie' }}</td>
                                        <td>{{ (int) ($annonce->pivot->is_visible ?? 0) === 1 ? 'Oui' : 'Non' }}</td>
                                        <td>{{ optional($annonce->created_at)->format('d/m/Y') ?: 'Non définie' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mb-0 text-muted">Aucune annonce rattachée à cet établissement.</p>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Informations de contact</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Indicatif</strong>
                        <div>{{ $etablissement->indicatif ?: 'Non défini' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Mobile</strong>
                        <div>{{ $etablissement->mobile ?: 'Non défini' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Téléphone fixe</strong>
                        <div>{{ $etablissement->mobile_fix ?: 'Non défini' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email</strong>
                        <div>{{ $etablissement->email ?: 'Non défini' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>WhatsApp</strong>
                        <div>{{ $whatsappLabel }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Service mobile</strong>
                        <div>{{ $serviceMobileLabel }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Adresse et localisation</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Adresse</strong>
                        <div>{{ $etablissement->adresse }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Adresse map</strong>
                        <div>{{ $etablissement->adresse_map ?: 'Non définie' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Pays</strong>
                        <div>{{ optional($etablissement->pays)->libelle ?? 'Non défini' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Ville</strong>
                        <div>{{ optional($etablissement->ville)->libelle ?? 'Non définie' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Commune</strong>
                        <div>{{ optional($etablissement->commune)->libelle ?? 'Non définie' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Latitude</strong>
                        <div>{{ $etablissement->latitude ?: 'Non définie' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Longitude</strong>
                        <div>{{ $etablissement->longitude ?: 'Non définie' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Mettre à jour l'établissement</h4>
                <form action="{{ route('update-etablissement', $etablissement->id) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $etablissement->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="specialite">Spécialité</label>
                            <input type="text" id="specialite" name="specialite" class="form-control" value="{{ old('specialite', $etablissement->specialite) }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $etablissement->description) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="adresse">Adresse</label>
                            <input type="text" id="adresse" name="adresse" class="form-control" value="{{ old('adresse', $etablissement->adresse) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="adresse_map">Adresse map</label>
                            <input type="text" id="adresse_map" name="adresse_map" class="form-control" value="{{ old('adresse_map', $etablissement->adresse_map) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="indicatif">Indicatif</label>
                            <input type="text" id="indicatif" name="indicatif" class="form-control" value="{{ old('indicatif', $etablissement->indicatif) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mobile">Mobile</label>
                            <input type="text" id="mobile" name="mobile" class="form-control" value="{{ old('mobile', $etablissement->mobile) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mobile_fix">Téléphone fixe</label>
                            <input type="text" id="mobile_fix" name="mobile_fix" class="form-control" value="{{ old('mobile_fix', $etablissement->mobile_fix) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $etablissement->email) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="latitude">Latitude</label>
                            <input type="number" step="any" id="latitude" name="latitude" class="form-control" value="{{ old('latitude', $etablissement->latitude) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="longitude">Longitude</label>
                            <input type="number" step="any" id="longitude" name="longitude" class="form-control" value="{{ old('longitude', $etablissement->longitude) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type_etablissement_id">Type d'établissement</label>
                            <select id="type_etablissement_id" name="type_etablissement_id" class="form-control">
                                <option value="">Sélectionner</option>
                                @foreach($typeEtablissementsList as $typeEtablissement)
                                    <option value="{{ $typeEtablissement->id }}" {{ (string) old('type_etablissement_id', $etablissement->type_etablissement_id) === (string) $typeEtablissement->id ? 'selected' : '' }}>
                                        {{ $typeEtablissement->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="categorie_service_id">Catégorie de service</label>
                            <select id="categorie_service_id" name="categorie_service_id" class="form-control">
                                <option value="">Sélectionner</option>
                                @foreach($categorieServicesList as $categorieService)
                                    <option value="{{ $categorieService->id }}" {{ (string) old('categorie_service_id', $etablissement->categorie_service_id) === (string) $categorieService->id ? 'selected' : '' }}>
                                        {{ $categorieService->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pays_id">Pays</label>
                            <select id="pays_id" name="pays_id" class="form-control">
                                <option value="">Sélectionner</option>
                                @foreach($paysList as $pays)
                                    <option value="{{ $pays->id }}" {{ (string) old('pays_id', $etablissement->pays_id) === (string) $pays->id ? 'selected' : '' }}>
                                        {{ $pays->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ville_id">Ville</label>
                            <select id="ville_id" name="ville_id" class="form-control">
                                <option value="">Sélectionner</option>
                                @foreach($villesList as $ville)
                                    <option value="{{ $ville->id }}" {{ (string) old('ville_id', $etablissement->ville_id) === (string) $ville->id ? 'selected' : '' }}>
                                        {{ $ville->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="commune_id">Commune</label>
                            <select id="commune_id" name="commune_id" class="form-control">
                                <option value="">Sélectionner</option>
                                @foreach($communesList as $commune)
                                    <option value="{{ $commune->id }}" {{ (string) old('commune_id', $etablissement->commune_id) === (string) $commune->id ? 'selected' : '' }}>
                                        {{ $commune->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="professionnel_id">Gérant</label>
                            <select id="professionnel_id" name="professionnel_id" class="form-control" required>
                                <option value="">Sélectionner</option>
                                @foreach($professionnelsList as $professionnelItem)
                                    @php
                                        $professionnelOptionLabel = trim(($professionnelItem->nom ?? '') . ' ' . ($professionnelItem->prenoms ?? ''));
                                        $professionnelOptionLabel = $professionnelOptionLabel !== '' ? $professionnelOptionLabel : 'Professionnel #' . $professionnelItem->id;
                                    @endphp
                                    <option value="{{ $professionnelItem->id }}" {{ (string) old('professionnel_id', $etablissement->professionnel_id) === (string) $professionnelItem->id ? 'selected' : '' }}>
                                        {{ $professionnelOptionLabel }}{{ !empty($professionnelItem->mobile) ? ' - ' . $professionnelItem->mobile : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code_parrain">Code parrain</label>
                            <input type="text" id="code_parrain" name="code_parrain" class="form-control" value="{{ old('code_parrain', $etablissement->code_parrain) }}">
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3 mb-3" style="background: #f8f9fa;">
                                <h5 class="mb-3">Propriétaire de l'établissement</h5>
                                <p class="text-muted mb-3">
                                    Ces champs mettent à jour la ligne liée dans la table <strong>professionnels</strong> via <strong>professionnel_id</strong>.
                                </p>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="owner_nom">Nom du propriétaire</label>
                                        <input type="text" id="owner_nom" name="owner_nom" class="form-control" value="{{ old('owner_nom', $professionnel->nom) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="owner_prenoms">Prénoms du propriétaire</label>
                                        <input type="text" id="owner_prenoms" name="owner_prenoms" class="form-control" value="{{ old('owner_prenoms', $professionnel->prenoms) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="owner_role">Rôle</label>
                                        <input type="text" id="owner_role" name="owner_role" class="form-control" value="{{ old('owner_role', $professionnel->role) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="owner_mobile">Mobile du propriétaire</label>
                                        <input type="text" id="owner_mobile" name="owner_mobile" class="form-control" value="{{ old('owner_mobile', $professionnel->mobile) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="owner_email">Email du propriétaire</label>
                                        <input type="email" id="owner_email" name="owner_email" class="form-control" value="{{ old('owner_email', $professionnel->email) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="type_de_prestations">Types de prestations</label>
                            <select id="type_de_prestations" name="type_de_prestations[]" class="form-control" multiple size="6">
                                @foreach($typesPrestationsList as $typePrestation)
                                    <option value="{{ $typePrestation->id }}" {{ in_array($typePrestation->id, array_map('intval', $selectedPrestations), true) ? 'selected' : '' }}>
                                        {{ $typePrestation->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Maintenez Ctrl ou Cmd pour sélectionner plusieurs prestations.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="logo">Chemin logo</label>
                            <input type="text" id="logo" name="logo" class="form-control" value="{{ old('logo', $etablissement->logo) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cover">Chemin couverture</label>
                            <input type="text" id="cover" name="cover" class="form-control" value="{{ old('cover', $etablissement->cover) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="logo_where_is_create">Source logo</label>
                            <input type="number" id="logo_where_is_create" name="logo_where_is_create" class="form-control" value="{{ old('logo_where_is_create', $etablissement->logo_where_is_create) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="cover_where_is_create">Source couverture</label>
                            <input type="number" id="cover_where_is_create" name="cover_where_is_create" class="form-control" value="{{ old('cover_where_is_create', $etablissement->cover_where_is_create) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="logo_create_by">Logo créé par</label>
                            <input type="number" id="logo_create_by" name="logo_create_by" class="form-control" value="{{ old('logo_create_by', $etablissement->logo_create_by) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="cover_create_by">Cover créée par</label>
                            <input type="number" id="cover_create_by" name="cover_create_by" class="form-control" value="{{ old('cover_create_by', $etablissement->cover_create_by) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="is_whatsapp">WhatsApp</label>
                            <select id="is_whatsapp" name="is_whatsapp" class="form-control">
                                <option value="1" {{ (string) old('is_whatsapp', $etablissement->is_whatsapp) === '1' ? 'selected' : '' }}>Oui</option>
                                <option value="0" {{ (string) old('is_whatsapp', $etablissement->is_whatsapp) === '0' ? 'selected' : '' }}>Non</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="service_mobile">Service mobile</label>
                            <select id="service_mobile" name="service_mobile" class="form-control">
                                <option value="1" {{ (string) old('service_mobile', $etablissement->service_mobile) === '1' ? 'selected' : '' }}>Oui</option>
                                <option value="0" {{ (string) old('service_mobile', $etablissement->service_mobile) === '0' ? 'selected' : '' }}>Non</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="statut">Statut</label>
                            <select id="statut" name="statut" class="form-control">
                                <option value="1" {{ (string) old('statut', $etablissement->statut) === '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ (string) old('statut', $etablissement->statut) === '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </form>

                <hr>

                <div class="mt-4">
                    <h5 class="text-danger">Suppression définitive</h5>
                    <p class="text-muted">
                        Cette action supprime l'établissement, ses articles, ses promotions, ses services, ses abonnements pro
                        et ses liaisons avec les annonces.
                    </p>
                    <button
                        type="button"
                        class="btn btn-danger"
                        data-toggle="modal"
                        data-target="#deleteEtablissementDetailModal"
                    >
                        Supprimer définitivement cet établissement
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Résumé</h4>
                <div class="mb-3">
                    <strong>ID</strong>
                    <div>#{{ $etablissement->id }}</div>
                </div>
                <div class="mb-3">
                    <strong>Type établissement</strong>
                    <div>{{ $etablissement->typeEtablissement->libelle ?? 'Non défini' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Spécialité</strong>
                    <div>{{ $etablissement->specialite ?: 'Non définie' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Catégorie service ID</strong>
                    <div>{{ $etablissement->categorieService->libelle ?? 'Non définie' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Est lié à un commercial</strong>
                    <div>{{ $isCommercialLabel }}</div>
                </div>
                <div class="mb-0">
                    <strong>Statut</strong>
                    <div>{{ $statutLabel }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Gérant</h4>
                <div class="mb-3">
                    <strong>Nom complet</strong>
                    <div>{{ $professionnelName ?: 'Non défini' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Mobile</strong>
                    <div>{{ $professionnel->mobile ?? 'Non défini' }}</div>
                </div>
                <div class="mb-0">
                    <strong>ID professionnel</strong>
                    <div>{{ $etablissement->professionnel_id ?: 'Non défini' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Commercial</h4>
                <div class="mb-3">
                    <strong>Nom complet</strong>
                    <div>{{ $commercialName ?: 'Non défini' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Mobile</strong>
                    <div>{{ $commercial->mobile ?? 'Non défini' }}</div>
                </div>
                <div class="mb-0">
                    <strong>ID commercial</strong>
                    <div>{{ $commercial->id ?? 'Non défini' }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Médias</h4>
                <div class="mb-3">
                    <strong>Logo</strong>
                    <div>{{ $etablissement->logo ?: 'Non défini' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Couverture</strong>
                    <div>{{ $etablissement->cover ?: 'Non définie' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Source du logo</strong>
                    <div>{{ $logoSourceLabel }}</div>
                </div>
                <div class="mb-3">
                    <strong>Source de la couverture</strong>
                    <div>{{ $coverSourceLabel }}</div>
                </div>
                <div class="mb-3">
                    <strong>Logo créé par</strong>
                    <div>{{ $logoCreateByLabel }}</div>
                </div>
                <div class="mb-0">
                    <strong>Couverture créée par</strong>
                    <div>{{ $coverCreateByLabel }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Références techniques</h4>
                <div class="mb-3">
                    <strong>Pays ID</strong>
                    <div>{{ $etablissement->pays->libelle ?? 'Non défini' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Ville ID</strong>
                    <div>{{ $etablissement->ville->libelle ?? 'Non défini' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Commune ID</strong>
                    <div>{{ $etablissement->commune->nom ?? 'Non défini' }}</div>
                </div>
                <div class="mb-3">
                    <strong>Type établissement ID</strong>
                    <div>{{ $etablissement->typeEtablissement->libelle ?? 'Non défini' }}</div>
                </div>
                <div class="mb-0">
                    <strong>Code parrain</strong>
                    <div>{{ $etablissement->code_parrain ?: 'Non défini' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteEtablissementDetailModal" tabindex="-1" role="dialog" aria-labelledby="deleteEtablissementDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEtablissementDetailModalLabel">Confirmer la suppression définitive</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Vous allez supprimer définitivement :</p>
                <p class="font-weight-bold mb-3">{{ $etablissement->name }}</p>
                <p class="text-danger mb-0">
                    Cette action est irréversible et supprimera aussi toutes les données liées à cet établissement.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <form action="{{ route('delete-etablissement', $etablissement->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
