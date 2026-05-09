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
                <h4 class="card-title mb-3">Les abonnements</h4>
                @if($abonnementPros->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Prestataire</th>
                                    <th scope="col">Forfait</th>
                                    <th scope="col">Prix</th>
                                    <th scope="col">Durée en mois</th>
                                    <th scope="col">Debut de l'abonnement</th>
                                    <th scope="col">Fin de l'abonnement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($abonnementPros as $key => $item)
                                @php
                                    $montant = $item->forfait ? $item->forfait->prix : 0;
                                    $montant_formate = number_format($montant, 0, ',', ' '); // Ajoute un espace comme séparateur de milliers
                                    $montant_final = $montant_formate . ' FCFA'; // Ajoute la devise
                                @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->etablissement->name ?? "" }}</td>
                                        <td>{{ $item->forfait ? $item->forfait->nom : "" }}</td>
                                        <td>{{ $montant_final ?? "" }}</td>
                                        <td>{{ $item->forfait ? $item->forfait->duree : "" }}</td>
                                        <td>{{ $item->date_debut ?? "" }}</td>
                                        <td>{{ $item->date_fin ?? "" }}</td>
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