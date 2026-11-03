{{-- Histogramme des appels par jour. Parametres : $daily, $total (nombre d'appels de la periode). --}}
        @php
            $maxDay = max(1, collect($daily)->max('total'));
            $labelEvery = max(1, (int) ceil(count($daily) / 15));
        @endphp
        <div class="cc-card mb-4">
            <div class="cc-card-header">
                <div>
                    <h5 class="cc-card-title">Appels par jour</h5>
                    <p class="cc-card-subtitle">Survolez une barre pour le détail (appels et personnes jointes).</p>
                </div>
            </div>
            @if(($total ?? 0) === 0)
                <div class="cc-empty-state">
                    @include('call-centers.partials.icon', ['name' => 'empty'])
                    <strong>Aucun appel sur la période</strong>
                    <span>Les appels enregistrés par les agents apparaîtront ici.</span>
                </div>
            @else
                <div class="cc-r-chart" role="img" aria-label="Histogramme des appels par jour, maximum {{ $maxDay }} appels">
                    <span class="cc-r-ymax">max {{ $maxDay }}</span>
                    <div class="cc-r-plot">
                        @foreach($daily as $day)
                            <div class="cc-r-col {{ $day['total'] === 0 ? 'is-empty' : '' }}" tabindex="0">
                                <div class="cc-r-bar" style="height: {{ round($day['total'] * 100 / $maxDay, 1) }}%"></div>
                                <div class="cc-r-tip"><strong>{{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}</strong><br>{{ $day['total'] }} appel(s) · {{ $day['joints'] }} joint(s)</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="cc-r-axis" aria-hidden="true">
                        @foreach($daily as $index => $day)
                            <span>{{ $index % $labelEvery === 0 ? $day['label'] : '' }}</span>
                        @endforeach
                    </div>
                </div>
                <details class="mt-3">
                    <summary class="cc-r-sub" style="cursor: pointer;">Voir les données en tableau</summary>
                    <div class="cc-table-wrap mt-2">
                        <table class="table cc-table cc-r-table">
                            <thead><tr><th>Jour</th><th class="cc-r-num">Appels</th><th class="cc-r-num">Joints</th></tr></thead>
                            <tbody>
                                @foreach(array_reverse($daily) as $day)
                                    @if($day['total'] > 0)
                                        <tr><td>{{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}</td><td class="cc-r-num">{{ $day['total'] }}</td><td class="cc-r-num">{{ $day['joints'] }}</td></tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </div>

