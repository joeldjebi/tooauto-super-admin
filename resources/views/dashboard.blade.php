@include('layouts.header')
@include('layouts.menu')

<style>
   .apx {
      --apx-card: #ffffff;
      --apx-radius: 18px;
      --apx-shadow: 0 1px 2px rgba(0, 0, 0, .04), 0 8px 24px rgba(0, 0, 0, .04);
      color: var(--apx-text);
      font-family: var(--apx-font);
      letter-spacing: -.01em;
      padding-bottom: 2rem;
   }

   .apx *,
   .apx *::before,
   .apx *::after {
      box-sizing: border-box;
   }

   /* ---------- Hero ---------- */
   .apx-hero {
      align-items: flex-end;
      display: flex;
      flex-wrap: wrap;
      gap: .75rem;
      justify-content: space-between;
      margin: .25rem 0 1.5rem;
   }

   .apx-eyebrow {
      color: var(--apx-subtle);
      font-size: .78rem;
      font-weight: 600;
      margin: 0 0 .25rem;
      text-transform: capitalize;
   }

   .apx-title {
      font-size: 1.75rem;
      font-weight: 700;
      letter-spacing: -.03em;
      line-height: 1.15;
      margin: 0;
   }

   .apx-chip {
      align-items: center;
      background: #fff;
      border: 1px solid var(--apx-line);
      border-radius: 999px;
      color: var(--apx-muted);
      display: inline-flex;
      font-size: .78rem;
      font-weight: 500;
      gap: .5rem;
      padding: .4rem .85rem;
   }

   .apx-dot {
      background: #34c759;
      border-radius: 50%;
      box-shadow: 0 0 0 3px rgba(52, 199, 89, .18);
      height: 7px;
      width: 7px;
   }

   /* ---------- Sections ---------- */
   .apx-section {
      margin-bottom: 1.75rem;
   }

   .apx-section-head {
      align-items: flex-end;
      display: flex;
      flex-wrap: wrap;
      gap: .75rem;
      justify-content: space-between;
      margin-bottom: .75rem;
   }

   .apx-h2 {
      font-size: 1.15rem;
      font-weight: 700;
      letter-spacing: -.02em;
      margin: 0;
   }

   .apx-h3 {
      font-size: .95rem;
      font-weight: 600;
      letter-spacing: -.01em;
      margin: 0;
   }

   .apx-sub {
      color: var(--apx-muted);
      font-size: .82rem;
      margin: .15rem 0 0;
   }

   .apx-tag {
      background: var(--apx-fill);
      border-radius: 999px;
      color: var(--apx-muted);
      font-size: .72rem;
      font-weight: 600;
      padding: .25rem .65rem;
      white-space: nowrap;
   }

   .apx-card {
      background: var(--apx-card);
      border-radius: var(--apx-radius);
      box-shadow: var(--apx-shadow);
   }

   /* ---------- Bandeau de chiffres ---------- */
   .apx-strip {
      overflow: hidden;
   }

   .apx-strip-label {
      border-bottom: 1px solid var(--apx-line);
      color: var(--apx-subtle);
      font-size: .7rem;
      font-weight: 600;
      letter-spacing: .06em;
      padding: .65rem 1.1rem;
      text-transform: uppercase;
   }

   .apx-strip-grid {
      display: grid;
      grid-template-columns: repeat(var(--cols, 7), minmax(0, 1fr));
      margin: 0 -1px -1px 0;
   }

   .apx-cell {
      border-bottom: 1px solid var(--apx-line);
      border-right: 1px solid var(--apx-line);
      color: inherit;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: .9rem 1.1rem 1rem;
      text-decoration: none;
      transition: background .15s ease;
   }

   a.apx-cell:hover {
      background: #fafafc;
      color: inherit;
      text-decoration: none;
   }

   .apx-cell-top {
      align-items: center;
      display: flex;
      gap: .5rem;
      margin-bottom: .45rem;
   }

   .apx-cell-icon {
      align-items: center;
      background: color-mix(in srgb, var(--tone) 12%, transparent);
      border-radius: 8px;
      color: var(--tone);
      display: inline-flex;
      flex: none;
      font-size: .9rem;
      height: 26px;
      justify-content: center;
      width: 26px;
   }

   .apx-cell-label {
      color: var(--apx-muted);
      font-size: .78rem;
      font-weight: 500;
      line-height: 1.2;
   }

   .apx-cell-value {
      font-size: 1.5rem;
      font-variant-numeric: tabular-nums;
      font-weight: 700;
      letter-spacing: -.03em;
      line-height: 1.1;
      margin: 0;
   }

   .apx-cell-go {
      color: var(--apx-subtle);
      font-size: .75rem;
      margin-left: auto;
      opacity: 0;
      transition: opacity .15s ease;
   }

   a.apx-cell:hover .apx-cell-go {
      color: var(--apx-blue);
      opacity: 1;
   }

   /* ---------- Barres horizontales ---------- */
   .apx-grid-2 {
      display: grid;
      gap: 1rem;
      grid-template-columns: repeat(2, minmax(0, 1fr));
   }

   .apx,
   .apx-section,
   .apx-panel {
      min-width: 0;
   }

   .apx-panel {
      padding: 1.1rem 1.25rem 1rem;
   }

   .apx-panel-head {
      align-items: flex-start;
      display: flex;
      gap: .75rem;
      justify-content: space-between;
      margin-bottom: .9rem;
   }

   .apx-bars-head,
   .apx-bar-row {
      align-items: center;
      display: grid;
      gap: .75rem;
      grid-template-columns: minmax(90px, 150px) 1fr 48px 48px;
   }

   .apx-bars-head {
      border-bottom: 1px solid var(--apx-line);
      color: var(--apx-subtle);
      font-size: .68rem;
      font-weight: 600;
      letter-spacing: .05em;
      padding-bottom: .4rem;
      text-transform: uppercase;
   }

   .apx-bar-row {
      border-radius: 8px;
      margin: 0 -.4rem;
      padding: .42rem .4rem;
      transition: background .12s ease;
   }

   .apx-bar-row:hover {
      background: var(--apx-fill);
   }

   .apx-bar-row.is-extra {
      display: none;
   }

   .apx-bars.is-expanded .apx-bar-row.is-extra {
      display: grid;
   }

   .apx-bar-label {
      font-size: .85rem;
      font-weight: 500;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
   }

   .apx-bar-track {
      height: 10px;
      position: relative;
   }

   .apx-bar-fill {
      background: var(--tone, var(--apx-blue));
      border-radius: 0 4px 4px 0;
      display: block;
      height: 100%;
      min-width: 3px;
   }

   .apx-num {
      font-size: .85rem;
      font-variant-numeric: tabular-nums;
      font-weight: 600;
      text-align: right;
   }

   .apx-pct {
      color: var(--apx-subtle);
      font-size: .78rem;
      font-variant-numeric: tabular-nums;
      text-align: right;
   }

   .apx-more {
      background: transparent;
      border: 0;
      border-radius: 8px;
      color: var(--apx-blue);
      cursor: pointer;
      font-size: .82rem;
      font-weight: 500;
      margin-top: .5rem;
      padding: .35rem .5rem;
   }

   .apx-more:hover {
      background: rgba(0, 113, 227, .08);
   }

   .apx-empty {
      color: var(--apx-subtle);
      font-size: .875rem;
      padding: 1.5rem 0;
      text-align: center;
   }

   /* ---------- Heatmap ---------- */
   .apx-tools {
      align-items: center;
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
   }

   .apx-legend {
      align-items: center;
      color: var(--apx-subtle);
      display: inline-flex;
      font-size: .72rem;
      gap: .45rem;
   }

   .apx-legend-ramp {
      background: linear-gradient(90deg, rgba(0, 113, 227, .08), rgba(0, 113, 227, .92));
      border-radius: 4px;
      display: inline-block;
      height: 8px;
      width: 90px;
   }

   .apx-search {
      align-items: center;
      background: var(--apx-fill);
      border-radius: 10px;
      display: flex;
      gap: .45rem;
      margin: 0;
      padding: 0 .7rem;
      width: 220px;
   }

   .apx-search i {
      color: var(--apx-subtle);
   }

   .apx-search input {
      background: transparent;
      border: 0;
      color: var(--apx-text);
      font-size: .85rem;
      height: 34px;
      outline: none;
      width: 100%;
   }

   .apx-heat-wrap {
      border: 1px solid var(--apx-line);
      border-radius: 12px;
      overflow-x: auto;
   }

   .apx-heat {
      border-collapse: separate;
      border-spacing: 0;
      font-size: .82rem;
      min-width: 100%;
      width: max-content;
   }

   .apx-heat th,
   .apx-heat td {
      border-bottom: 1px solid var(--apx-line);
      padding: 0;
   }

   .apx-heat thead th {
      background: #fafafc;
      color: var(--apx-muted);
      font-size: .7rem;
      font-weight: 600;
      line-height: 1.25;
      max-width: 96px;
      min-width: 76px;
      padding: .6rem .4rem;
      text-align: center;
      vertical-align: bottom;
      white-space: normal;
   }

   .apx-heat .apx-heat-name {
      background: #fff;
      font-weight: 600;
      left: 0;
      min-width: 150px;
      padding: .5rem .9rem;
      position: sticky;
      text-align: left;
      white-space: nowrap;
      z-index: 1;
   }

   .apx-heat thead .apx-heat-name {
      background: #fafafc;
      z-index: 2;
   }

   .apx-heat td.apx-heat-cell {
      padding: 3px;
      text-align: center;
   }

   .apx-heat-cell span {
      align-items: center;
      border-radius: 6px;
      display: flex;
      font-variant-numeric: tabular-nums;
      font-weight: 600;
      height: 30px;
      justify-content: center;
   }

   .apx-heat-cell span.is-zero {
      color: #c7c7cc;
      font-weight: 400;
   }

   .apx-heat-cell span.is-dark {
      color: #fff;
   }

   .apx-heat .apx-heat-total {
      background: #fafafc;
      border-left: 1px solid var(--apx-line);
      font-variant-numeric: tabular-nums;
      font-weight: 700;
      min-width: 70px;
      padding: .5rem .9rem;
      text-align: right;
   }

   .apx-heat tbody tr:hover td {
      background-color: #f7f9fc;
   }

   .apx-heat tbody tr:hover .apx-heat-name {
      color: var(--apx-blue);
   }

   .apx-heat tfoot td {
      background: #fafafc;
      border-bottom: 0;
      color: var(--apx-muted);
      font-variant-numeric: tabular-nums;
      font-weight: 700;
      padding: .55rem .4rem;
      text-align: center;
   }

   .apx-heat tfoot .apx-heat-name,
   .apx-heat tfoot .apx-heat-total {
      background: #fafafc;
      color: var(--apx-text);
   }

   /* ---------- Responsive ---------- */
   @media (max-width: 1199.98px) {
      .apx-strip-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
      .apx-strip-grid.is-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
   }

   @media (max-width: 991.98px) {
      .apx-grid-2 { grid-template-columns: 1fr; }
      .apx-strip-grid.is-5 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
   }

   @media (max-width: 575.98px) {
      .apx-strip-grid,
      .apx-strip-grid.is-5 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .apx-cell { padding: .75rem .85rem .85rem; }
      .apx-cell-value { font-size: 1.3rem; }
      .apx-title { font-size: 1.45rem; }
      .apx-bars-head,
      .apx-bar-row { grid-template-columns: minmax(80px, 110px) 1fr 40px 42px; gap: .5rem; }
      .apx-search { width: 100%; }
   }
</style>

@php
   $routeOrNull = fn ($name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : null;
   $fmt = fn ($value) => number_format((int) $value, 0, ',', ' ');
   $pct = fn ($value, $total) => number_format($value / max(1, $total) * 100, 1, ',', ' ') . ' %';

   $stats = [
      ['label' => 'Usagers', 'value' => $usagerCount ?? 0, 'icon' => 'i-Add-User', 'tone' => '#0071e3', 'url' => $routeOrNull('index-usagers')],
      ['label' => 'Établissements', 'value' => $etablissementCount ?? 0, 'icon' => 'i-Administrator', 'tone' => '#5856d6', 'url' => $routeOrNull('index-etablissements')],
      ['label' => 'Abonnements usagers', 'value' => $abonnementUsagerCount ?? 0, 'icon' => 'i-Financial', 'tone' => '#34c759', 'url' => $routeOrNull('abonnement-usager')],
      ['label' => 'Abonnements pro', 'value' => $abonnementProCount ?? 0, 'icon' => 'i-Money-2', 'tone' => '#30b0c7', 'url' => $routeOrNull('abonnement-pro')],
      ['label' => 'Annonces', 'value' => $annonceCount ?? 0, 'icon' => 'i-Internet', 'tone' => '#ff9500', 'url' => $routeOrNull('index-annonce')],
      ['label' => 'Alertes', 'value' => $alertCount ?? 0, 'icon' => 'i-Bell', 'tone' => '#ff3b30', 'url' => $routeOrNull('index-alerte')],
      ['label' => 'Préfectures', 'value' => $prefectureCount ?? 0, 'icon' => 'i-Police', 'tone' => '#af52de', 'url' => $routeOrNull('index-prefecture')],
   ];

   $fproStats = [
      ['label' => 'Entretien', 'value' => $entretienCount ?? 0, 'icon' => 'i-Gear', 'tone' => '#0071e3'],
      ['label' => 'Assistance', 'value' => $assistanceCount ?? 0, 'icon' => 'i-Support', 'tone' => '#ff9500'],
      ['label' => 'Réparations & suivi', 'value' => $reparationSuiviCount ?? 0, 'icon' => 'i-Settings-Window', 'tone' => '#5856d6'],
      ['label' => 'Carburant & conso.', 'value' => $carburantConsoCount ?? 0, 'icon' => 'i-Drop', 'tone' => '#ff3b30', 'url' => $routeOrNull('index-station_service')],
      ['label' => 'Lavage véhicules', 'value' => $lavageVehiculeCount ?? 0, 'icon' => 'i-Car', 'tone' => '#30b0c7', 'url' => $routeOrNull('demande-lavages.index')],
   ];

   $byCommune = collect($etablissementsByCommune ?? [])->values();
   $byType = collect($etablissementsByType ?? [])->values();
   $byCommuneAndType = collect($etablissementsByCommuneAndType ?? []);

   $barCharts = [
      [
         'title' => 'Par commune',
         'subtitle' => 'Où sont implantés les établissements.',
         'column' => 'Commune',
         'key' => 'commune',
         'rows' => $byCommune,
         'unit' => 'communes',
         'tone' => '#0071e3',
      ],
      [
         'title' => 'Par type d\'établissement',
         'subtitle' => 'Quelles activités sont les plus représentées.',
         'column' => 'Type',
         'key' => 'type_etablissement',
         'rows' => $byType,
         'unit' => 'types',
         'tone' => '#5856d6',
      ],
   ];
   $visibleBars = 8;

   // Matrice commune x type (lignes et colonnes triées par volume)
   $heatTypes = $byCommuneAndType->groupBy('type_etablissement')->map->sum('total')->sortDesc();
   $heatCommunes = $byCommuneAndType->groupBy('commune')->map->sum('total')->sortDesc();
   $heatMatrix = [];
   foreach ($byCommuneAndType as $row) {
      $heatMatrix[$row->commune][$row->type_etablissement] = (int) $row->total;
   }
   $heatMax = max(1, $byCommuneAndType->max('total') ?? 1);
   $heatGrandTotal = $heatCommunes->sum();

   $now = \Carbon\Carbon::now()->locale('fr');
   $greeting = (int) $now->format('H') < 18 ? 'Bonjour' : 'Bonsoir';
   $adminName = trim(($user->prenoms ?? '') ?: ($user->nom ?? ''));
@endphp

<div class="apx">

   {{-- Hero --}}
   <header class="apx-hero">
      <div>
         <p class="apx-eyebrow">{{ $now->isoFormat('dddd D MMMM YYYY') }}</p>
         <h1 class="apx-title">{{ $greeting }}{{ $adminName ? ', ' . $adminName : '' }}.</h1>
      </div>
      <span class="apx-chip"><span class="apx-dot"></span> Données à jour · {{ $now->format('H:i') }}</span>
   </header>

   {{-- Chiffres clés --}}
   <section class="apx-section">
      <div class="apx-card apx-strip">
         <div class="apx-strip-label">Plateforme</div>
         <div class="apx-strip-grid" style="--cols: {{ count($stats) }}">
            @foreach($stats as $stat)
               @php $url = $stat['url'] ?? null; $tag = $url ? 'a' : 'div'; @endphp
               <{{ $tag }} @if($url) href="{{ $url }}" @endif class="apx-cell" style="--tone: {{ $stat['tone'] }}">
                  <div class="apx-cell-top">
                     <span class="apx-cell-icon"><i class="{{ $stat['icon'] }}"></i></span>
                     <span class="apx-cell-label">{{ $stat['label'] }}</span>
                     @if($url)<span class="apx-cell-go">&rarr;</span>@endif
                  </div>
                  <p class="apx-cell-value">{{ $fmt($stat['value']) }}</p>
               </{{ $tag }}>
            @endforeach
         </div>
      </div>
   </section>

   <section class="apx-section">
      <div class="apx-card apx-strip">
         <div class="apx-strip-label">Services F-PRO</div>
         <div class="apx-strip-grid is-5" style="--cols: {{ count($fproStats) }}">
            @foreach($fproStats as $stat)
               @php $url = $stat['url'] ?? null; $tag = $url ? 'a' : 'div'; @endphp
               <{{ $tag }} @if($url) href="{{ $url }}" @endif class="apx-cell" style="--tone: {{ $stat['tone'] }}">
                  <div class="apx-cell-top">
                     <span class="apx-cell-icon"><i class="{{ $stat['icon'] }}"></i></span>
                     <span class="apx-cell-label">{{ $stat['label'] }}</span>
                     @if($url)<span class="apx-cell-go">&rarr;</span>@endif
                  </div>
                  <p class="apx-cell-value">{{ $fmt($stat['value']) }}</p>
               </{{ $tag }}>
            @endforeach
         </div>
      </div>
   </section>

   {{-- Établissements --}}
   <section class="apx-section">
      <div class="apx-section-head">
         <div>
            <h2 class="apx-h2">Établissements</h2>
            <p class="apx-sub">{{ $fmt($etablissementCount ?? 0) }} établissements au total, répartis par commune et par type.</p>
         </div>
      </div>

      <div class="apx-grid-2">
         @foreach($barCharts as $chart)
            @php
               $rows = $chart['rows'];
               $max = max(1, $rows->max('total') ?? 1);
               $sum = $rows->sum('total');
               $hidden = max(0, $rows->count() - $visibleBars);
            @endphp
            <div class="apx-card apx-panel" style="--tone: {{ $chart['tone'] }}">
               <div class="apx-panel-head">
                  <div>
                     <h3 class="apx-h3">{{ $chart['title'] }}</h3>
                     <p class="apx-sub">{{ $chart['subtitle'] }}</p>
                  </div>
                  <span class="apx-tag">{{ $rows->count() }} {{ $chart['unit'] }}</span>
               </div>

               @if($rows->isEmpty())
                  <p class="apx-empty">Aucune donnée disponible</p>
               @else
                  <div class="apx-bars">
                     <div class="apx-bars-head">
                        <span>{{ $chart['column'] }}</span>
                        <span></span>
                        <span class="text-right">Nombre</span>
                        <span class="text-right">Part</span>
                     </div>
                     @foreach($rows as $i => $row)
                        @php $label = $row->{$chart['key']}; @endphp
                        <div class="apx-bar-row {{ $i >= $visibleBars ? 'is-extra' : '' }}"
                           title="{{ $label }} : {{ $fmt($row->total) }} établissement(s), soit {{ $pct($row->total, $sum) }}">
                           <span class="apx-bar-label">{{ $label }}</span>
                           <span class="apx-bar-track">
                              <span class="apx-bar-fill" style="width: {{ $row->total / $max * 100 }}%"></span>
                           </span>
                           <span class="apx-num">{{ $fmt($row->total) }}</span>
                           <span class="apx-pct">{{ $pct($row->total, $sum) }}</span>
                        </div>
                     @endforeach
                  </div>
                  @if($hidden)
                     <button type="button" class="apx-more" data-apx-toggle
                        data-more="Afficher les {{ $hidden }} autres" data-less="Réduire">Afficher les {{ $hidden }} autres</button>
                  @endif
               @endif
            </div>
         @endforeach
      </div>
   </section>

   {{-- Commune x type --}}
   <section class="apx-section">
      <div class="apx-card apx-panel">
         <div class="apx-panel-head" style="flex-wrap: wrap">
            <div>
               <h3 class="apx-h3">Croisement commune × type</h3>
               <p class="apx-sub">Nombre d'établissements de chaque type dans chaque commune. Plus la case est foncée, plus il y en a.</p>
            </div>
            <div class="apx-tools">
               <span class="apx-legend">Peu <span class="apx-legend-ramp"></span> Beaucoup</span>
               <label class="apx-search">
                  <i class="i-Magnifi-Glass1"></i>
                  <input type="search" id="apx-heat-search" placeholder="Filtrer les communes" autocomplete="off">
               </label>
            </div>
         </div>

         @if($heatCommunes->isEmpty())
            <p class="apx-empty">Aucune donnée disponible</p>
         @else
            <div class="apx-heat-wrap">
               <table class="apx-heat">
                  <thead>
                     <tr>
                        <th class="apx-heat-name">Commune</th>
                        @foreach($heatTypes as $type => $typeTotal)
                           <th>{{ $type }}</th>
                        @endforeach
                        <th class="apx-heat-total">Total</th>
                     </tr>
                  </thead>
                  <tbody id="apx-heat-body">
                     @foreach($heatCommunes as $commune => $communeTotal)
                        <tr data-search="{{ \Illuminate\Support\Str::lower($commune) }}">
                           <td class="apx-heat-name">{{ $commune }}</td>
                           @foreach($heatTypes as $type => $typeTotal)
                              @php
                                 $value = $heatMatrix[$commune][$type] ?? 0;
                                 $ratio = $value / $heatMax;
                              @endphp
                              <td class="apx-heat-cell" title="{{ $commune }} · {{ $type }} : {{ $fmt($value) }}">
                                 @if($value > 0)
                                    <span class="{{ $ratio > .5 ? 'is-dark' : '' }}"
                                       style="background: rgba(0, 113, 227, {{ round(.08 + $ratio * .84, 3) }})">{{ $fmt($value) }}</span>
                                 @else
                                    <span class="is-zero">–</span>
                                 @endif
                              </td>
                           @endforeach
                           <td class="apx-heat-total">{{ $fmt($communeTotal) }}</td>
                        </tr>
                     @endforeach
                     <tr id="apx-heat-empty" style="display: none">
                        <td colspan="{{ $heatTypes->count() + 2 }}" class="apx-empty">Aucune commune ne correspond</td>
                     </tr>
                  </tbody>
                  <tfoot>
                     <tr>
                        <td class="apx-heat-name">Total</td>
                        @foreach($heatTypes as $typeTotal)
                           <td>{{ $fmt($typeTotal) }}</td>
                        @endforeach
                        <td class="apx-heat-total">{{ $fmt($heatGrandTotal) }}</td>
                     </tr>
                  </tfoot>
               </table>
            </div>
         @endif
      </div>
   </section>
</div>

<script>
   (function () {
      document.querySelectorAll('[data-apx-toggle]').forEach(function (btn) {
         btn.addEventListener('click', function () {
            var bars = btn.previousElementSibling;
            var expanded = bars.classList.toggle('is-expanded');
            btn.textContent = expanded ? btn.dataset.less : btn.dataset.more;
         });
      });

      var input = document.getElementById('apx-heat-search');
      if (!input) return;
      var rows = document.querySelectorAll('#apx-heat-body tr[data-search]');
      var empty = document.getElementById('apx-heat-empty');
      input.addEventListener('input', function () {
         var q = input.value.trim().toLowerCase();
         var visible = 0;
         rows.forEach(function (row) {
            var match = !q || row.dataset.search.indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
         });
         empty.style.display = visible ? 'none' : '';
      });
   })();
</script>

@include('layouts.footer')
