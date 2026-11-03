{{-- Dernier appel d'un contact, tous agents confondus. Parametres : $lastCall (objet ou null), $historyUrl. --}}
@if(!empty($lastCall))
    @php
        $lastCallAt = \Carbon\Carbon::parse($lastCall->created_at);
    @endphp
    <a href="{{ $historyUrl }}" class="cc-last-call" title="Voir l'historique des appels">
        <span class="badge badge-{{ \App\Support\CallCenterOutcomes::tone($lastCall->resultat) }}"><span class="cc-dot"></span>{{ \App\Support\CallCenterOutcomes::label('resultat', $lastCall->resultat) }}</span>
        <span class="cc-last-call-meta">
            par <strong>{{ $lastCall->agent }}</strong>
            · <span title="{{ $lastCallAt->format('d/m/Y H:i') }}">{{ $lastCallAt->isToday() ? "aujourd'hui " . $lastCallAt->format('H:i') : $lastCallAt->format('d/m/Y H:i') }}</span>
            @if($lastCall->total > 1)
                · {{ $lastCall->total }} appels
            @endif
        </span>
    </a>
@endif
