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
                <h4 class="card-title mb-3">Modifier le type d'offre d'emploi</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Informations actuelles</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Libellé :</strong> {{ $type_offre->libelle }}</p>
                                <p><strong>Commentaire :</strong> {{ $type_offre->commentaire }}</p>
                                <p><strong>Posts :</strong></p>
                                @if($type_offre->post)
                                    @php
                                        $posts = explode('; ', $type_offre->post);
                                        $posts = array_filter(array_map('trim', $posts), function($post) {
                                            return !empty($post);
                                        });
                                    @endphp
                                    @if(count($posts) > 0)
                                        @foreach($posts as $index => $post)
                                            <span class="badge badge-primary mr-1 mb-1">{{ $post }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Aucun post</span>
                                    @endif
                                @else
                                    <span class="text-muted">Aucun post</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Modifier les informations</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('update-type-offre', ['id' => $type_offre->id]) }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="libelle">Libellé</label>
                                        <input class="form-control" name="libelle" type="text" value="{{ old('libelle', $type_offre->libelle) }}" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="commentaire">Commentaire</label>
                                        <textarea class="form-control" name="commentaire" rows="3">{{ old('commentaire', $type_offre->commentaire) }}</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="posts">Posts</label>
                                        <div id="posts-container">
                                            @php
                                                $existingPosts = $type_offre->post ? explode('; ', $type_offre->post) : [''];
                                                $existingPosts = array_filter(array_map('trim', $existingPosts), function($post) {
                                                    return !empty($post);
                                                });
                                                if (empty($existingPosts)) {
                                                    $existingPosts = [''];
                                                }
                                            @endphp
                                            @foreach($existingPosts as $index => $post)
                                                <div class="post-field-group mb-2">
                                                    <div class="input-group">
                                                        <input class="form-control" name="posts[]" type="text" value="{{ old('posts.'.$index, $post) }}" placeholder="Entrez un post">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-success btn-sm add-post" title="Ajouter un autre post">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm remove-post" title="Supprimer ce post" style="display: {{ count($existingPosts) > 1 ? 'block' : 'none' }};">
                                                                <i class="fa fa-minus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <button type="submit" class="btn btn-primary pd-x-20">Mettre à jour</button>
                                        <a href="{{ route('index-type-offre') }}" class="btn btn-secondary pd-x-20 ml-2">Annuler</a>
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
    // Fonction pour ajouter un nouveau champ post
    $(document).on('click', '.add-post', function() {
        var newPostField = `
            <div class="post-field-group mb-2">
                <div class="input-group">
                    <input class="form-control" name="posts[]" type="text" placeholder="Entrez un post">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-success btn-sm add-post" title="Ajouter un autre post">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm remove-post" title="Supprimer ce post">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#posts-container').append(newPostField);
        updateButtonsVisibility();
    });
    
    // Fonction pour supprimer un champ post
    $(document).on('click', '.remove-post', function() {
        $(this).closest('.post-field-group').remove();
        updateButtonsVisibility();
    });
    
    // Fonction pour mettre à jour la visibilité des boutons
    function updateButtonsVisibility() {
        var postFields = $('.post-field-group');
        
        // Si il y a plus d'un champ, afficher le bouton supprimer sur tous
        if (postFields.length > 1) {
            $('.remove-post').show();
        } else {
            // Si il n'y a qu'un seul champ, cacher le bouton supprimer
            $('.remove-post').hide();
        }
    }
    
    // Initialiser la visibilité des boutons
    updateButtonsVisibility();
});
</script>
