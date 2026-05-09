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
                <h4 class="card-title mb-3">Les vehicules</h4>
                @if($vehicules->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Carte grise</th>
                                    <th scope="col">Matricule</th>
                                    <th scope="col">Nom et prénoms usager</th>
                                    <th scope="col">Contact de l'usager</th>
                                    <th scope="col">Type de vehicule</th>
                                    <th scope="col">Marque</th>
                                    <th scope="col">Type de carburant</th>
                                    <th scope="col">Couleur</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicules as $key => $item)
                                @php
                                    $jsonImages = $item->photos;
                                    $imagePaths = json_decode($jsonImages, true);

                                @endphp
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Photos du véhicule</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>
                                                @if (is_array($imagePaths))
                                                    @foreach ($imagePaths as $path)
                                                        <img src="{{ config('app.url_api_usager') }}/{{ $path }}" alt="Image" style="width:150px; margin:10px;">
                                                    @endforeach
                                                @else
                                                    <p>Erreur : Impossible de décoder le JSON.</p>
                                                @endif
                                                
                                                </p>
                                                
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->carte_grise }}</td>
                                        <td>{{ $item->matricule }}</td>
                                        <td>{{ $item->user->nom ?? "" }} {{ $item->user->prenoms ?? "" }}</td>
                                        <td>{{ $item->user->mobile ?? "" }}</td>
                                        <td>{{ $item->type_de_vehicule->libelle }}</td>
                                        <td>{{ $item->marque->libelle }}</td>
                                        <td>{{ $item->type_de_carburant->libelle }}</td>
                                        <td>{{ $item->couleur }}</td>
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