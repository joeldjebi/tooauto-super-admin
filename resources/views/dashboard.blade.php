@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<style>
   .dashboard-section {
      margin-bottom: 1.5rem;
   }

   .dashboard-section-header {
      align-items: center;
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
   }

   .dashboard-section-title {
      color: #1f2937;
      font-size: 1rem;
      font-weight: 700;
      margin: 0;
   }

   .dashboard-section-subtitle {
      color: #64748b;
      font-size: .82rem;
      margin: .15rem 0 0;
   }

   .stat-table-card {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
      height: 100%;
   }

   .stat-table-card .card-body {
      padding: 1.1rem;
   }

   .stat-table-header {
      align-items: center;
      border-bottom: 1px solid #edf2f7;
      display: flex;
      justify-content: space-between;
      margin-bottom: .75rem;
      padding-bottom: .75rem;
   }

   .stat-table-title {
      color: #334155;
      font-size: .95rem;
      font-weight: 700;
      margin: 0;
   }

   .stat-pill {
      background: #eef2ff;
      border-radius: 999px;
      color: #3730a3;
      display: inline-block;
      font-size: .75rem;
      font-weight: 700;
      padding: .22rem .55rem;
      white-space: nowrap;
   }

   .stat-table-wrap {
      max-height: 430px;
      overflow: auto;
   }

   .stat-table {
      color: #334155;
      font-size: .88rem;
   }

   .stat-table thead th {
      background: #f8fafc;
      border-bottom: 1px solid #e5e7eb;
      color: #64748b;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .04em;
      position: sticky;
      text-transform: uppercase;
      top: 0;
      z-index: 1;
   }

   .stat-table td {
      vertical-align: middle;
   }

   .stat-label {
      color: #1f2937;
      font-weight: 600;
   }

   .stat-count {
      color: #0f172a;
      font-weight: 800;
      white-space: nowrap;
   }

   .stat-progress {
      background: #e5e7eb;
      border-radius: 999px;
      height: 6px;
      margin-top: .4rem;
      overflow: hidden;
      width: 100%;
   }

   .stat-progress-bar {
      background: #2563eb;
      border-radius: inherit;
      height: 100%;
      min-width: 4px;
   }

   .matrix-table-wrap {
      max-height: 560px;
   }

   .matrix-commune {
      background: #f8fafc;
      border-right: 1px solid #e5e7eb;
      color: #111827;
      min-width: 170px;
   }

   .matrix-type {
      min-width: 240px;
   }

   @media (max-width: 767.98px) {
      .dashboard-section-header,
      .stat-table-header {
         align-items: flex-start;
         flex-direction: column;
         gap: .5rem;
      }
   }
</style>

@php
   $stats = [
      [
         'label' => 'Usagers',
         'value' => $usagerCount ?? 0,
         'icon' => 'i-Add-User',
      ],
      [
         'label' => 'Etablissements',
         'value' => $etablissementCount ?? 0,
         'icon' => 'i-Administrator',
      ],
      [
         'label' => 'Prefectures',
         'value' => $prefectureCount ?? 0,
         'icon' => 'i-Police',
      ],
      [
         'label' => 'Alertes',
         'value' => $alertCount ?? 0,
         'icon' => 'i-Bell',
      ],
      [
         'label' => 'Annonces',
         'value' => $annonceCount ?? 0,
         'icon' => 'i-Internet',
      ],
      [
         'label' => 'Abonnements usagers',
         'value' => $abonnementUsagerCount ?? 0,
         'icon' => 'i-Financial',
      ],
      [
         'label' => 'Abonnements pro',
         'value' => $abonnementProCount ?? 0,
         'icon' => 'i-Money-2',
      ],
   ];

   $fproStats = [
      [
         'label' => 'Entretien',
         'value' => $entretienCount ?? 0,
         'icon' => 'i-Gear',
      ],
      [
         'label' => 'Assistance',
         'value' => $assistanceCount ?? 0,
         'icon' => 'i-Support',
      ],
      [
         'label' => 'Réparations & Suivi',
         'value' => $reparationSuiviCount ?? 0,
         'icon' => 'i-Settings-Window',
      ],
      [
         'label' => 'Carburant & Conso.',
         'value' => $carburantConsoCount ?? 0,
         'icon' => 'i-Drop',
      ],
      [
         'label' => 'Lavage véhicules',
         'value' => $lavageVehiculeCount ?? 0,
         'icon' => 'i-Car',
      ],
   ];

   $communeTypeGroups = collect($etablissementsByCommuneAndType ?? [])->groupBy('commune');
   $maxCommuneTotal = max(1, collect($etablissementsByCommune ?? [])->max('total') ?? 1);
   $maxTypeTotal = max(1, collect($etablissementsByType ?? [])->max('total') ?? 1);
   $maxCommuneTypeTotal = max(1, collect($etablissementsByCommuneAndType ?? [])->max('total') ?? 1);
@endphp

      <div class="row">
         @foreach($stats as $stat)
            <div class="col-lg-3 col-md-6 col-sm-6">
               <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                  <div class="card-body text-center">
                     <i class="{{ $stat['icon'] }}"></i>
                     <div class="content">
                        <p class="text-muted mt-2 mb-0">{{ $stat['label'] }}</p>
                        <p class="text-primary text-24 line-height-1 mb-2">{{ number_format($stat['value'], 0, ',', ' ') }}</p>
                     </div>
                  </div>
               </div>
            </div>
         @endforeach
      </div>

      <div class="row">
         <div class="col-md-12">
            <div class="card-title mb-3">Services F-PRO</div>
         </div>
         @foreach($fproStats as $stat)
            <div class="col-xl col-lg-3 col-md-4 col-sm-6">
               <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                  <div class="card-body text-center">
                     <i class="{{ $stat['icon'] }}"></i>
                     <div class="content">
                        <p class="text-muted mt-2 mb-0">{{ $stat['label'] }}</p>
                        <p class="text-primary text-24 line-height-1 mb-2">{{ number_format($stat['value'], 0, ',', ' ') }}</p>
                     </div>
                  </div>
               </div>
            </div>
         @endforeach
      </div>

      <div class="dashboard-section">
         <div class="dashboard-section-header">
            <div>
               <h5 class="dashboard-section-title">Statistiques des établissements</h5>
               <p class="dashboard-section-subtitle">Répartition par zone, type d'activité, puis croisement commune/type.</p>
            </div>
            <span class="stat-pill">{{ number_format(($etablissementCount ?? 0), 0, ',', ' ') }} établissement(s)</span>
         </div>

         <div class="row">
            <div class="col-lg-6 col-md-12">
               <div class="card stat-table-card mb-4">
                  <div class="card-body">
                     <div class="stat-table-header">
                        <h6 class="stat-table-title">Par commune</h6>
                        <span class="stat-pill">{{ collect($etablissementsByCommune ?? [])->count() }} commune(s)</span>
                     </div>
                     <div class="table-responsive stat-table-wrap">
                        <table class="table table-hover stat-table mb-0">
                           <thead>
                              <tr>
                                 <th>Commune</th>
                                 <th class="text-right">Total</th>
                              </tr>
                           </thead>
                           <tbody>
                              @forelse($etablissementsByCommune ?? [] as $stat)
                                 <tr>
                                    <td>
                                       <div class="stat-label">{{ $stat->commune }}</div>
                                       <div class="stat-progress">
                                          <div class="stat-progress-bar" style="width: {{ max(4, ($stat->total / $maxCommuneTotal) * 100) }}%;"></div>
                                       </div>
                                    </td>
                                    <td class="text-right stat-count">{{ number_format($stat->total, 0, ',', ' ') }}</td>
                                 </tr>
                              @empty
                                 <tr>
                                    <td colspan="2" class="text-center text-muted">Aucune donnée disponible</td>
                                 </tr>
                              @endforelse
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-lg-6 col-md-12">
               <div class="card stat-table-card mb-4">
                  <div class="card-body">
                     <div class="stat-table-header">
                        <h6 class="stat-table-title">Par type d'établissement</h6>
                        <span class="stat-pill">{{ collect($etablissementsByType ?? [])->count() }} type(s)</span>
                     </div>
                     <div class="table-responsive stat-table-wrap">
                        <table class="table table-hover stat-table mb-0">
                           <thead>
                              <tr>
                                 <th>Type</th>
                                 <th class="text-right">Total</th>
                              </tr>
                           </thead>
                           <tbody>
                              @forelse($etablissementsByType ?? [] as $stat)
                                 <tr>
                                    <td>
                                       <div class="stat-label">{{ $stat->type_etablissement }}</div>
                                       <div class="stat-progress">
                                          <div class="stat-progress-bar" style="width: {{ max(4, ($stat->total / $maxTypeTotal) * 100) }}%;"></div>
                                       </div>
                                    </td>
                                    <td class="text-right stat-count">{{ number_format($stat->total, 0, ',', ' ') }}</td>
                                 </tr>
                              @empty
                                 <tr>
                                    <td colspan="2" class="text-center text-muted">Aucune donnée disponible</td>
                                 </tr>
                              @endforelse
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="dashboard-section">
         <div class="card stat-table-card mb-4">
            <div class="card-body">
               <div class="stat-table-header">
                  <div>
                     <h6 class="stat-table-title">Par commune et par type</h6>
                     <p class="dashboard-section-subtitle">Chaque commune affiche ses types d'établissement, classés par volume.</p>
                  </div>
                  <span class="stat-pill">{{ collect($etablissementsByCommuneAndType ?? [])->count() }} combinaison(s)</span>
               </div>
               <div class="table-responsive stat-table-wrap matrix-table-wrap">
                  <table class="table table-hover stat-table mb-0">
                     <thead>
                        <tr>
                           <th>Commune</th>
                           <th>Type d'établissement</th>
                           <th class="text-right">Total</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse($communeTypeGroups as $commune => $statsByType)
                           @foreach($statsByType as $index => $stat)
                              <tr>
                                 @if($index === 0)
                                    <td rowspan="{{ $statsByType->count() }}" class="align-middle font-weight-bold matrix-commune">
                                       {{ $commune }}
                                    </td>
                                 @endif
                                 <td class="matrix-type">
                                    <div class="stat-label">{{ $stat->type_etablissement }}</div>
                                    <div class="stat-progress">
                                       <div class="stat-progress-bar" style="width: {{ max(4, ($stat->total / $maxCommuneTypeTotal) * 100) }}%;"></div>
                                    </div>
                                 </td>
                                 <td class="text-right stat-count">{{ number_format($stat->total, 0, ',', ' ') }}</td>
                              </tr>
                           @endforeach
                        @empty
                           <tr>
                              <td colspan="3" class="text-center text-muted">Aucune donnée disponible</td>
                           </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <!--<div class="row">
         <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
               <div class="card-body">
                  <div class="card-title">Les abonnements de cette année</div>
                  <div id="echartBar" style="height: 300px"></div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-12">
            <div class="card mb-4">
               <div class="card-body">
                  <div class="card-title">Ventes par pays</div>
                  <div id="echartPie" style="height: 300px"></div>
               </div>
            </div>
         </div>
      </div>-->


@include('layouts.footer')
