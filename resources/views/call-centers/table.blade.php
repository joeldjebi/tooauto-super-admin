@extends('call-centers.layout')

@section('content')
    @php
        $dateFilterNames = ['date_from', 'date_to'];
        $dateFilters = collect($filters ?? [])->whereIn('name', $dateFilterNames)->keyBy('name');
        $standardFilters = collect($filters ?? [])->reject(function ($filter) use ($dateFilterNames) {
            return in_array($filter['name'] ?? '', $dateFilterNames, true);
        });
        $dateFromFilter = $dateFilters->get('date_from');
        $dateToFilter = $dateFilters->get('date_to');
        $hasDateColumn = array_key_exists('date_creation', $columns ?? []);
        $activeFilterCount = collect($filters ?? [])->filter(fn ($filter) => filled($filter['value'] ?? null))->count();
    @endphp

    @if(!empty($contactUser))
        @php
            $contactName = trim(($contactUser->nom ?? '') . ' ' . ($contactUser->prenoms ?? '')) ?: 'Usager #' . $contactUser->id;
            $contactInitials = collect(preg_split('/\s+/', $contactName))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
            $contactCalled = (string) ($contactUser->call_center_deja_appele ?? '0') === '1';
            $contactHasFollowUp = property_exists($contactUser, 'call_center_deja_appele');
        @endphp
        <div class="cc-card cc-contact mb-4">
            <div class="cc-contact-id">
                <span class="cc-contact-avatar">{{ $contactInitials ?: '?' }}</span>
                <div style="min-width: 0;">
                    <h5 class="cc-card-title">{{ $contactName }}</h5>
                    <div class="cc-contact-meta">
                        @if(($contactUser->alertes_expirees ?? 0) > 0)
                            <span class="cc-due cc-due-expired"><span class="cc-dot"></span>{{ $contactUser->alertes_expirees }} alerte(s) expiree(s)</span>
                        @endif
                        @if($contactHasFollowUp)
                            <span class="badge {{ $contactCalled ? 'badge-success' : 'badge-light' }}"><span class="cc-dot"></span>{{ $contactCalled ? 'Déjà appelé' : 'Non appelé' }}</span>
                        @endif
                        @if(!empty($contactUser->email))
                            <a href="mailto:{{ $contactUser->email }}">{{ $contactUser->email }}</a>
                        @endif
                    </div>
                    @if(!empty($contactUser->last_call))
                        <div class="mt-2">
                            @include('call-centers.partials.last-call', [
                                'lastCall' => $contactUser->last_call,
                                'historyUrl' => route('call-center.interactions.history', ['users', $contactUser->id]),
                            ])
                        </div>
                    @endif
                    @if(filled($contactUser->call_center_commentaire ?? null))
                        <div class="cc-note-preview">{{ \Illuminate\Support\Str::limit($contactUser->call_center_commentaire, 160) }}</div>
                    @endif
                </div>
            </div>
            <div class="cc-contact-actions">
                @include('call-centers.partials.phone', ['mobile' => $contactUser->mobile ?? null, 'indicatif' => $contactUser->indicatif ?? null, 'whatsapp' => (string) ($contactUser->is_whatsapp ?? '0') === '1', 'size' => 'lg'])
                @if(Route::has('call-center.interactions.store'))
                    <button type="button" class="btn btn-sm btn-primary" data-cc-log="{{ json_encode(['cible_type' => 'users', 'cible_id' => (int) $contactUser->id, 'nom' => $contactName]) }}">
                        @include('call-centers.partials.icon', ['name' => 'phone'])
                        Enregistrer un appel
                    </button>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('call-center.interactions.history', ['users', $contactUser->id]) }}">Historique</a>
                @endif
                @if($contactHasFollowUp)
                    <form action="{{ route('call-center.users.suivi-appel', $contactUser->id) }}" method="POST" class="mb-0">
                        @csrf
                        <input type="hidden" name="call_center_deja_appele" value="{{ $contactCalled ? '0' : '1' }}">
                        <button type="submit" class="btn btn-sm {{ $contactCalled ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                            {{ $contactCalled ? 'Marquer non appelé' : 'Marquer appelé' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="cc-card">
        <div class="cc-card-header">
            <div>
                <h5 class="cc-card-title">Liste</h5>
                <p class="cc-card-subtitle">Filtrez et consultez les données disponibles.</p>
            </div>
            <div class="cc-header-actions">
                @if(!empty($backUrl))
                    <a href="{{ $backUrl }}" class="btn btn-plain">
                        @include('call-centers.partials.icon', ['name' => 'back'])
                        {{ $backLabel ?? 'Retour' }}
                    </a>
                @endif
                <span class="cc-count">{{ number_format($items->total(), 0, ',', ' ') }} element(s)</span>
                @if(!empty($perPageOptions) && $items->total() > min($perPageOptions))
                    <form method="GET" class="cc-perpage">
                        @foreach(request()->except(['page', 'per_page']) as $name => $value)
                            @if(!is_array($value))
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="cc-per-page">Afficher</label>
                        <select id="cc-per-page" name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
                            @foreach($perPageOptions as $option)
                                <option value="{{ $option }}" {{ (int) ($perPage ?? 15) === $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>

        @if($standardFilters->isNotEmpty() || $dateFilters->isNotEmpty())
            <form method="GET" class="cc-filter-panel">
                @if(request()->filled('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <div class="cc-filter-head">
                    <span>
                        Filtres
                        @if($activeFilterCount > 0)
                            <span class="cc-filter-count">{{ $activeFilterCount }} actif(s)</span>
                        @endif
                    </span>
                    @if($hasActiveFilters)
                        <a href="{{ url()->current() }}">Tout effacer</a>
                    @endif
                </div>

                @if($standardFilters->isNotEmpty())
                    <div class="cc-filter-grid">
                        @foreach ($standardFilters as $filter)
                            <div>
                                <label class="cc-field-label">{{ $filter['label'] }}</label>

                                @if (!empty($filter['remote']))
                                    <div class="cc-combo" data-cc-combo="{{ $filter['remote'] }}">
                                        <input type="hidden" name="{{ $filter['name'] }}" value="{{ $filter['value'] ?? '' }}">
                                        <input type="text" class="form-control cc-input-search" value="{{ $filter['selected_label'] ?? '' }}" placeholder="Rechercher..." autocomplete="off" aria-label="{{ $filter['label'] }}">
                                        <div class="cc-combo-list" role="listbox" hidden></div>
                                    </div>
                                @elseif (($filter['type'] ?? 'text') === 'select')
                                    <select name="{{ $filter['name'] }}" class="form-control">
                                        <option value="">Tous</option>
                                        @foreach (($filter['options'] ?? []) as $option)
                                            <option value="{{ $option['value'] }}" {{ (string) ($filter['value'] ?? '') === (string) $option['value'] ? 'selected' : '' }}>
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="{{ $filter['type'] ?? 'text' }}"
                                        name="{{ $filter['name'] }}"
                                        class="form-control {{ ($filter['type'] ?? 'text') === 'text' ? 'cc-input-search' : '' }}"
                                        value="{{ $filter['value'] ?? '' }}"
                                        placeholder="{{ $filter['placeholder'] ?? '' }}"
                                    >
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($dateFilters->isNotEmpty())
                    <div class="cc-filter-divider">
                        @include('call-centers.partials.icon', ['name' => 'calendar'])
                        Date de création
                    </div>
                    <div>
                        <div class="cc-filter-grid">
                            @if($dateFromFilter)
                                <div>
                                    <label class="cc-field-label">{{ $dateFromFilter['label'] }}</label>
                                    <input
                                        type="date"
                                        name="{{ $dateFromFilter['name'] }}"
                                        class="form-control"
                                        value="{{ $dateFromFilter['value'] ?? '' }}"
                                    >
                                </div>
                            @endif

                            @if($dateToFilter)
                                <div>
                                    <label class="cc-field-label">{{ $dateToFilter['label'] }}</label>
                                    <input
                                        type="date"
                                        name="{{ $dateToFilter['name'] }}"
                                        class="form-control"
                                        value="{{ $dateToFilter['value'] ?? '' }}"
                                    >
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="cc-filter-actions">
                    <button type="submit" class="btn btn-primary">
                        @include('call-centers.partials.icon', ['name' => 'search'])
                        Filtrer
                    </button>
                    @if($hasDateColumn && (request()->filled('date_from') || request()->filled('date_to')))
                        <span class="cc-filter-chip">
                            @include('call-centers.partials.icon', ['name' => 'calendar'])
                            {{ request('date_from', '...') }} au {{ request('date_to', '...') }}
                        </span>
                    @endif
                </div>
            </form>
        @endif

        @if($items->count() > 0)
            <div class="cc-table-wrap">
                <table class="table table-hover cc-table">
                    <thead>
                        <tr>
                            @foreach ($columns as $key => $label)
                                <th class="{{ $key === 'actions' ? 'cc-table-actions' : '' }}">{{ $key === 'date_creation' ? 'Date' : $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $dueDays = property_exists($item, 'echeance') && $item->echeance !== null ? (int) $item->echeance : null;
                                $dueRowClass = $dueDays === null ? '' : ($dueDays < 0 ? 'cc-row-expired' : ($dueDays <= 7 ? 'cc-row-urgent' : ''));
                            @endphp
                            <tr class="{{ $dueRowClass }}">
                                @foreach (array_keys($columns) as $key)
                                    @php
                                        $value = $item->{$key} ?? null;
                                        if (in_array($key, ['statut', 'is_visible'], true) && $value !== null && $value !== '') {
                                            $value = (string) $value === '1' ? 'Oui' : 'Non';
                                        }

                                        if (str_starts_with($key, 'date_') && $value !== null && $value !== '') {
                                            try {
                                                $value = \Carbon\Carbon::parse($value)->format(str_contains((string) $value, ':') ? 'd/m/Y H:i' : 'd/m/Y');
                                            } catch (\Throwable $exception) {
                                                $value = (string) $value;
                                            }
                                        }
                                    @endphp
                                    <td class="{{ $key === 'actions' ? 'cc-table-actions' : '' }} {{ in_array($key, ['echeance', 'date_fin', 'mobile'], true) ? 'cc-td-full' : '' }}" data-label="{{ $key === 'date_creation' ? 'Date' : ($columns[$key] ?? '') }}">
                                        @if ($key === 'echeance')
                                            @if($dueDays === null)
                                                <span class="cc-muted">&mdash;</span>
                                            @else
                                                @include('call-centers.partials.alert-due', ['days' => $dueDays])
                                            @endif
                                        @elseif ($key === 'date_fin' && $dueDays !== null && filled($item->date_fin ?? null))
                                            @include('call-centers.partials.alert-due', ['days' => $dueDays, 'mode' => 'date', 'date' => $item->date_fin])
                                        @elseif ($key === 'mobile' && filled($value))
                                            @include('call-centers.partials.phone', ['mobile' => $value, 'indicatif' => $item->indicatif ?? null, 'whatsapp' => (string) ($item->is_whatsapp ?? '0') === '1'])
                                        @elseif ($key === 'suivi_appel')
                                            @php
                                                $hasCallFollowUp = property_exists($item, 'call_center_deja_appele') && property_exists($item, 'call_center_commentaire');
                                                $dejaAppele = (string) ($item->call_center_deja_appele ?? '0') === '1';
                                                $callCommentaire = trim((string) ($item->call_center_commentaire ?? ''));
                                            @endphp

                                            @if($hasCallFollowUp)
                                                <div class="cc-follow-badges">
                                                    <span class="badge {{ $dejaAppele ? 'badge-success' : 'badge-light' }}">
                                                        <span class="cc-dot"></span>{{ $dejaAppele ? 'Déjà appelé' : 'Non appelé' }}
                                                    </span>
                                                    @if($callCommentaire !== '')
                                                        <span class="badge badge-info">Commenté</span>
                                                    @endif
                                                </div>
                                                @php
                                                    $rowCibleType = \App\Support\CallCenterOutcomes::MENU_CIBLES[$menu ?? ''] ?? null;
                                                @endphp
                                                @if($rowCibleType && !empty($item->last_call))
                                                    @include('call-centers.partials.last-call', [
                                                        'lastCall' => $item->last_call,
                                                        'historyUrl' => route('call-center.interactions.history', [$rowCibleType, $item->row_id]),
                                                    ])
                                                @endif
                                            @else
                                                <span class="cc-muted">&mdash;</span>
                                            @endif
                                        @elseif ($key === 'actions' && empty($relatedUserId) && in_array(($menu ?? ''), ['call-center-users', 'call-center-alertes', 'call-center-professionnels', 'call-center-etablissements', 'call-center-concessionnaires', 'call-center-station-services', 'call-center-station-de-lavages'], true))
                                            @php
                                                $entityId = $item->row_id ?? null;
                                                $routeName = [
                                                    'call-center-users' => 'call-center.users.suivi-appel',
                                                    // Sur la liste des alertes, row_id est l'id du user : le suivi d'appel porte sur lui.
                                                    'call-center-alertes' => 'call-center.users.suivi-appel',
                                                    'call-center-professionnels' => 'call-center.professionnels.suivi-appel',
                                                    'call-center-etablissements' => 'call-center.etablissements.suivi-appel',
                                                    'call-center-concessionnaires' => 'call-center.concessionnaires.suivi-appel',
                                                    'call-center-station-services' => 'call-center.station-services.suivi-appel',
                                                    'call-center-station-de-lavages' => 'call-center.station-de-lavages.suivi-appel',
                                                ][$menu] ?? null;
                                                $hasCallFollowUp = property_exists($item, 'call_center_deja_appele') && property_exists($item, 'call_center_commentaire');
                                                $dejaAppele = (string) ($item->call_center_deja_appele ?? '0') === '1';
                                                $callCommentaire = $hasCallFollowUp ? (string) ($item->call_center_commentaire ?? '') : '';
                                                $nextStatus = $dejaAppele ? '0' : '1';
                                                $statusLabel = $dejaAppele ? 'Marquer non appelé' : 'Marquer appelé';
                                                $statusClass = $dejaAppele ? 'btn-outline-secondary' : 'btn-outline-success';
                                                $badgeLabel = $dejaAppele ? 'Déjà appelé' : 'Non appelé';
                                                $badgeClass = $dejaAppele ? 'badge-success' : 'badge-light';
                                                $commentBlockId = 'cc-comment-' . ($menu ?? 'item') . '-' . $entityId;
                                                $logCibleType = \App\Support\CallCenterOutcomes::MENU_CIBLES[$menu] ?? null;
                                                $logNom = trim((string) ($item->utilisateur ?? $item->name ?? trim(($item->nom ?? '') . ' ' . ($item->prenoms ?? ''))));
                                                $logAlerte = ($item->alert_id ?? null)
                                                    ? trim(($item->type_alerte ?? 'Alerte') . (filled($item->vehicule ?? null) ? ' · ' . $item->vehicule : '') . (filled($item->date_fin ?? null) ? ' · fin ' . \Carbon\Carbon::parse($item->date_fin)->format('d/m/Y') : ''))
                                                    : null;
                                                $logPayload = $logCibleType ? [
                                                    'cible_type' => $logCibleType,
                                                    'cible_id' => (int) $entityId,
                                                    'alert_id' => $item->alert_id ?? null,
                                                    'nom' => $logNom !== '' ? $logNom : '#' . $entityId,
                                                    'alerte' => $logAlerte,
                                                ] : null;
                                            @endphp

                                            @if($entityId)
                                                <details class="cc-actions-dropdown">
                                                    <summary class="btn btn-sm btn-outline-primary cc-actions-toggle">
                                                        Actions
                                                        @include('call-centers.partials.icon', ['name' => 'chevron'])
                                                    </summary>
                                                    <div class="cc-actions-menu">
                                                        <div class="cc-menu-label">Consulter</div>
                                                        <div class="cc-action-links">
                                                            @if(($menu ?? '') === 'call-center-etablissements')
                                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('call-center.etablissements.articles', $entityId) }}">Articles</a>
                                                                <a class="btn btn-sm btn-outline-warning" href="{{ route('call-center.etablissements.promotions', $entityId) }}">Promotions</a>
                                                                <a class="btn btn-sm btn-outline-success" href="{{ route('call-center.etablissements.abonnements', $entityId) }}">Abonnements</a>
                                                            @elseif($value)
                                                                {!! $value !!}
                                                            @endif
                                                        </div>

                                                        @if($hasCallFollowUp)
                                                            <div class="cc-call-panel">
                                                                <div class="cc-call-panel-head">
                                                                    <span class="cc-menu-label mb-0">Suivi d'appel</span>
                                                                    <span class="badge {{ $badgeClass }}"><span class="cc-dot"></span>{{ $badgeLabel }}</span>
                                                                </div>

                                                                @if($logPayload && Route::has('call-center.interactions.store'))
                                                                    <div class="cc-action-buttons mb-2">
                                                                        <button type="button" class="btn btn-sm btn-primary" data-cc-log="{{ json_encode($logPayload) }}">
                                                                            @include('call-centers.partials.icon', ['name' => 'phone'])
                                                                            Enregistrer un appel
                                                                        </button>
                                                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('call-center.interactions.history', [$logCibleType, $entityId]) }}">Historique</a>
                                                                    </div>
                                                                @endif

                                                                <div class="cc-action-buttons">
                                                                    <form action="{{ route($routeName, $entityId) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        <input type="hidden" name="call_center_deja_appele" value="{{ $nextStatus }}">
                                                                        <button type="submit" class="btn btn-sm {{ $statusClass }}">{{ $statusLabel }}</button>
                                                                    </form>

                                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="document.getElementById('{{ $commentBlockId }}').classList.toggle('d-none')">
                                                                        Noter / commenter
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            @if($callCommentaire !== '')
                                                                <div class="cc-note-preview">
                                                                    {{ \Illuminate\Support\Str::limit($callCommentaire, 80) }}
                                                                </div>
                                                            @endif

                                                            <div id="{{ $commentBlockId }}" class="d-none cc-note-editor">
                                                                <form action="{{ route($routeName, $entityId) }}" method="POST">
                                                                    @csrf
                                                                    <textarea name="call_center_commentaire" class="form-control form-control-sm mb-2" rows="3" style="resize: vertical;" placeholder="Note ou commentaire d'appel">{{ $callCommentaire }}</textarea>
                                                                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <div class="alert alert-danger mt-3 mb-0 py-2">Migration requise.</div>
                                                        @endif
                                                    </div>
                                                </details>
                                            @else
                                                <span class="cc-muted">&mdash;</span>
                                            @endif
                                        @elseif ($key === 'actions' && $value)
                                            {!! $value !!}
                                        @else
                                            @if($value === null || $value === '')
                                                <span class="cc-muted">&mdash;</span>
                                            @else
                                                {{ \Illuminate\Support\Str::limit((string) $value, 80) }}
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $items->links('call-centers.partials.pagination') }}
            </div>
        @else
            <div class="cc-empty-state">
                @include('call-centers.partials.icon', ['name' => 'empty'])
                <strong>Aucun résultat</strong>
                <span>Aucune donnée disponible pour cette liste avec les filtres actuels.</span>
                @if($hasActiveFilters ?? false)
                    <a href="{{ url()->current() }}" class="btn btn-outline-primary mt-2">Réinitialiser les filtres</a>
                @endif
            </div>
        @endif
    </div>
@endsection
