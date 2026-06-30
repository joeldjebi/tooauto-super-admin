@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')


<div class="row">
    <div class="col-lg-12 col-md-12">
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#exampleModal">Ajouter un commercial</button>
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
        <div class="card text-left mb-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Configuration commission commerciaux</h4>
                <form action="{{ route('commercial.wallet.commission-setting') }}" method="post">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label>Type de commission</label>
                            <select name="type" class="form-control" required>
                                <option value="fixed" {{ ((optional($commissionSetting)->type ?? env('COMMERCIAL_COMMISSION_TYPE', 'fixed')) === 'fixed') ? 'selected' : '' }}>Montant fixe</option>
                                <option value="percentage" {{ ((optional($commissionSetting)->type ?? env('COMMERCIAL_COMMISSION_TYPE', 'fixed')) === 'percentage') ? 'selected' : '' }}>Pourcentage</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <label>Valeur</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="value" value="{{ optional($commissionSetting)->value ?? env('COMMERCIAL_COMMISSION_VALUE', 0) }}" required>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <button type="submit" class="btn btn-primary">Enregistrer la commission</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Les commerciaux</h4>
                @if($commercials->isNotEmpty() )
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénoms</th>
                                    <th scope="col">Mobile</th>
                                    <th scope="col">Code de parrainage</th>
                                    <th scope="col">Solde wallet</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Date de création</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commercials as $key => $item)
                                    <!-- Modal -->
                                    <div class="modal fade" id="id{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Enregistrer un commercial</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('commercial.update', ['id' => $item->id]) }}" method="post">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="mobile">Nom</label>
                                                                    <div class="input-group">
                                                                            <input type="text" class="form-control" name="nom" value="{{ $item->nom }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="mobile">Prénoms</label>
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control" name="prenoms" value="{{ $item->prenoms }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label for="mobile">Mobile</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" name="mobile" value="{{ $item->mobile }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary">Enregistrer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="walletPayout{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('commercial.wallet.payout', ['id' => $item->id]) }}" method="post">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reversement wallet</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Solde disponible: <strong>{{ number_format(optional($item->wallet)->balance ?? 0, 0, ',', ' ') }} FCFA</strong></p>
                                                        <div class="form-group">
                                                            <label>Montant</label>
                                                            <input type="number" min="1" step="0.01" max="{{ optional($item->wallet)->balance ?? 0 }}" name="amount" class="form-control" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Référence</label>
                                                            <input type="text" name="reference" class="form-control" placeholder="Ex: VIREMENT-001">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Description</label>
                                                            <textarea name="description" class="form-control" rows="3" placeholder="Note interne"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Valider le reversement</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->nom }}</td>
                                        <td>{{ $item->prenoms }}</td>
                                        <td>{{ $item->mobile }}</td>
                                        <td>{{ optional($item->parrain)->code ?? "Non attribue" }}</td>
                                        <td>{{ number_format(optional($item->wallet)->balance ?? 0, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ $item->statut == 1 ? 'Actif' : 'Inactif' }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="#" data-toggle="modal" data-target="#id{{ $item->id }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('commercial.destroy', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" id="delete">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form> 
                                            {{-- pour activer --}}
                                            @if ($item->statut == 0)
                                                <form action="{{ route('commercial.activer', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">
                                                        Activer
                                                    </button>
                                                </form> 
                                            @endif
                                            {{-- pour desactiver --}}
                                            @if ($item->statut == 1)
                                                <form action="{{ route('commercial.desactiver', ['id' => $item->id]) }}" method="POST"  style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">
                                                        Désactiver
                                                    </button>
                                                </form> 
                                            @endif
                                            <button type="button" class="btn btn-warning ml-1" data-toggle="modal" data-target="#walletPayout{{ $item->id }}">
                                                Reversement
                                            </button>
                                            <a href="{{ route('commercial.wallet.history', ['id' => $item->id]) }}" class="btn btn-secondary ml-1">
                                                Historique wallet
                                            </a>
                                            @if($item->parrain)
                                                <a href="{{ route('commercial.filleuls', ['code' => $item->parrain->code]) }}" class="btn btn-info ml-1">
                                                    Filleuls
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(isset($recentWalletTransactions) && $recentWalletTransactions->isNotEmpty())
                        <h5 class="mt-4">Historique wallet récent</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Commercial</th>
                                        <th>Type</th>
                                        <th>Sens</th>
                                        <th>Montant</th>
                                        <th>Solde après</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentWalletTransactions as $transaction)
                                        <tr>
                                            <td>{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ optional($transaction->commercial)->nom }} {{ optional($transaction->commercial)->prenoms }}</td>
                                            <td>{{ $transaction->type }}</td>
                                            <td>{{ $transaction->direction }}</td>
                                            <td>{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA</td>
                                            <td>{{ number_format($transaction->balance_after, 0, ',', ' ') }} FCFA</td>
                                            <td>{{ $transaction->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @else
                        <p>Aucun commercial enregistrer !</p>
                @endif
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Enregistrer un commercial</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{ route('commercial.store') }}" method="post">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="mobile">Nom</label>
                        <div class="input-group">
                                <input type="text" class="form-control" name="nom">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="mobile">Prénoms</label>
                        <div class="input-group">
                                <input type="text" class="form-control" name="prenoms">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label>Numéro de téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text">+225</span>
                            <input type="text" class="form-control" name="mobile" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
              </div>
          </form>
        </div>
        
      </div>
    </div>
</div>


@include('layouts.footer')