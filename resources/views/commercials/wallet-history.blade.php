@include('layouts.header')
@include('layouts.menu')
@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif

        <div class="card text-left mb-3">
            <div class="card-body">
                <h4 class="card-title mb-2">Historique wallet</h4>
                <p class="mb-1">
                    Commercial: <strong>{{ $commercial->nom }} {{ $commercial->prenoms }}</strong> | {{ $commercial->mobile }}
                </p>
                <p class="mb-0">
                    Solde actuel: <strong>{{ number_format(optional($commercial->wallet)->balance ?? 0, 0, ',', ' ') }} FCFA</strong>
                </p>
            </div>
        </div>

        <div class="card text-left">
            <div class="card-body">
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Sens</th>
                                    <th>Montant</th>
                                    <th>Solde avant</th>
                                    <th>Solde après</th>
                                    <th>Usager</th>
                                    <th>Forfait</th>
                                    <th>Référence</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ $transaction->type }}</td>
                                        <td>{{ $transaction->direction === 'credit' ? 'Crédit' : 'Débit' }}</td>
                                        <td>{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ number_format($transaction->balance_before, 0, ',', ' ') }} FCFA</td>
                                        <td>{{ number_format($transaction->balance_after, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            @if($transaction->user)
                                                {{ trim(($transaction->user->nom ?? '') . ' ' . ($transaction->user->prenoms ?? '')) ?: 'Usager #' . $transaction->user_id }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ optional($transaction->forfaitUsager)->libelle ?? '-' }}</td>
                                        <td>{{ $transaction->reference ?: '-' }}</td>
                                        <td>{{ $transaction->description ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                        <p class="mb-2 mb-md-0">
                            Affichage de {{ $transactions->firstItem() }} à {{ $transactions->lastItem() }} sur {{ $transactions->total() }} transactions
                        </p>
                        <div>{{ $transactions->links() }}</div>
                    </div>
                @else
                    <p>Aucune transaction wallet pour ce commercial.</p>
                @endif

                <a href="{{ route('index-commercial') }}" class="btn btn-secondary mt-3">Retour aux commerciaux</a>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')