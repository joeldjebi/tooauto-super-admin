<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <i class="fas fa-home"></i> Accueil
            </a>
        </li>
        @yield('breadcrumb')
    </ol>
</nav>
