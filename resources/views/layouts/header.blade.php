<!DOCTYPE html>
<html lang="fr" dir="">
   <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width,initial-scale=1" />
      <meta http-equiv="X-UA-Compatible" content="ie=edge" />
      <title>{{ $title ?? 'TooAuto' }} · TooAuto BO</title>
      <link
         href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900"
         rel="stylesheet"
         />
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
      <link
         href="../../dist-assets/css/themes/lite-purple.min.css"
         rel="stylesheet"
         />
      <link href="../../dist-assets/css/plugins/perfect-scrollbar.min.css"rel="stylesheet"/>

      <link href="../../dist-assets/css/main.css"rel="stylesheet"/>
      <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
         <!-- Include Select2 JS -->
      <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
      <link href="../../dist-assets/css/apx-shell.css" rel="stylesheet" />
   </head>
   <body class="text-start">
      @php
         $apxAdmin = auth()->user();
         $apxAdminName = trim(($apxAdmin->prenoms ?? '') . ' ' . ($apxAdmin->nom ?? '')) ?: 'Administrateur';
         $apxInitials = collect(preg_split('/\s+/', $apxAdminName))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
      @endphp
      <div class="app-admin-wrap layout-sidebar-large">
         <div class="main-header">
            <div class="logo">
               <img src="../../dist-assets/images/logo.png" alt="TooAuto" />
            </div>
            <div class="menu-toggle">
               <div></div>
               <div></div>
               <div></div>
            </div>
            <div class="apx-header-title d-none d-md-block">
               <small>TooAuto · Back office</small>
               {{ $title ?? 'Tableau de bord' }}
            </div>
            <div style="margin: auto"></div>
            <div class="header-part-right">
               <button type="button" class="apx-icon-btn d-none d-sm-inline-flex" data-fullscreen title="Plein écran" aria-label="Plein écran">
                  <i class="i-Full-Screen"></i>
               </button>
               <div class="dropdown">
                  <button
                     type="button"
                     class="apx-avatar"
                     id="userDropdown"
                     data-bs-toggle="dropdown"
                     aria-haspopup="true"
                     aria-expanded="false"
                     title="{{ $apxAdminName }}"
                     >{{ $apxInitials ?: 'A' }}</button>
                  <div class="dropdown-menu dropdown-menu-end dropdown-menu-right apx-user-menu" aria-labelledby="userDropdown">
                     <div class="apx-user-head">
                        <strong>{{ $apxAdminName }}</strong>
                        @if(!empty($apxAdmin->email))
                           <small>{{ $apxAdmin->email }}</small>
                        @endif
                     </div>
                     <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item apx-danger">Se déconnecter</button>
                     </form>
                  </div>
               </div>
            </div>
         </div>
