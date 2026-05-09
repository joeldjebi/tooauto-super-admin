@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

    <div class="row">
        <div class="col-lg-8 col-md-12">
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
                <h4 class="card-title mb-3">Les offres d'emploi</h4>
                @if($offres->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Type d'offre</th>
                                    <th scope="col">Ville</th>
                                    <th scope="col">Type de contrat</th>
                                    <th scope="col">Expérience</th>
                                    <th scope="col">Salaire</th>
                                    <th scope="col">Compétences</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offres as $key => $offre)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ Str::limit($offre->description, 50) }}</td>
                                        <td>{{ $offre->type_offre->libelle ?? 'N/A' }}</td>
                                        <td>{{ $offre->ville->libelle ?? 'N/A' }}</td>
                                        <td>{{ $offre->type_contrat->libelle ?? 'N/A' }}</td>
                                        <td>{{ $offre->experience }}</td>
                                        <td>{{ $offre->salaire }}</td>
                                        <td>
                                            @if($offre->competence_requises)
                                                @php
                                                    $competences = explode('; ', $offre->competence_requises);
                                                    $competences = array_filter(array_map('trim', $competences), function($comp) {
                                                        return !empty($comp);
                                                    });
                                                @endphp
                                                @if(count($competences) > 0)
                                                    @foreach(array_slice($competences, 0, 2) as $competence)
                                                        <span class="badge badge-primary mr-1 mb-1">{{ $competence }}</span>
                                                    @endforeach
                                                    @if(count($competences) > 2)
                                                        <span class="badge badge-secondary">+{{ count($competences) - 2 }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Aucune</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Aucune</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="text-success mr-2" href="{{ route('edit-offre', ['id' => $offre->id]) }}" title="Modifier">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-offre', ['id' => $offre->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete" title="Supprimer">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form> 
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p>Aucune offre d'emploi enregistrée !</p>
                @endif
            </div>
        </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <h4>Ajouter une offre d'emploi</h4>
            <div class="card mb-5">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <form action="{{ route('store-offre') }}" method="post">
                            @csrf
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" name="description" rows="3" required>{{ old('description') }}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="type_offre_id">Type d'offre</label>
                                <select class="form-control" name="type_offre_id" required>
                                    <option value="">Sélectionner un type d'offre</option>
                                    @foreach($types_offre as $type)
                                        <option value="{{ $type->id }}" {{ old('type_offre_id') == $type->id ? 'selected' : '' }}>
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
                                        <option value="{{ $ville->id }}" {{ old('ville_id') == $ville->id ? 'selected' : '' }}>
                                            {{ $ville->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="type_de_contrat_id">Type de contrat</label>
                                <select class="form-control" name="type_de_contrat_id" required>
                                    <option value="">Sélectionner un type de contrat</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('type_de_contrat_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="experience">Expérience requise</label>
                                <input class="form-control" name="experience" type="text" value="{{ old('experience') }}" placeholder="Ex: 2-5 ans" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="salaire">Salaire</label>
                                <input class="form-control" name="salaire" type="text" value="{{ old('salaire') }}" placeholder="Ex: 500 000 - 800 000 FCFA" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="competence_requises">Compétences requises</label>
                                <div id="competences-container">
                                    <div class="input-group mb-2">
                                        <input class="form-control" name="competence_requises[]" type="text" value="{{ old('competence_requises.0') }}" placeholder="Ex: Maîtrise de PHP" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-competence" title="Ajouter une compétence">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="missions">Missions</label>
                                <div id="missions-container">
                                    <div class="input-group mb-2">
                                        <input class="form-control" name="missions[]" type="text" value="{{ old('missions.0') }}" placeholder="Ex: Développement d'applications web" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-mission" title="Ajouter une mission">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="profil_rechercher">Profil recherché</label>
                                <div id="profils-container">
                                    <div class="input-group mb-2">
                                        <input class="form-control" name="profil_rechercher[]" type="text" value="{{ old('profil_rechercher.0') }}" placeholder="Ex: Développeur senior" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-profil" title="Ajouter un profil">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="avantages">Avantages</label>
                                <div id="avantages-container">
                                    <div class="input-group mb-2">
                                        <input class="form-control" name="avantages[]" type="text" value="{{ old('avantages.0') }}" placeholder="Ex: Mutuelle santé" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-avantage" title="Ajouter un avantage">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary pd-x-20">Enregistrer</button>
                            </div>
                        </form>
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
