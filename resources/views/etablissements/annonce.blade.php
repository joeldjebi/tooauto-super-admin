@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Annonces de l'établissement</h4>
                        <p class="mb-0 text-muted">{{ $etablissement->name }}</p>
                    </div>
                    <a href="{{ route('show-etablissement', $etablissement->id) }}" class="btn btn-outline-primary">Retour au détail</a>
                </div>

                @if($annonces->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Libellé</th>
                                    <th>Marque</th>
                                    <th>Utilisateur</th>
                                    <th>Type de pièce</th>
                                    <th>Visible</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($annonces as $key => $annonce)
                                    <tr>
                                        <td>{{ $annonces->firstItem() + $key }}</td>
                                        <td style="width: 80px;">
                                            @if($annonce->image_url)
                                                <img src="{{ $annonce->image_url }}" alt="{{ $annonce->libelle ?? 'Annonce' }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $annonce->libelle ?? 'Annonce #' . $annonce->id }}</strong>
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($annonce->description ?? ''), 100) ?: 'Aucune description' }}</div>
                                        </td>
                                        <td>{{ optional($annonce->marque)->libelle ?? 'Non définie' }}</td>
                                        <td>{{ optional($annonce->currentUser)->name ?? optional($annonce->currentUser)->nom ?? 'Non défini' }}</td>
                                        <td>{{ optional($annonce->type_de_piece)->libelle ?? 'Non définie' }}</td>
                                        <td>{{ (int) ($annonce->pivot->is_visible ?? 0) === 1 ? 'Oui' : 'Non' }}</td>
                                        <td>{{ optional($annonce->created_at)->format('d/m/Y H:i') ?: 'Non définie' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                        <p class="mb-2 mb-md-0">
                            Affichage de {{ $annonces->firstItem() }} à {{ $annonces->lastItem() }} sur {{ $annonces->total() }} annonces
                        </p>
                        <div>{{ $annonces->links() }}</div>
                    </div>
                @else
                    <p class="mb-0 text-muted">Aucune annonce trouvée pour cet établissement.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
