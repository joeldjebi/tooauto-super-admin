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
                <h4 class="card-title mb-3">Les annonces</h4>
                @if($annonces->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Type de demande</th>
                                    <th scope="col">Type de véhicule</th>
                                    <th scope="col">Marque</th>
                                    <th scope="col">Modèle</th>
                                    <th scope="col">Type de pièce</th>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($annonces as $key => $item)
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Détails de l'annonce</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            </div>
                                            <div class="modal-body">
                                                <h4 class="card-title mb-3">Détails de l'annonce</h4>
                                                <p><strong>Type de demande:</strong> {{ $item->typeDeDemande->libelle ?? 'N/A' }}</p>
                                                <p><strong>Type de véhicule:</strong> {{ $item->typeDeVehicule->libelle ?? 'N/A' }}</p>
                                                <p><strong>Marque:</strong> {{ $item->marque->libelle ?? 'N/A' }}</p>
                                                <p><strong>Modèle:</strong> {{ $item->modele ?? 'N/A' }}</p>
                                                <p><strong>Type de pièce:</strong> {{ $item->typeDePiece->libelle ?? 'N/A' }}</p>
                                                <p><strong>Utilisateur:</strong> {{ $item->user->name ?? 'N/A' }}</p>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>

                                        </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $item->typeDeDemande->libelle ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ $item->typeDeVehicule->libelle ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $item->marque->libelle ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $item->modele ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">
                                                {{ $item->typeDePiece->libelle ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $item->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Eye font-weight-bold"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p>Aucune annonce enregistrer !</p>
                @endif
            </div>
        </div>
    </div>
</div>





@include('layouts.footer')
