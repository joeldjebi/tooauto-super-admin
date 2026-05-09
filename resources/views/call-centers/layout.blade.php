<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Call Center' }}</title>
    <link href="/dist-assets/css/themes/lite-purple.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f4f6;
        }
        .cc-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }
        .cc-sidebar {
            background: linear-gradient(180deg, #102a43 0%, #0b1f33 100%);
            color: #fff;
            padding: 24px 16px;
        }
        .cc-brand {
            padding: 8px 12px 24px;
            border-bottom: 1px solid rgba(255,255,255,.12);
            margin-bottom: 20px;
        }
        .cc-brand h2 {
            font-size: 1.1rem;
            margin: 0;
            color: #fff;
        }
        .cc-brand p {
            margin: 6px 0 0;
            color: rgba(255,255,255,.72);
            font-size: .9rem;
        }
        .cc-nav {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .cc-nav a {
            display: block;
            padding: 10px 12px;
            border-radius: 10px;
            color: rgba(255,255,255,.82);
            text-decoration: none;
            transition: .2s ease;
        }
        .cc-nav a:hover,
        .cc-nav a.active {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        .cc-main {
            padding: 28px;
        }
        .cc-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .cc-topbar h1 {
            margin: 0;
            font-size: 1.6rem;
            color: #102a43;
        }
        .cc-topbar p {
            margin: 4px 0 0;
            color: #6b7280;
        }
        .cc-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
            padding: 22px;
        }
        @media (max-width: 900px) {
            .cc-shell {
                grid-template-columns: 1fr;
            }
            .cc-sidebar {
                padding-bottom: 12px;
            }
        }
    </style>
</head>
<body>
    @php
        $callCenterMenu = [
            ['key' => 'call-center-dashboard', 'label' => 'Dashboard', 'route' => route('call-center.dashboard')],
            ['key' => 'call-center-users', 'label' => 'Users', 'route' => route('call-center.users')],
            ['key' => 'call-center-professionnels', 'label' => 'Professionnels', 'route' => route('call-center.professionnels')],
            ['key' => 'call-center-vehicules', 'label' => 'Vehicules', 'route' => route('call-center.vehicules')],
            ['key' => 'call-center-station-services', 'label' => 'Station services', 'route' => route('call-center.station-services')],
            ['key' => 'call-center-station-de-lavages', 'label' => 'Station de lavages', 'route' => route('call-center.station-de-lavages')],
            ['key' => 'call-center-annonces', 'label' => 'Annonces', 'route' => route('call-center.annonces')],
            ['key' => 'call-center-annonce-concessionnaires', 'label' => 'Annonce concessionnaires', 'route' => route('call-center.annonce-concessionnaires')],
            ['key' => 'call-center-annonce-etablissements', 'label' => 'Annonce etablissements', 'route' => route('call-center.annonce-etablissements')],
            ['key' => 'call-center-concessionnaires', 'label' => 'Concessionnaires', 'route' => route('call-center.concessionnaires')],
            ['key' => 'call-center-etablissements', 'label' => 'Etablissements', 'route' => route('call-center.etablissements')],
            ['key' => 'call-center-autodocs', 'label' => 'Autodocs', 'route' => route('call-center.autodocs')],
        ];
    @endphp

    <div class="cc-shell">
        <aside class="cc-sidebar">
            <div class="cc-brand">
                <h2>Espace Call Center</h2>
                <p>{{ $user->full_name ?? $user->email ?? 'Utilisateur' }}</p>
            </div>

            <nav class="cc-nav">
                @foreach ($callCenterMenu as $item)
                    <a href="{{ $item['route'] }}" class="{{ ($menu ?? '') === $item['key'] ? 'active' : '' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="cc-main">
            <div class="cc-topbar">
                <div>
                    <h1>{{ $title ?? 'Call Center' }}</h1>
                    <p>Consultation des donnees disponibles pour le call center.</p>
                </div>

                <form action="{{ route('call-center.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">Deconnexion</button>
                </form>
            </div>

            @if(session()->has('message'))
                <div class="alert {{ session()->get('type') ?? 'alert-info' }}">
                    {{ session()->get('message') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
