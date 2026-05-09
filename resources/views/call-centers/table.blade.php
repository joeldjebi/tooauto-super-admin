@extends('call-centers.layout')

@section('content')
    <div class="cc-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">{{ $title }}</h5>
                <p class="text-muted mb-0">Liste filtrable en lecture seule.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(!empty($backUrl))
                    <a href="{{ $backUrl }}" class="btn btn-outline-secondary mr-2">{{ $backLabel ?? 'Retour' }}</a>
                @endif
                <span class="badge badge-light">{{ $items->total() }} element(s)</span>
            </div>
        </div>

        @if(!empty($filters))
            <form method="GET" class="mb-4">
                <div class="row">
                    @foreach ($filters as $filter)
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
                                    type="text"
                                    name="{{ $filter['name'] }}"
                                    class="form-control"
                                    value="{{ $filter['value'] ?? '' }}"
                                    placeholder="{{ $filter['placeholder'] ?? '' }}"
                                >
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    @if($hasActiveFilters)
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reinitialiser</a>
                    @endif
                </div>
            </form>
        @endif

        @if($items->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            @foreach ($columns as $label)
                                <th>{{ $label }}</th>
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
                                    @endphp
                                    <td>
                                        @if ($key === 'actions' && $value)
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
