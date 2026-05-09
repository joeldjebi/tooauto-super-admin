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
                <h4 class="card-title mb-3">Les Prestations</h4>
                @if($services->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Libelle</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Montant Minimum</th>
                                    <th scope="col">Etablissement</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($services as $key => $item)
                                    <div class="modal fade" id="id{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Détails de la prestation</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            </div>
                                            <div class="modal-body">
                                                <h4 class="card-title mb-3">Description de la prestation</h4>
                                                <p>{!! html_entity_decode($item->description) !!}</p>

                                                <h4 class="card-title mb-3">Image de la prestation</h4>
                                                <p>
                                                    <img 
                                                        width="250" 
                                                        height="250" 
                                                        src="{{ config('app.url_etablissement_pro') }}/services/image/{{ $item->image }}" 
                                                        alt="{{ $item->libelle }}"
                                                    >
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
                                        <td>
                                            <img 
                                                width="50" 
                                                height="50" 
                                                src="{{ config('app.url_etablissement_pro') }}/services/image/{{ $item->image }}" 
                                                alt="{{ $item->libelle }}"
                                            >
                                        </td>
                                        <td>{{ $item->libelle }}</td>
                                        <td>{!! Str::limit(html_entity_decode($item->description), 50) !!} </td>
                                        <td>{{ $item->amount_min != null ? $item->amount_min : 'Non défini' }}</td>
                                        <td>{{ $item->etablissement->name }}</td>
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
                        <p>Aucune catégorie enregistrer !'</p>
                @endif
            </div>
        </div>
    </div>
</div>



@include('layouts.footer')