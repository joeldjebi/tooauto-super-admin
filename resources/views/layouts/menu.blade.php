<div class="side-content-wrap">
   <div
      class="sidebar-left open rtl-ps-none"
      data-perfect-scrollbar=""
      data-suppress-scroll-x="true"
      >
      <ul class="navigation-left">
         <li class="nav-item {{ $menu == "dashboard" ? 'active' : '' }}">
           <a class="nav-item-hold" href="{{ route('dashboard') }}" >
              <i class="nav-icon i-Bar-Chart"></i ><span class="nav-text">Dashboard</span>
           </a>
            <div class="triangle"></div>
         </li>
         @if(auth()->user()?->isSuperAdmin())
         <li class="nav-item {{ $menu == "admins" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('admins.index') }}">
               <i class="nav-icon i-Checked-User"></i>
               <span class="nav-text">Admins</span>
            </a>
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "call-centers" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('call-centers.index') }}">
               <i class="nav-icon i-Headset"></i>
               <span class="nav-text">Call center</span>
            </a>
            <div class="triangle"></div>
         </li>
         @endif
         <li class="nav-item" data-item="uikits">
            <a class="nav-item-hold" href="#"
               ><i class="nav-icon i-Library"></i
               ><span class="nav-text">Données</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "usager" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-usagers') }}"
               ><i class="nav-icon i-Suitcase"></i
               ><span class="nav-text">Usagers</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "etablissement" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-etablissements') }}"
               ><i class="nav-icon i-Administrator"></i
               ><span class="nav-text">Etablissements</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "professionnel" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-professionnels') }}"
               ><i class="nav-icon i-Business-ManWoman"></i
               ><span class="nav-text">Professionnels</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "service" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-services') }}"
               ><i class="nav-icon i-Computer-Secure"></i
               ><span class="nav-text">Prestations</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "articles" ? 'active' : '' }}" >
            <a class="nav-item-hold" href="{{ route('index-article') }}"
               ><i class="nav-icon i-File-Clipboard-File--Text"></i
               ><span class="nav-text">Articles</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "annonces" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-annonce') }}"
               ><i class="nav-icon i-File-Clipboard-File--Text"></i
               ><span class="nav-text">Annonces</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "vehicules" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-vehicule') }}"
               ><i class="nav-icon i-File-Horizontal-Text"></i
               ><span class="nav-text">Véhicules</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "alertes" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-alerte') }}"
               ><i class="nav-icon i-Administrator"></i
               ><span class="nav-text">Alertes</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item  {{ $menu == "abonnementpro" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('abonnement-pro') }}"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Aboonnements pro</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "abonnementusager" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('abonnement-usager') }}"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Abonnements usager</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "concessionnaires" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('concessionnaires.liste') }}"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Concessionnaires</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "promotions" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('promotions.index') }}"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Promotions</span></a
               >
            <div class="triangle"></div>
         </li>
         <li data-item="apps" class="nav-item">
            <a class="nav-item-hold" href="#"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Services publics</span></a
               >
            <div class="triangle"></div>
         </li>
         <li data-item="offres" class="nav-item">
            <a class="nav-item-hold" href="#"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Offres d'emploi</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "offre-recrutement" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('show-offres') }}"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Offres d'emploi recrutement</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "candidats-recrutement" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-candidats-recrutement') }}"
               ><i class="nav-icon i-Double-Tap"></i
               ><span class="nav-text">Candidats recrutement</span></a
               >
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "index-commercial" ? 'active' : '' }}">
              <a class="nav-item-hold" href="{{ route('index-commercial') }}">
                 <i class="nav-icon i-Safe-Box1"></i>
                 <span class="nav-text">Commerciaux</span>
              </a>
            <div class="triangle"></div>
         </li>
         <li class="nav-item {{ $menu == "qrcode-generate" ? 'active' : '' }}">
            <a class="nav-item-hold" href="{{ route('index-qrcode-generate') }}">
               <i class="nav-icon i-Double-Tap"></i>
               <span class="nav-text">QR codes générés</span>
            </a>
            <div class="triangle"></div>
         </li>
      </ul>
   </div>
   <div
      class="sidebar-left-secondary rtl-ps-none" data-perfect-scrollbar=""data-suppress-scroll-x="true">
      <!-- Submenu Dashboards-->


      <ul class="childNav" data-parent="apps">
         <li class="nav-item" {{ $menu == "prefectures" ? 'active' : '' }}>
            <a href="{{ route('index-prefecture') }}" >
              <i class="nav-icon i-Add-File">
              </i ><span class="item-name">Préfectures</span>
           </a>
         </li>
         <li class="nav-item {{ $menu == "sapeur_pompier" ? 'active' : '' }}">
            <a href="{{ route('index-sapeur_pompier') }}">
              <i class="nav-icon i-Email"></i>
              <span class="item-name">Sapeur pompier</span>
           </a>
         </li>
         <li class="nav-item">
            <a href="chat.html"
               ><i class="nav-icon i-Speach-Bubble-3"></i
               ><span class="item-name">Chat</span></a
               >
         </li>
      </ul>

      <ul class="childNav" data-parent="offres">
         <li class="nav-item">
            <a href="{{ route('index-offre') }}"
               ><i class="nav-icon i-File-Clipboard-Text--Image"></i
               ><span class="item-name">Liste des offres d'emploi</span></a
               >
         </li>
         <li class="nav-item">
            <a href="{{ route('index-type-offre') }}"
               ><i class="nav-icon i-File-Clipboard-Text--Image"></i
               ><span class="item-name">Types d'offres d'emploi</span></a
               >
         </li>
         <li class="nav-item">
            <a href="{{ route('index-type-contrat') }}"
               ><i class="nav-icon i-File-Clipboard-Text--Image"></i
               ><span class="item-name">Type de contrat</span></a
               >
         </li>
         <li class="nav-item">
            <a href="{{ route('index-candidat') }}"
               ><i class="nav-icon i-File-Clipboard-Text--Image"></i
               ><span class="item-name">Liste des candidats</span></a
               >
         </li>
      </ul>

      <!-- chartjs-->
      <ul class="childNav" data-parent="charts">
         <li class="nav-item">
            <a href="charts.echarts.html"
               ><i class="nav-icon i-File-Clipboard-Text--Image"></i
               ><span class="item-name">echarts</span></a
               >
         </li>
         <li class="nav-item">
            <a href="charts.chartsjs.html"
               ><i class="nav-icon i-File-Clipboard-Text--Image"></i
               ><span class="item-name">ChartJs</span></a
               >
         </li>
         <li class="nav-item dropdown-sidemenu">
            <a href=""
               ><i class="nav-icon i-File-Clipboard-Text--Image"></i
               ><span class="item-name">Apex Charts</span
               ><i class="dd-arrow i-Arrow-Down"></i
               ></a>
            <ul class="submenu">
               <li><a href="charts.apexAreaCharts.html">Area Charts</a></li>
               <li><a href="charts.apexBarCharts.html">Bar Charts</a></li>
               <li>
                  <a href="charts.apexBubbleCharts.html">Bubble Charts</a>
               </li>
               <li>
                  <a href="charts.apexColumnCharts.html">Column Charts</a>
               </li>
               <li>
                  <a href="charts.apexCandleStickCharts.html">CandleStick Charts</a>
               </li>
               <li><a href="charts.apexLineCharts.html">Line Charts</a></li>
               <li><a href="charts.apexMixCharts.html">Mix Charts</a></li>
               <li>
                  <a href="charts.apexPieDonutCharts.html">PieDonut Charts</a>
               </li>
               <li><a href="charts.apexRadarCharts.html">Radar Charts</a></li>
               <li>
                  <a href="charts.apexRadialBarCharts.html">RadialBar Charts</a>
               </li>
               <li>
                  <a href="charts.apexScatterCharts.html">Scatter Charts</a>
               </li>
               <li>
                  <a href="charts.apexSparklineCharts.html">Sparkline Charts</a>
               </li>
            </ul>
         </li>
      </ul>
      <ul class="childNav" data-parent="uikits">
         <li class="nav-item {{ $menu == "categorie_service" ? 'active' : '' }}">
            <a href="{{ route('index-service-categorie') }}"><i class="nav-icon i-Bell1"></i>
              <span class="item-name">Catégories services</span>
           </a>
         </li>
         <li class="nav-item {{ $menu == "sous_categorie_service" ? 'active' : '' }}">
            <a href="{{ route('index-sous-categorie-service') }}"><i class="nav-icon i-Bell1"></i>
              <span class="item-name">Sous-Catégories services</span>
           </a>
         </li>
         <li class="nav-item {{ $menu == "ss_categorie_service" ? 'active' : '' }}">
            <a href="{{ route('index-ss-categorie-service') }}"><i class="nav-icon i-Bell1"></i>
              <span class="item-name">SS-Catégories services</span>
           </a>
         </li>
         <li class="nav-item {{ $menu == "type_etablissement" ? 'active' : '' }}">
            <a href="{{ route('index-type-etablissement') }}"
               ><i class="nav-icon i-Split-Horizontal-2-Window"></i
               ><span class="item-name">Type d'établissements</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "forfait-pro" ? 'active' : '' }}">
            <a href="{{ route('index-forfait-pro') }}"
               ><i class="nav-icon i-Medal-2"></i
               ><span class="item-name">Forfaits Pro</span></a
               >
         </li>
		  <li class="nav-item {{ $menu == "forfait-usager" ? 'active' : '' }}">
            <a href="{{ route('index-forfait-usager') }}"
               ><i class="nav-icon i-Medal-2"></i
               ><span class="item-name">Forfaits Usagers</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "forfait-avantage-usager" ? 'active' : '' }}">
            <a href="{{ route('index-forfait-avantage-usager') }}"
               ><i class="nav-icon i-Medal-2"></i
               ><span class="item-name">Avantages Forfaits Usagers</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "marque" ? 'active' : '' }}">
            <a href="{{ route('index-marque') }}"
               ><i class="nav-icon i-Cursor-Click"></i
               ><span class="item-name">Marques</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "pays" ? 'active' : '' }}">
            <a href="{{ route('index-pays') }}"
               ><i class="nav-icon i-Cursor-Click"></i
               ><span class="item-name">Pays</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "ville" ? 'active' : '' }}">
            <a href="{{ route('index-ville') }}"
               ><i class="nav-icon i-Line-Chart-2"></i
               ><span class="item-name">Villes</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "commune" ? 'active' : '' }}">
            <a href="{{ route('index-commune') }}"
               ><i class="nav-icon i-ID-Card"></i
               ><span class="item-name">Communes</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "type_alert" ? 'active' : '' }}">
            <a href="{{ route('index-type-alert')}}"
               ><i class="nav-icon i-Video-Photographer"></i
               ><span class="item-name">Type Alerte</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "type_de_carburant" ? 'active' : '' }}">
            <a href="{{ route('index-type-de-carburant') }}"
               ><i class="nav-icon i-Arrow-Next"></i
               ><span class="item-name">Type de carburants</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "type_de_piece" ? 'active' : '' }}">
            <a href="{{ route('index-type-de-piece') }}"
               ><i class="nav-icon i-Receipt-4"></i
               ><span class="item-name">Type de pièces</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "type_de_vehicule" ? 'active' : '' }}">
            <a href="{{ route('index-type-de-vehicule') }}"
               ><i class="nav-icon i-Receipt-4"></i
               ><span class="item-name">Type de véhicules</span></a
               >
         </li>
         <li class="nav-item {{ $menu == "type_de_declaration" ? 'active' : '' }}">
            <a href="{{ route('index-type-de-declaration') }}"
               ><i class="nav-icon i-Receipt-4"></i
               ><span class="item-name">Type de déclarations</span></a
               >
         </li>
            <li class="nav-item {{ $menu == "type_de_prestation" ? 'active' : '' }}">
               <a href="{{ route('index-type-de-prestation') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Type de prestations</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "cabinet_expertise" ? 'active' : '' }}">
               <a href="{{ route('index-cabinet-expertise') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Cabinets d'expertise</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "visite_technique" ? 'active' : '' }}">
               <a href="{{ route('index-visite-technique') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Visites techniques</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "revision_technique" ? 'active' : '' }}">
               <a href="{{ route('index-revision-technique') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Révisions techniques</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "categorie_tv" ? 'active' : '' }}">
               <a href="{{ route('index-categorie-tv') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Catégories de TV</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "tv" ? 'active' : '' }}">
               <a href="{{ route('index-tv') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">TV</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "info" ? 'active' : '' }}">
               <a href="{{ route('index-info') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Informations</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "info" ? 'active' : '' }}">
               <a href="{{ route('index-contact-util') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Contacts utils</span></a
                  >
            </li>
		  <li class="nav-item {{ $menu == "commissariat_inofs" ? 'active' : '' }}">
               <a href="{{ route('index-commissariat-inofs') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Commissariat infos</span></a
                  >
            </li>
		  	<li class="nav-item {{ $menu == "entreprises_assurances" ? 'active' : '' }}">
               <a href="{{ route('index-entreprises-assurances') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Assurances</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "couleur" ? 'active' : '' }}">
               <a href="{{ route('index-couleur') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Couleurs</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "acteurs" ? 'active' : '' }}">
               <a href="{{ route('index-acteurs') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Acteurs</span></a
                  >
            </li>
            <li class="nav-item {{ $menu == "notifications" ? 'active' : '' }}">
               <a href="{{ route('notifications.index') }}"
                  ><i class="nav-icon i-Receipt-4"></i
                  ><span class="item-name">Notifications</span></a
                  >
            </li>
            <!-- Notifications par type d'alerte -->
            <li class="nav-item {{ $menu == "notifications-type-alerte" ? 'active' : '' }}">
               <a href="{{ route('notifications.by-alert.index') }}"
                  ><i class="nav-icon i-Alert"></i
                  ><span class="item-name">Notifications par Alerte</span></a
                  >
            </li>
      </ul>
      <ul class="childNav" data-parent="sessions">
         <li class="nav-item">
            <a href="../sessions/signin.html"
               ><i class="nav-icon i-Checked-User"></i
               ><span class="item-name">Sign in</span></a
               >
         </li>
         <li class="nav-item">
            <a href="../sessions/signup.html"
               ><i class="nav-icon i-Add-User"></i
               ><span class="item-name">Sign up</span></a
               >
         </li>
         <li class="nav-item">
            <a href="../sessions/forgot.html"
               ><i class="nav-icon i-Find-User"></i
               ><span class="item-name">Forgot</span></a
               >
         </li>
      </ul>
      <ul class="childNav" data-parent="others">
         <li class="nav-item">
            <a href="../sessions/not-found.html"
               ><i class="nav-icon i-Error-404-Window"></i
               ><span class="item-name">Not Found</span></a
               >
         </li>
         <li class="nav-item">
            <a href="user.profile.html"
               ><i class="nav-icon i-Male"></i
               ><span class="item-name">User Profile</span></a
               >
         </li>
         <li class="nav-item">
            <a class="open" href="blank.html"
               ><i class="nav-icon i-File-Horizontal"></i
               ><span class="item-name">Blank Page</span></a
               >
         </li>
      </ul>
   </div>
   <div class="sidebar-overlay"></div>
</div>
<!-- =============== Left side End ================-->
<div class="main-content-wrap sidenav-open d-flex flex-column">
   <!-- ============ Body content start ============= -->
   <div class="main-content">
