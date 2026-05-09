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
                <h4 class="card-title mb-3">Les usagers parrainés par les stations services</h4>
                @if($getStationsServices->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nom usager</th>
                                    <th scope="col">Prénoms usager</th>
                                    <th scope="col">Contact usager</th>
									<th scope="col">Station service</th>
                                    <th scope="col">Contact Station</th>
                                    <th scope="col">Situation géographique</th>
                                    <th scope="col">Adresse Google Map</th>
                                    <th scope="col">Date de parrainage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($getStationsServices as $key => $item)
                                    
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->nom ?? "" }}</td>
                                        <td>{{ $item->prenoms ?? "" }}</td>
                                        <td>{{ $item->mobile ?? "" }}</td>
                                        <td>{{ $item->station_service->name }}</td>
                                        <td>{{ $item->station_service->mobile }}</td>
                                        <td>{{ $item->station_service->adresse }}</td>
                                        <td><a target="_blank" href="{{ $item->station_service->adresse_map }}">
												GOOGLE MAP
											</a>
										</td>
										<td>{{ $item->created_at }}</td>
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