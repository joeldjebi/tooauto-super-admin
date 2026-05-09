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
                <h4 class="card-title mb-3">Modifier une offre d'emploi</h4>
                
                <form action="{{ route('update-offre-recrutement', ['id' => $offre->id]) }}" method="post">
                    @csrf
                    
                    <div class="form-group">
                        <label for="titre">Titre du poste <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titre" id="titre" value="{{ old('titre', $offre->titre) }}" placeholder="Ex: Responsable Commercial (E)" required>
                        <small class="form-text text-muted">Ex: 1. Responsable Commercial (E)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="ordre">Numéro d'ordre <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="ordre" id="ordre" value="{{ old('ordre', $offre->ordre) }}" min="1" required>
                        <small class="form-text text-muted">Numéro d'ordre d'affichage (1, 2, 3, etc.)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="categorie">Catégorie/Badge <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="categorie" id="categorie" value="{{ old('categorie', $offre->categorie) }}" placeholder="Ex: Management, Vente, Digital, etc." required>
                        <small class="form-text text-muted">Ex: Management, Vente, Digital, Audiovisuel, etc.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description du poste <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="description" rows="5" required>{{ old('description', $offre->description) }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Missions principales <span class="text-danger">*</span></label>
                        <div id="missions-container">
                            @php
                                $missions = old('missions', explode('; ', $offre->missions ?? ''));
                                $missions = array_filter(array_map('trim', $missions), function($m) { return !empty($m); });
                            @endphp
                            @if(count($missions) > 0)
                                @foreach($missions as $index => $mission)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="missions[]" value="{{ $mission }}" placeholder="Ex: Élaborer et mettre en œuvre la stratégie commerciale" required>
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
                                    <input type="text" class="form-control" name="missions[]" placeholder="Ex: Élaborer et mettre en œuvre la stratégie commerciale" required>
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
                        <label>Profil recherché <span class="text-danger">*</span></label>
                        <div id="profils-container">
                            @php
                                $profils = old('profil_recherche', explode('; ', $offre->profil_rechercher ?? ''));
                                $profils = array_filter(array_map('trim', $profils), function($p) { return !empty($p); });
                            @endphp
                            @if(count($profils) > 0)
                                @foreach($profils as $index => $profil)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="profil_recherche[]" value="{{ $profil }}" placeholder="Ex: Formation bac+3 à bac+5" required>
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
                                    <input type="text" class="form-control" name="profil_recherche[]" placeholder="Ex: Formation bac+3 à bac+5" required>
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
                        <label>Compétences requises <span class="text-danger">*</span></label>
                        <div id="competences-container">
                            @php
                                $competences = old('competences', explode('; ', $offre->competence_requises ?? ''));
                                $competences = array_filter(array_map('trim', $competences), function($c) { return !empty($c); });
                            @endphp
                            @if(count($competences) > 0)
                                @foreach($competences as $index => $competence)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="competences[]" value="{{ $competence }}" placeholder="Ex: Maîtrise des techniques de vente" required>
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
                                    <input type="text" class="form-control" name="competences[]" placeholder="Ex: Maîtrise des techniques de vente" required>
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
                        <label>Pré-requis (optionnel)</label>
                        <div id="prerequis-container">
                            @php
                                $prerequis = [];
                                if ($offre->prerequis) {
                                    $prerequis = old('prerequis', explode('; ', $offre->prerequis));
                                    $prerequis = array_filter(array_map('trim', $prerequis), function($pr) { return !empty($pr); });
                                }
                            @endphp
                            @if(count($prerequis) > 0)
                                @foreach($prerequis as $index => $prereq)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="prerequis[]" value="{{ $prereq }}" placeholder="Ex: DISPOSER D'UN SMARTPHONE">
                                        <div class="input-group-append">
                                            @if($index == 0)
                                                <button type="button" class="btn btn-success add-prerequis" title="Ajouter un pré-requis">
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
                                    <input type="text" class="form-control" name="prerequis[]" placeholder="Ex: DISPOSER D'UN SMARTPHONE">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success add-prerequis" title="Ajouter un pré-requis">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary pd-x-20">Mettre à jour</button>
                        <a href="{{ route('show-offres') }}" class="btn btn-secondary pd-x-20 ml-2">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
$(document).ready(function() {
    // Gestion des missions
    $(document).on('click', '.add-mission', function() {
        var container = $('#missions-container');
        var newField = '<div class="input-group mb-2">' +
            '<input type="text" class="form-control" name="missions[]" placeholder="Ex: Élaborer et mettre en œuvre la stratégie commerciale" required>' +
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
            '<input type="text" class="form-control" name="profil_recherche[]" placeholder="Ex: Formation bac+3 à bac+5" required>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger remove-field" title="Supprimer">' +
                    '<i class="fa fa-minus"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        container.append(newField);
    });

    // Gestion des compétences
    $(document).on('click', '.add-competence', function() {
        var container = $('#competences-container');
        var newField = '<div class="input-group mb-2">' +
            '<input type="text" class="form-control" name="competences[]" placeholder="Ex: Maîtrise des techniques de vente" required>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger remove-field" title="Supprimer">' +
                    '<i class="fa fa-minus"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        container.append(newField);
    });

    // Gestion des pré-requis
    $(document).on('click', '.add-prerequis', function() {
        var container = $('#prerequis-container');
        var newField = '<div class="input-group mb-2">' +
            '<input type="text" class="form-control" name="prerequis[]" placeholder="Ex: DISPOSER D\'UN SMARTPHONE">' +
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

