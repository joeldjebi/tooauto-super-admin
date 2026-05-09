@include('layouts.header')
@include('layouts.menu')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif

        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Établissements enregistrés avec le code: {{ $code }}</h4>
                <p class="text-muted">
                    Commercial: {{ $commercial->nom }} {{ $commercial->prenoms }} | {{ $commercial->mobile }}
                </p>
                <div class="row mb-4">
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-primary shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Aujourd'hui</div>
                                <div class="h4 mb-0">{{ $stats['today'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-success shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Cette semaine</div>
                                <div class="h4 mb-0">{{ $stats['this_week'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-info shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Mois en cours</div>
                                <div class="h4 mb-0">{{ $stats['current_month'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-warning shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Mois précédent</div>
                                <div class="h4 mb-0">{{ $stats['previous_month'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl mb-3">
                        <div class="card border-left-dark shadow-sm h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small">Total</div>
                                <div class="h4 mb-0">{{ $stats['total'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('commercial.filleuls', ['code' => $code]) }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Rechercher par nom, contact, adresse, type..."
                                value="{{ $filters['search'] ?? '' }}"
                            >
                        </div>
                        <div class="col-md-6 col-lg-3 mt-3 mt-md-0">
                            <select name="type_etablissement_id" class="form-control">
                                <option value="">Tous les types</option>
                                @foreach($typeEtablissements as $typeEtablissement)
                                    <option value="{{ $typeEtablissement->id }}" {{ (string) ($filters['type_etablissement_id'] ?? '') === (string) $typeEtablissement->id ? 'selected' : '' }}>
                                        {{ $typeEtablissement->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-2 mt-3 mt-lg-0">
                            <input
                                type="date"
                                name="date_from"
                                class="form-control"
                                value="{{ $filters['date_from'] ?? '' }}"
                            >
                        </div>
                        <div class="col-md-6 col-lg-2 mt-3 mt-lg-0">
                            <input
                                type="date"
                                name="date_to"
                                class="form-control"
                                value="{{ $filters['date_to'] ?? '' }}"
                            >
                        </div>
                        <div class="col-md-6 col-lg-1 mt-3 mt-lg-0 d-flex">
                            <button type="submit" class="btn btn-primary btn-block mr-2">Filtrer</button>
                            <a href="{{ route('commercial.filleuls', ['code' => $code]) }}" class="btn btn-outline-secondary btn-block">Reset</a>
                        </div>
                    </div>
                </form>

                @if($filleuls->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Établissement</th>
                                    <th>Contact</th>
                                    <th>Adresse</th>
                                    <th>Type</th>
                                    <th>Gérant</th>
                                    <th>Statut</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filleuls as $index => $etablissement)
                                <tr>
                                    <td>{{ $filleuls->firstItem() + $index }}</td>
                                    <td>{{ $etablissement->name }}</td>
                                    <td>{{ $etablissement->indicatif ?? '225' }} {{ $etablissement->mobile ?? 'Non défini' }}</td>
                                    <td>{{ $etablissement->adresse }}</td>
                                    <td>{{ $etablissement->typeEtablissement->libelle ?? 'Non défini' }}</td>
                                    <td>{{ trim(($etablissement->professionnel->nom ?? '') . ' ' . ($etablissement->professionnel->prenoms ?? '')) ?: 'Non défini' }}</td>
                                    <td>{{ $etablissement->statut == 1 ? 'Actif' : 'Inactif' }}</td>
                                    <td>{{ $etablissement->mobile_fix ?: 'Non défini' }}</td>
                                    <td>{{ $etablissement->email ?: 'Non défini' }}</td>
                                    <td>{{ optional($etablissement->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('show-etablissement', ['id' => $etablissement->id]) }}" class="btn btn-info btn-sm">
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                        <p class="mb-2 mb-md-0">
                            Affichage de {{ $filleuls->firstItem() }} à {{ $filleuls->lastItem() }} sur {{ $filleuls->total() }} établissements
                        </p>
                        <div>
                            {{ $filleuls->links() }}
                        </div>
                    </div>
                @else
                    <p>Aucun établissement trouvé pour ce code.</p>
                @endif

                <a href="{{ route('index-commercial') }}" class="btn btn-secondary mt-3">Retour aux commerciaux</a>
            </div>
        </div>
    </div>
    
</div>

@include('layouts.footer')
