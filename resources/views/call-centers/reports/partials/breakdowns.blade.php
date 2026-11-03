{{-- Repartitions des appels. Parametres : $breakdowns = [titre => ['data' => [cle => n], 'group' => 'resultat'|'motif'|'cible'|'canal']]. --}}
@php
    $fmt = $fmt ?? fn ($n) => number_format((int) $n, 0, ',', ' ');
@endphp
        <div class="cc-r-two">
            @foreach($breakdowns as $title => $breakdown)
                <div class="cc-card">
                    <div class="cc-card-header"><div><h5 class="cc-card-title">{{ $title }}</h5></div></div>
                    @if(empty($breakdown['data']))
                        <p class="cc-muted mb-0">Aucun appel sur la periode.</p>
                    @else
                        @php $max = max(1, max($breakdown['data'])); $sum = max(1, array_sum($breakdown['data'])); @endphp
                        <div class="cc-r-hbars">
                            @foreach($breakdown['data'] as $key => $count)
                                <div>
                                    <div class="cc-r-hbar-head"><span>{{ \App\Support\CallCenterOutcomes::label($breakdown['group'], $key) }}</span><strong>{{ $fmt($count) }} <span class="cc-r-sub">· {{ round($count * 100 / $sum) }} %</span></strong></div>
                                    <div class="cc-r-hbar-track"><div class="cc-r-hbar-fill" style="width: {{ round($count * 100 / $max, 1) }}%"></div></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
