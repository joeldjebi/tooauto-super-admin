@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

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
