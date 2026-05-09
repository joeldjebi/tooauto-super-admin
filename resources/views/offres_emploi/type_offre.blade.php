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
                <h4 class="card-title mb-3">Les types d'offres d'emploi</h4>
                @if($types->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Libellé</th>
                                    <th scope="col">Commentaire</th>
                                    <th scope="col">Post</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->libelle }}</td>
                                        <td>{{ $item->commentaire }}</td>
                                        <td>
                                            @if($item->post)
                                                @php
                                                    $posts = explode('; ', $item->post);
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
                                        </td>
                                        <td>
                                            <a class="text-success mr-2" href="{{ route('edit-type-offre', ['id' => $item->id]) }}" title="Modifier">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('delete-type-offre', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
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
                        <p>Aucune type d'offre d'emploi enregistrer !'</p>
                @endif
            </div>
        </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <h4>Ajouter un type d'offre d'emploi</h4>
            <div class="card mb-5">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <form action="{{ route('store-type-offre') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="">Libellé</label>
                                <input class="form-control" name="libelle" type="text">
                            </div>
                            <div class="form-group">
                                <label for="">Commentaire</label>
                                <textarea class="form-control" name="commentaire" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="">Posts</label>
                                <div id="posts-container">
                                    <div class="post-field-group mb-2">
                                        <div class="input-group">
                                            <input class="form-control" name="posts[]" type="text" placeholder="Entrez un post">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success btn-sm add-post" title="Ajouter un autre post">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm remove-post" title="Supprimer ce post" style="display: none;">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                            </div>
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