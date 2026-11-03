@php
    // $days : jours restants avant expiration (negatif = expiree). $date : date d'expiration (mode "date").
    $days = (int) $days;
    $tone = match (true) {
        $days < 0 => 'expired',
        $days === 0 => 'today',
        $days <= 7 => 'urgent',
        $days <= 30 => 'soon',
        default => 'ok',
    };
    $abs = abs($days);
    $duration = match (true) {
        $abs >= 365 => intdiv($abs, 365) . ' an' . ($abs >= 730 ? 's' : ''),
        $abs >= 60 => intdiv($abs, 30) . ' mois',
        default => $abs . ' j',
    };
    $label = match ($tone) {
        'expired' => 'Expirée depuis ' . $duration,
        'today' => "Expire aujourd'hui",
        'ok' => 'Valide · ' . $duration,
        default => 'Expire dans ' . $duration,
    };
@endphp
@if(($mode ?? 'badge') === 'date')
    <span class="cc-due-date cc-due-{{ $tone }}" title="{{ $label }}">
        @include('call-centers.partials.icon', ['name' => 'calendar'])
        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
    </span>
@else
    <span class="cc-due cc-due-{{ $tone }}"><span class="cc-dot"></span>{{ $label }}</span>
@endif
