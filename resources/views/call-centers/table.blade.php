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
    @endphp

    <div class="cc-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <p class="text-muted mb-0">Liste filtrable des donnees disponibles.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(!empty($backUrl))
                    <a href="{{ $backUrl }}" class="btn btn-outline-secondary mr-2">{{ $backLabel ?? 'Retour' }}</a>
                @endif
                <span class="badge badge-light">{{ $items->total() }} element(s)</span>
            </div>
        </div>

        @if($standardFilters->isNotEmpty() || $dateFilters->isNotEmpty())
            <form method="GET" class="mb-4">
                @if($standardFilters->isNotEmpty())
                    <div class="row">
                        @foreach ($standardFilters as $filter)
                            <div class="col-xl-3 col-md-6 mb-3">
                                <label class="form-label d-block">{{ $filter['label'] }}</label>

                                @if (($filter['type'] ?? 'text') === 'select')
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
                                        class="form-control"
                                        value="{{ $filter['value'] ?? '' }}"
                                        placeholder="{{ $filter['placeholder'] ?? '' }}"
                                    >
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($dateFilters->isNotEmpty())
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Filtrer par date</strong>
                            <small class="text-muted">Date de creation</small>
                        </div>
                        <div class="row">
                            @if($dateFromFilter)
                                <div class="col-xl-3 col-md-6 mb-2">
                                    <label class="form-label d-block">{{ $dateFromFilter['label'] }}</label>
                                    <input
                                        type="date"
                                        name="{{ $dateFromFilter['name'] }}"
                                        class="form-control"
                                        value="{{ $dateFromFilter['value'] ?? '' }}"
                                    >
                                </div>
                            @endif

                            @if($dateToFilter)
                                <div class="col-xl-3 col-md-6 mb-2">
                                    <label class="form-label d-block">{{ $dateToFilter['label'] }}</label>
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

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    @if($hasActiveFilters)
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reinitialiser</a>
                    @endif
                </div>
            </form>
        @endif

        @if($hasDateColumn)
            <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-3">
                <span>Colonne date activee sur cette liste.</span>
                @if(request()->filled('date_from') || request()->filled('date_to'))
                    <span class="badge badge-primary">
                        {{ request('date_from', '...') }} au {{ request('date_to', '...') }}
                    </span>
                @endif
            </div>
        @endif

        @if($items->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            @foreach ($columns as $key => $label)
                                <th>{{ $key === 'date_creation' ? 'Date' : $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
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
                                    <td>
                                        @if ($key === 'actions' && ($menu ?? '') === 'call-center-etablissements')
                                            @php
                                                $etablissementId = $item->row_id ?? null;
                                                $hasCallFollowUp = property_exists($item, 'call_center_deja_appele') && property_exists($item, 'call_center_commentaire');
                                                $dejaAppele = (string) ($item->call_center_deja_appele ?? '0') === '1';
                                                $nextStatus = $dejaAppele ? '0' : '1';
                                                $statusLabel = $dejaAppele ? 'Marquer non appele' : 'Marquer appele';
                                                $statusClass = $dejaAppele ? 'btn-outline-secondary' : 'btn-outline-success';
                                                $badgeLabel = $dejaAppele ? 'Deja appele' : 'Non appele';
                                                $badgeClass = $dejaAppele ? 'badge-success' : 'badge-light';
                                                $commentBlockId = 'cc-comment-' . $etablissementId;
                                            @endphp

                                            @if($etablissementId)
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a class="btn btn-sm btn-outline-primary mr-1 mb-1" href="{{ route('call-center.etablissements.articles', $etablissementId) }}">Articles</a>
                                                    <a class="btn btn-sm btn-outline-warning mr-1 mb-1" href="{{ route('call-center.etablissements.promotions', $etablissementId) }}">Promotions</a>
                                                    <a class="btn btn-sm btn-outline-success mr-1 mb-1" href="{{ route('call-center.etablissements.abonnements', $etablissementId) }}">Abonnements</a>
                                                </div>

                                                @if($hasCallFollowUp)
                                                    <div class="mb-1">
                                                        <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <form action="{{ route('call-center.etablissements.suivi-appel', $etablissementId) }}" method="POST" class="d-inline mr-1 mb-1">
                                                            @csrf
                                                            <input type="hidden" name="call_center_deja_appele" value="{{ $nextStatus }}">
                                                            <button type="submit" class="btn btn-sm {{ $statusClass }}">{{ $statusLabel }}</button>
                                                        </form>

                                                        <button type="button" class="btn btn-sm btn-outline-info mb-1" onclick="document.getElementById('{{ $commentBlockId }}').classList.toggle('d-none')">
                                                            Commentaire
                                                        </button>
                                                    </div>

                                                    <div id="{{ $commentBlockId }}" class="d-none mt-2" style="min-width: 240px;">
                                                        <form action="{{ route('call-center.etablissements.suivi-appel', $etablissementId) }}" method="POST">
                                                            @csrf
                                                            <textarea name="call_center_commentaire" class="form-control form-control-sm mb-2" rows="3" placeholder="Commentaire">{{ $item->call_center_commentaire }}</textarea>
                                                            <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        @elseif ($key === 'actions' && $value)
                                            {!! $value !!}
                                        @else
                                            {{ $value === null || $value === '' ? '-' : \Illuminate\Support\Str::limit((string) $value, 80) }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $items->links() }}
            </div>
        @else
            <div class="alert alert-light border mb-0">
                Aucune donnee disponible pour cette liste avec les filtres actuels.
            </div>
        @endif
    </div>
@endsection
