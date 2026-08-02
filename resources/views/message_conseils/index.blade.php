@include('layouts.header')
@include('layouts.menu')
@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has('message'))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Programmer</h4>
                    <span class="badge badge-info">{{ $previewCount }} cible(s)</span>
                </div>

                <form method="GET" action="{{ route('message-conseils.index') }}" class="mb-3">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Recherche</label>
                            <input type="text" name="filters[keyword]" class="form-control" value="{{ $previewFilters['keyword'] ?? '' }}" placeholder="Nom, mobile, email">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ville</label>
                            <select name="filters[ville_id]" class="form-control" {{ $availableColumns['ville_id'] ? '' : 'disabled' }}>
                                <option value="">Toutes</option>
                                @foreach($villes as $ville)
                                    <option value="{{ $ville->id }}" {{ ($previewFilters['ville_id'] ?? '') == $ville->id ? 'selected' : '' }}>{{ $ville->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Commune</label>
                            <select name="filters[commune_id]" class="form-control" {{ $availableColumns['commune_id'] ? '' : 'disabled' }}>
                                <option value="">Toutes</option>
                                @foreach($communes as $commune)
                                    <option value="{{ $commune->id }}" {{ ($previewFilters['commune_id'] ?? '') == $commune->id ? 'selected' : '' }}>{{ $commune->libelle ?? $commune->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Quartier</label>
                            <input type="text" name="filters[quartier]" class="form-control" value="{{ $previewFilters['quartier'] ?? '' }}" placeholder="Ex: Cocody" {{ $availableColumns['quartier'] ? '' : 'disabled' }}>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Statut</label>
                            <select name="filters[statut]" class="form-control" {{ $availableColumns['statut'] ? '' : 'disabled' }}>
                                <option value="">Tous</option>
                                <option value="1" {{ ($previewFilters['statut'] ?? '') === '1' ? 'selected' : '' }}>Actifs</option>
                                <option value="0" {{ ($previewFilters['statut'] ?? '') === '0' ? 'selected' : '' }}>Inactifs</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-block">Prévisualiser la cible</button>
                </form>

                <form method="POST" action="{{ route('message-conseils.store') }}">
                    @csrf
                    @foreach($previewFilters as $key => $value)
                        <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                    @endforeach

                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255" placeholder="Ex: Conseil sécurité pluie">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="body" class="form-control" rows="4" required maxlength="1000" placeholder="Rédigez le conseil à envoyer aux usagers ciblés">{{ old('body') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Image URL</label>
                        <input type="url" name="image_url" class="form-control" value="{{ old('image_url') }}" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label>Lien d'action</label>
                        <input type="url" name="action_url" class="form-control" value="{{ old('action_url') }}" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label>Date d'envoi</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                    </div>
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary flex-fill mr-2">Programmer</button>
                        <button type="submit" name="send_now" value="1" class="btn btn-success flex-fill">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Messages conseils</h4>
                    <span class="text-muted">{{ $messages->total() }} élément(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Message</th>
                                <th>Programmation</th>
                                <th>Cibles</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $message)
                                <tr>
                                    <td style="min-width: 260px;">
                                        <strong>{{ $message->title }}</strong>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($message->body, 120) }}</div>
                                        @if($message->last_error)
                                            <div class="small text-danger mt-1">{{ \Illuminate\Support\Str::limit($message->last_error, 120) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ optional($message->scheduled_at)->format('d/m/Y H:i') ?? '-' }}</div>
                                        <small class="text-muted">Créé {{ optional($message->created_at)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $message->total_targets }}</span>
                                        <span class="badge badge-success">{{ $message->success_count }} ok</span>
                                        <span class="badge badge-danger">{{ $message->failure_count }} ko</span>
                                    </td>
                                    <td>
                                        @php
                                            $classes = [
                                                'draft' => 'badge-secondary',
                                                'scheduled' => 'badge-warning',
                                                'sending' => 'badge-primary',
                                                'sent' => 'badge-success',
                                                'failed' => 'badge-danger',
                                                'cancelled' => 'badge-dark',
                                            ];
                                            $labels = [
                                                'draft' => 'Brouillon',
                                                'scheduled' => 'Programmé',
                                                'sending' => 'Envoi',
                                                'sent' => 'Envoyé',
                                                'failed' => 'Échec',
                                                'cancelled' => 'Annulé',
                                            ];
                                        @endphp
                                        <span class="badge {{ $classes[$message->status] ?? 'badge-secondary' }}">{{ $labels[$message->status] ?? $message->status }}</span>
                                    </td>
                                    <td style="min-width: 170px;">
                                        @if(in_array($message->status, ['draft', 'scheduled', 'failed', 'cancelled']))
                                            <form method="POST" action="{{ route('message-conseils.send-now', $message->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Envoyer</button>
                                            </form>
                                        @endif
                                        @if($message->status === 'scheduled')
                                            <form method="POST" action="{{ route('message-conseils.cancel', $message->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">Annuler</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('message-conseils.destroy', $message->id) }}" class="d-inline" onsubmit="return confirm('Supprimer ce message conseil ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Suppr.</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Aucun message conseil programmé pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
