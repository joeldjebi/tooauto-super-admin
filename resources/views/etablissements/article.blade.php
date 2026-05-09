@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Articles de l'établissement</h4>
                        <p class="mb-0 text-muted">{{ $etablissement->name }}</p>
                    </div>
                    <a href="{{ route('show-etablissement', $etablissement->id) }}" class="btn btn-outline-primary">Retour au détail</a>
                </div>

                @if($articles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Libellé</th>
                                    <th>Description</th>
                                    <th>Prix</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($articles as $key => $article)
                                    <tr>
                                        <td>{{ $articles->firstItem() + $key }}</td>
                                        <td style="width: 80px;">
                                            @if($article->image_url)
                                                <img src="{{ $article->image_url }}" alt="{{ $article->libelle }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $article->libelle }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 120) }}</td>
                                        <td>{{ $article->amount ? number_format((float) $article->amount, 0, ',', ' ') . ' FCFA' : 'Non défini' }}</td>
                                        <td>{{ optional($article->created_at)->format('d/m/Y H:i') ?: 'Non définie' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                        <p class="mb-2 mb-md-0">
                            Affichage de {{ $articles->firstItem() }} à {{ $articles->lastItem() }} sur {{ $articles->total() }} articles
                        </p>
                        <div>{{ $articles->links() }}</div>
                    </div>
                @else
                    <p class="mb-0 text-muted">Aucun article trouvé pour cet établissement.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
