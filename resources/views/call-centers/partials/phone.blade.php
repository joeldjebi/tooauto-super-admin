@php
    // $mobile, $indicatif (optionnel), $whatsapp (optionnel), $size ('sm' | 'lg')
    $localDigits = preg_replace('/\D+/', '', (string) ($mobile ?? ''));
    $dialCode = preg_replace('/\D+/', '', (string) ($indicatif ?? ''));
    // Certains numeros sont deja saisis avec l'indicatif : on ne le double pas.
    $alreadyPrefixed = $dialCode !== '' && str_starts_with($localDigits, $dialCode) && strlen($localDigits) > 10;
    $international = $dialCode !== '' && ! $alreadyPrefixed ? $dialCode . $localDigits : $localDigits;
    $nationalPart = $alreadyPrefixed ? substr($localDigits, strlen($dialCode)) : $localDigits;
    $display = trim(($dialCode !== '' ? '+' . $dialCode . ' ' : '') . trim(chunk_split($nationalPart, 2, ' ')));
    $telHref = 'tel:' . ($dialCode !== '' ? '+' : '') . $international;
@endphp
@if($localDigits !== '')
    <span class="cc-phone {{ ($size ?? 'sm') === 'lg' ? 'cc-phone-lg' : '' }}">
        <a href="{{ $telHref }}" class="cc-phone-call" title="Appeler {{ $display }}">
            @include('call-centers.partials.icon', ['name' => 'phone'])
            <span>{{ $display }}</span>
        </a>
        @if(! empty($whatsapp))
            <a href="https://wa.me/{{ $international }}" target="_blank" rel="noopener noreferrer" class="cc-phone-btn cc-phone-wa" title="Ouvrir WhatsApp" aria-label="WhatsApp">
                @include('call-centers.partials.icon', ['name' => 'whatsapp'])
            </a>
        @endif
        <button type="button" class="cc-phone-btn" data-cc-copy="{{ ($dialCode !== '' ? '+' : '') . $international }}" title="Copier le numéro" aria-label="Copier le numéro">
            @include('call-centers.partials.icon', ['name' => 'copy'])
        </button>
    </span>
@else
    <span class="cc-muted">&mdash;</span>
@endif
