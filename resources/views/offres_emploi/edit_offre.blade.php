@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Modifier l'offre d'emploi</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Informations actuelles</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Description :</strong> {{ Str::limit($offre->description, 100) }}</p>
                                <p><strong>Type d'offre :</strong> {{ $offre->type_offre->libelle ?? 'N/A' }}</p>
                                <p><strong>Ville :</strong> {{ $offre->ville->libelle ?? 'N/A' }}</p>
                                <p><strong>Type de contrat :</strong> {{ $offre->type_contrat->libelle ?? 'N/A' }}</p>
                                <p><strong>Expérience :</strong> {{ $offre->experience }}</p>
                                <p><strong>Salaire :</strong> {{ $offre->salaire }}</p>
                                <p><strong>Date de création :</strong> {{ $offre->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Modifier les informations</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('update-offre', ['id' => $offre->id]) }}" method="post">
                                    @csrf
                                    
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" name="description" rows="3" required>{{ old('description', $offre->description) }}</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="type_offre_id">Type d'offre</label>
                                        <select class="form-control" name="type_offre_id" required>
                                            <option value="">Sélectionner un type d'offre</option>
                                            @foreach($types_offre as $type)
                                                <option value="{{ $type->id }}" {{ old('type_offre_id', $offre->type_offre_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="ville_id">Ville</label>
                                        <select class="form-control" name="ville_id" required>
                                            <option value="">Sélectionner une ville</option>
                                            @foreach($villes as $ville)
                                                <option value="{{ $ville->id }}" {{ old('ville_id', $offre->ville_id) == $ville->id ? 'selected' : '' }}>
                                                    {{ $ville->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="type_de_contrat_id">Type de contrat</label>
                                        <select class="form-control" name="type_de_contrat_id" required>
                                            <option value="">Sélectionner un type de contrat</option>
                                            @foreach($types_contrat as $type)
                                                <option value="{{ $type->id }}" {{ old('type_de_contrat_id', $offre->type_de_contrat_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="experience">Expérience requise</label>
                                        <input class="form-control" name="experience" type="text" value="{{ old('experience', $offre->experience) }}" placeholder="Ex: 2-5 ans" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="salaire">Salaire</label>
                                        <input class="form-control" name="salaire" type="text" value="{{ old('salaire', $offre->salaire) }}" placeholder="Ex: 500 000 - 800 000 FCFA" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="competence_requises">Compétences requises</label>
                                        <div id="competences-container">
                                            @php
                                                $competences = old('competence_requises', explode('; ', $offre->competence_requises));
                                                $competences = array_filter(array_map('trim', $competences), function($comp) {
                                                    return !empty($comp);
                                                });
                                            @endphp
                                            @if(count($competences) > 0)
                                                @foreach($competences as $index => $competence)
                                                    <div class="input-group mb-2">
                                                        <input class="form-control" name="competence_requises[]" type="text" value="{{ $competence }}" placeholder="Ex: Maîtrise de PHP" required>
                                                        <div class="input-group-append">
                                                            @if($index == 0)
                                                                <button type="button" class="btn btn-success add-competence" title="Ajouter une compétence">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-danger remove-field" title="Supprimer">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group mb-2">
                                                    <input class="form-control" name="competence_requises[]" type="text" value="{{ old('competence_requises.0') }}" placeholder="Ex: Maîtrise de PHP" required>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success add-competence" title="Ajouter une compétence">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="missions">Missions</label>
                                        <div id="missions-container">
                                            @php
                                                $missions = old('missions', explode('; ', $offre->missions));
                                                $missions = array_filter(array_map('trim', $missions), function($mission) {
                                                    return !empty($mission);
                                                });
                                            @endphp
                                            @if(count($missions) > 0)
                                                @foreach($missions as $index => $mission)
                                                    <div class="input-group mb-2">
                                                        <input class="form-control" name="missions[]" type="text" value="{{ $mission }}" placeholder="Ex: Développement d'applications web" required>
                                                        <div class="input-group-append">
                                                            @if($index == 0)
                                                                <button type="button" class="btn btn-success add-mission" title="Ajouter une mission">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-danger remove-field" title="Supprimer">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group mb-2">
                                                    <input class="form-control" name="missions[]" type="text" value="{{ old('missions.0') }}" placeholder="Ex: Développement d'applications web" required>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success add-mission" title="Ajouter une mission">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="profil_rechercher">Profil recherché</label>
                                        <div id="profils-container">
                                            @php
                                                $profils = old('profil_rechercher', explode('; ', $offre->profil_rechercher));
                                                $profils = array_filter(array_map('trim', $profils), function($profil) {
                                                    return !empty($profil);
                                                });
                                            @endphp
                                            @if(count($profils) > 0)
                                                @foreach($profils as $index => $profil)
                                                    <div class="input-group mb-2">
                                                        <input class="form-control" name="profil_rechercher[]" type="text" value="{{ $profil }}" placeholder="Ex: Développeur senior" required>
                                                        <div class="input-group-append">
                                                            @if($index == 0)
                                                                <button type="button" class="btn btn-success add-profil" title="Ajouter un profil">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-danger remove-field" title="Supprimer">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group mb-2">
                                                    <input class="form-control" name="profil_rechercher[]" type="text" value="{{ old('profil_rechercher.0') }}" placeholder="Ex: Développeur senior" required>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success add-profil" title="Ajouter un profil">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="avantages">Avantages</label>
                                        <div id="avantages-container">
                                            @php
                                                $avantages = old('avantages', explode('; ', $offre->avantages));
                                                $avantages = array_filter(array_map('trim', $avantages), function($avantage) {
                                                    return !empty($avantage);
                                                });
                                            @endphp
                                            @if(count($avantages) > 0)
                                                @foreach($avantages as $index => $avantage)
                                                    <div class="input-group mb-2">
                                                        <input class="form-control" name="avantages[]" type="text" value="{{ $avantage }}" placeholder="Ex: Mutuelle santé" required>
                                                        <div class="input-group-append">
                                                            @if($index == 0)
                                                                <button type="button" class="btn btn-success add-avantage" title="Ajouter un avantage">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-danger remove-field" title="Supprimer">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group mb-2">
                                                    <input class="form-control" name="avantages[]" type="text" value="{{ old('avantages.0') }}" placeholder="Ex: Mutuelle santé" required>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success add-avantage" title="Ajouter un avantage">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <button type="submit" class="btn btn-primary pd-x-20">Mettre à jour</button>
                                        <a href="{{ route('index-offre') }}" class="btn btn-secondary pd-x-20 ml-2">Annuler</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
$(document).ready(function() {
    // Gestion des compétences
    $(document).on('click', '.add-competence', function() {
        var container = $('#competences-container');
        var newField = '<div class="input-group mb-2">' +
            '<input class="form-control" name="competence_requises[]" type="text" placeholder="Ex: Maîtrise de PHP" required>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger remove-field" title="Supprimer">' +
                    '<i class="fa fa-minus"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        container.append(newField);
    });

    // Gestion des missions
    $(document).on('click', '.add-mission', function() {
        var container = $('#missions-container');
        var newField = '<div class="input-group mb-2">' +
            '<input class="form-control" name="missions[]" type="text" placeholder="Ex: Développement d\'applications web" required>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger remove-field" title="Supprimer">' +
                    '<i class="fa fa-minus"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        container.append(newField);
    });

    // Gestion des profils
    $(document).on('click', '.add-profil', function() {
        var container = $('#profils-container');
        var newField = '<div class="input-group mb-2">' +
            '<input class="form-control" name="profil_rechercher[]" type="text" placeholder="Ex: Développeur senior" required>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger remove-field" title="Supprimer">' +
                    '<i class="fa fa-minus"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        container.append(newField);
    });

    // Gestion des avantages
    $(document).on('click', '.add-avantage', function() {
        var container = $('#avantages-container');
        var newField = '<div class="input-group mb-2">' +
            '<input class="form-control" name="avantages[]" type="text" placeholder="Ex: Mutuelle santé" required>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger remove-field" title="Supprimer">' +
                    '<i class="fa fa-minus"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        container.append(newField);
    });

    // Suppression des champs
    $(document).on('click', '.remove-field', function() {
        $(this).closest('.input-group').remove();
    });
});
</script>
