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
                    <h4 class="card-title mb-0">Programmér</h4>
                    <span class="badge badge-info">{{ $previewCount }} cible(s)</span>
                </div>

                <form method="GET" action="{{ route('message-conseils.index') }}" class="mb-3" data-message-filter-form>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Recherche</label>
                            <input type="text" name="filters[keyword]" class="form-control" value="{{ $previewFilters['keyword'] ?? '' }}" placeholder="Nom, mobile, email">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ville</label>
                            <select name="filters[ville_id]" class="form-control js-ville-select">
                                <option value="">Toutes</option>
                                @foreach($villes as $ville)
                                    <option value="{{ $ville->id }}" {{ ($previewFilters['ville_id'] ?? '') == $ville->id ? 'selected' : '' }}>{{ $ville->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Commune</label>
                            <select name="filters[commune_id]" class="form-control js-commune-select">
                                <option value="">Toutes</option>
                                @foreach($communes as $commune)
                                    <option value="{{ $commune->id }}" data-ville-id="{{ $commune->ville_id }}" {{ ($previewFilters['commune_id'] ?? '') == $commune->id ? 'selected' : '' }}>{{ $commune->libelle ?? $commune->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Statut</label>
                            <select name="filters[statut]" class="form-control" {{ ($availableColumns['statut'] ?? false) ? '' : 'disabled' }}>
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
                        <button type="submit" class="btn btn-primary flex-fill mr-2">Programmér</button>
                        <button type="submit" name="send_now" value="1" class="btn btn-success flex-fill">Envoyér</button>
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
                    <div>
                        <a href="{{ route('message-conseils.logs') }}" class="btn btn-outline-info btn-sm mr-2">Logs</a>
                        <span class="text-muted">{{ $messages->total() }} élément(s)</span>
                    </div>
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
                                    <td style="min-width: 270px;">
                                        <a href="{{ route('message-conseils.logs', ['message_conseil_id' => $message->id]) }}" class="btn btn-sm btn-outline-info">Logs</a>
                                        @if($message->status !== 'sending')
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editMessageConseil{{ $message->id }}">Modifier</button>
                                        @endif
                                        @if(in_array($message->status, ['draft', 'scheduled', 'failed', 'cancelled']))
                                            <form method="POST" action="{{ route('message-conseils.send-now', $message->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Envoyér</button>
                                            </form>
                                        @endif
                                        @if($message->status === 'scheduled')
                                            <form method="POST" action="{{ route('message-conseils.cancel', $message->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">Annulér</button>
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

@foreach($messages as $message)
    @php($messageFilters = $message->filters ?? [])
    <div class="modal fade" id="editMessageConseil{{ $message->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('message-conseils.update', $message->id) }}" data-message-filter-form>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier et reprogrammer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Titre</label>
                                <input type="text" name="title" class="form-control" value="{{ $message->title }}" required maxlength="255">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Message</label>
                                <textarea name="body" class="form-control" rows="4" required maxlength="1000">{{ $message->body }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Image URL</label>
                                <input type="url" name="image_url" class="form-control" value="{{ $message->image_url }}" placeholder="https://...">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Lien d'action</label>
                                <input type="url" name="action_url" class="form-control" value="{{ $message->action_url }}" placeholder="https://...">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nouvelle date d'envoi</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ optional($message->scheduled_at)->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Recherche cible</label>
                                <input type="text" name="filters[keyword]" class="form-control" value="{{ $messageFilters['keyword'] ?? '' }}" placeholder="Nom, mobile, email">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Ville</label>
                                <select name="filters[ville_id]" class="form-control js-ville-select">
                                    <option value="">Toutes</option>
                                    @foreach($villes as $ville)
                                        <option value="{{ $ville->id }}" {{ ($messageFilters['ville_id'] ?? '') == $ville->id ? 'selected' : '' }}>{{ $ville->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Commune</label>
                                <select name="filters[commune_id]" class="form-control js-commune-select">
                                    <option value="">Toutes</option>
                                    @foreach($communes as $commune)
                                        <option value="{{ $commune->id }}" data-ville-id="{{ $commune->ville_id }}" {{ ($messageFilters['commune_id'] ?? '') == $commune->id ? 'selected' : '' }}>{{ $commune->libelle ?? $commune->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Statut</label>
                                <select name="filters[statut]" class="form-control" {{ ($availableColumns['statut'] ?? false) ? '' : 'disabled' }}>
                                    <option value="">Tous</option>
                                    <option value="1" {{ ($messageFilters['statut'] ?? '') === '1' ? 'selected' : '' }}>Actifs</option>
                                    <option value="0" {{ ($messageFilters['statut'] ?? '') === '0' ? 'selected' : '' }}>Inactifs</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
    (function () {
        function syncCommunes(form) {
            var villeSelect = form.querySelector('.js-ville-select');
            var communeSelect = form.querySelector('.js-commune-select');

            if (!villeSelect || !communeSelect) {
                return;
            }

            var villeId = villeSelect.value;
            var selectedOptionHidden = false;

            Array.prototype.forEach.call(communeSelect.options, function (option) {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                var visible = !villeId || option.getAttribute('data-ville-id') === villeId;
                option.hidden = !visible;

                if (option.selected && !visible) {
                    selectedOptionHidden = true;
                }
            });

            if (selectedOptionHidden) {
                communeSelect.value = '';
            }
        }

        Array.prototype.forEach.call(document.querySelectorAll('[data-message-filter-form]'), function (form) {
            var villeSelect = form.querySelector('.js-ville-select');
            syncCommunes(form);

            if (villeSelect) {
                villeSelect.addEventListener('change', function () {
                    syncCommunes(form);
                });
            }
        });
    })();
</script>
@include('layouts.footer')
