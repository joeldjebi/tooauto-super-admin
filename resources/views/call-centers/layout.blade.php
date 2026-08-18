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
            min-width: 0;
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
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
            min-width: 0;
            padding: 22px;
        }
        .cc-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }
        .cc-card-title {
            margin: 0;
            color: #102a43;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .cc-card-subtitle {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: .9rem;
        }
        .cc-filter-panel {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 18px;
        }
        .cc-filter-panel label {
            color: #475569;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .cc-date-filter {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 14px;
        }
        .cc-table-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            scrollbar-color: #94a3b8 #e5e7eb;
            scrollbar-width: thin;
        }
        .cc-table-wrap::-webkit-scrollbar {
            height: 10px;
        }
        .cc-table-wrap::-webkit-scrollbar-track {
            background: #e5e7eb;
            border-radius: 999px;
        }
        .cc-table-wrap::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 999px;
        }
        .cc-table {
            margin-bottom: 0;
            min-width: 100%;
            width: max-content;
        }
        .cc-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            border-bottom: 1px solid #dbe3ef;
            color: #334155;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .cc-table td {
            color: #334155;
            font-size: .88rem;
            max-width: 260px;
            vertical-align: top;
            white-space: nowrap;
        }
        .cc-table td:not(.cc-table-actions) {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cc-table tbody tr:hover {
            background: #f8fafc;
        }
        .cc-table-actions {
            min-width: 260px;
            max-width: 340px;
        }
        .cc-follow-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            min-width: 130px;
        }
        .cc-follow-badges .badge {
            font-size: .76rem;
            line-height: 1.2;
            padding: 5px 7px;
        }
        .cc-actions-dropdown {
            position: relative;
            display: inline-block;
        }
        .cc-actions-dropdown summary {
            list-style: none;
        }
        .cc-actions-dropdown summary::-webkit-details-marker {
            display: none;
        }
        .cc-actions-toggle {
            min-width: 110px;
        }
        .cc-actions-menu {
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
            margin-top: 6px;
            padding: 10px;
            position: absolute;
            right: 0;
            top: 100%;
            width: 280px;
            z-index: 20;
        }
        .cc-actions-dropdown[open] .cc-actions-toggle {
            background: #102a43;
            border-color: #102a43;
            color: #fff;
        }
        .cc-action-links,
        .cc-action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .cc-call-panel {
            border-top: 1px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 8px;
        }
        .cc-note-preview {
            background: #f8fafc;
            border-left: 3px solid #38bdf8;
            border-radius: 6px;
            color: #475569;
            font-size: .8rem;
            margin-top: 8px;
            padding: 7px 9px;
        }
        .cc-note-editor {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 8px;
            min-width: 260px;
            padding: 10px;
        }
        .cc-empty-state {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #64748b;
            padding: 18px;
            text-align: center;
        }
        @media (max-width: 900px) {
            .cc-shell {
                grid-template-columns: 1fr;
            }
            .cc-sidebar {
                padding-bottom: 12px;
            }
            .cc-main {
                padding: 18px;
            }
            .cc-card-header {
                flex-direction: column;
            }
            .cc-table-wrap {
                border: 0;
                overflow: visible;
            }
            .cc-table {
                display: block;
                min-width: 0;
                width: 100%;
            }
            .cc-table thead {
                display: none;
            }
            .cc-table tbody,
            .cc-table tr,
            .cc-table td {
                display: block;
                width: 100%;
            }
            .cc-table tbody tr {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 12px;
                padding: 8px 0;
            }
            .cc-table td {
                align-items: flex-start;
                border-top: 0;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                max-width: none;
                padding: 8px 12px;
                white-space: normal;
            }
            .cc-table td::before {
                color: #64748b;
                content: attr(data-label);
                flex: 0 0 38%;
                font-size: .72rem;
                font-weight: 800;
                letter-spacing: .03em;
                text-transform: uppercase;
            }
            .cc-table td > * {
                max-width: 62%;
            }
            .cc-table-actions {
                max-width: none;
                min-width: 0;
            }
            .cc-table-actions::before {
                padding-top: 6px;
            }
            .cc-actions-dropdown,
            .cc-actions-toggle {
                width: 100%;
            }
            .cc-actions-menu {
                position: static;
                width: 100%;
            }
            .cc-follow-badges {
                justify-content: flex-end;
                min-width: 0;
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
            ['key' => 'call-center-notification-send', 'label' => 'Notification send', 'route' => route('call-center.notification-send.index')],
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
