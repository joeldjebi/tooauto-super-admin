@extends('call-centers.layout')

@section('lead', 'Accédez rapidement aux listes et suivez vos appels.')

@section('content')
    @php
        $cardStyles = [
            'Alertes' => ['icon' => 'alertes', 'tone' => 'red', 'hint' => 'Échéances à relancer'],
            'Usagers' => ['icon' => 'users', 'tone' => 'blue', 'hint' => 'Clients et suivi d\'appel'],
            'Professionnels' => ['icon' => 'professionnels', 'tone' => 'indigo', 'hint' => 'Comptes professionnels'],
            'Véhicules' => ['icon' => 'vehicules', 'tone' => 'teal', 'hint' => 'Parc et photos'],
            'Stations service' => ['icon' => 'station-services', 'tone' => 'orange', 'hint' => 'Stations carburant'],
            'Stations de lavage' => ['icon' => 'station-de-lavages', 'tone' => 'teal', 'hint' => 'Points de lavage'],
            'Annonces' => ['icon' => 'annonces', 'tone' => 'pink', 'hint' => 'Annonces des usagers'],
            'Annonces concessionnaires' => ['icon' => 'annonce-concessionnaires', 'tone' => 'purple', 'hint' => 'Offres concessionnaires'],
            'Annonces établissements' => ['icon' => 'annonce-etablissements', 'tone' => 'red', 'hint' => 'Offres établissements'],
            'Concessionnaires' => ['icon' => 'concessionnaires', 'tone' => 'indigo', 'hint' => 'Réseau concessionnaires'],
            'Établissements' => ['icon' => 'etablissements', 'tone' => 'green', 'hint' => 'Garages et services'],
            'Autodocs' => ['icon' => 'autodocs', 'tone' => 'yellow', 'hint' => 'Documents automobiles'],
            'Envoi de notifications' => ['icon' => 'notification-send', 'tone' => 'red', 'hint' => 'Cibler et envoyer'],
        ];
    @endphp

    <style>
        .cc-section-head {
            align-items: baseline;
            display: flex;
            justify-content: space-between;
            margin: 0 0 14px;
        }
        .cc-section-head h2 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -.022em;
            margin: 0;
        }
        .cc-section-head a {
            font-size: 14px;
            font-weight: 500;
        }
        .cc-stat-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            margin-bottom: 20px;
        }
        .cc-stat {
            background: var(--cc-surface);
            border-radius: var(--cc-radius-lg);
            box-shadow: var(--cc-shadow);
            color: var(--cc-label) !important;
            display: block;
            padding: 18px 20px;
            position: relative;
            text-decoration: none !important;
            transition: transform .25s var(--cc-ease), box-shadow .25s var(--cc-ease);
        }
        .cc-stat:hover {
            box-shadow: var(--cc-shadow-pop);
            transform: translateY(-2px);
        }
        .cc-stat-label {
            align-items: center;
            color: var(--cc-label-2);
            display: flex;
            font-size: 13.5px;
            font-weight: 500;
            gap: 8px;
        }
        .cc-stat-label .cc-dot {
            height: 9px;
            margin: 0;
            width: 9px;
        }
        .cc-stat-value {
            display: block;
            font-size: 38px;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            letter-spacing: -.03em;
            line-height: 1.1;
            margin-top: 8px;
        }
        .cc-stat-hint {
            color: var(--cc-label-3);
            font-size: 12.5px;
        }
        .cc-stat > .cc-icon {
            color: var(--cc-label-3);
            height: 18px;
            position: absolute;
            right: 16px;
            top: 18px;
            width: 18px;
        }
        .cc-stat-expired .cc-stat-label,
        .cc-stat-expired .cc-stat-value { color: var(--cc-red); }
        .cc-stat-today .cc-stat-label { color: var(--cc-red); }
        .cc-stat-urgent .cc-stat-label,
        .cc-stat-urgent .cc-stat-value { color: var(--cc-orange); }
        .cc-stat-soon .cc-stat-label { color: #8a6100; }
        .cc-alert-search {
            align-items: end;
            display: grid;
            gap: 14px 16px;
            grid-template-columns: 2fr 1.6fr 1fr 1fr auto;
        }
        .cc-alert-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .cc-alert-list li + li {
            border-top: 1px solid var(--cc-separator);
        }
        .cc-alert-item {
            align-items: center;
            color: var(--cc-label) !important;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin: 0 -10px;
            padding: 11px 10px;
            border-radius: 10px;
            text-decoration: none !important;
            transition: background .15s var(--cc-ease);
        }
        .cc-alert-item:hover {
            background: var(--cc-fill-2);
        }
        .cc-alert-name {
            color: var(--cc-label) !important;
            display: block;
            font-size: 14.5px;
            font-weight: 600;
        }
        .cc-alert-name:hover {
            color: var(--cc-blue) !important;
        }
        .cc-call-grid {
            column-gap: 32px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        }
        .cc-call-grid li + li {
            border-top: 0;
        }
        .cc-call-grid li {
            border-bottom: 1px solid var(--cc-separator);
        }
        .cc-alert-item small {
            color: var(--cc-label-2);
            display: block;
            font-size: 12.5px;
            margin-top: 1px;
        }
        .cc-alert-item-end {
            align-items: flex-end;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            gap: 4px;
        }
        .cc-alert-item-end small {
            font-variant-numeric: tabular-nums;
        }
        @media (max-width: 1100px) {
            .cc-alert-search {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 560px) {
            .cc-alert-search {
                grid-template-columns: 1fr;
            }
            .cc-stat-grid {
                gap: 12px;
                grid-template-columns: 1fr 1fr;
            }
            .cc-stat {
                padding: 14px 16px;
            }
            .cc-stat-value {
                font-size: 30px;
            }
        }
        .cc-launch-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
        .cc-launch {
            align-items: center;
            background: var(--cc-surface);
            border-radius: var(--cc-radius-lg);
            box-shadow: var(--cc-shadow);
            color: var(--cc-label) !important;
            display: flex;
            gap: 14px;
            padding: 18px;
            text-decoration: none !important;
            transition: transform .25s var(--cc-ease), box-shadow .25s var(--cc-ease);
        }
        .cc-launch:hover {
            box-shadow: var(--cc-shadow-pop);
            transform: translateY(-2px);
        }
        .cc-launch:active {
            transform: scale(.985);
        }
        .cc-launch-text {
            flex: 1;
            min-width: 0;
        }
        .cc-launch-text strong {
            display: block;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: -.015em;
        }
        .cc-launch-text span {
            color: var(--cc-label-2);
            display: block;
            font-size: 13px;
            margin-top: 2px;
        }
        .cc-launch > .cc-icon {
            color: var(--cc-label-3);
            height: 18px;
            transition: transform .25s var(--cc-ease);
            width: 18px;
        }
        .cc-launch:hover > .cc-icon {
            color: var(--cc-blue);
            transform: translateX(2px);
        }
    </style>

    @if($alertStats)
        @php
            $statTiles = [
                ['key' => 'expirees', 'label' => 'Expirées', 'hint' => 'Date dépassée', 'tone' => 'expired', 'value' => $alertStats->expirees],
                ['key' => 'aujourdhui', 'label' => "Aujourd'hui", 'hint' => "Expirent aujourd'hui", 'tone' => 'today', 'value' => $alertStats->aujourdhui],
                ['key' => '7j', 'label' => 'Sous 7 jours', 'hint' => 'À relancer en urgence', 'tone' => 'urgent', 'value' => $alertStats->sous_7j],
                ['key' => '30j', 'label' => 'Sous 30 jours', 'hint' => 'Bientôt à échéance', 'tone' => 'soon', 'value' => $alertStats->sous_30j],
            ];
            $alertBlocks = [
                ['title' => 'Expirent bientôt', 'subtitle' => 'Dans les 30 prochains jours', 'items' => $upcomingAlerts, 'filter' => '30j', 'empty' => "Aucune alerte n'expire dans les 30 jours."],
                ['title' => 'Récemment expirées', 'subtitle' => "Les plus récentes d'abord", 'items' => $expiredAlerts, 'filter' => 'expirees', 'empty' => 'Aucune alerte expirée.'],
            ];
        @endphp

        <div class="cc-section-head">
            <h2>Alertes</h2>
            <a href="{{ route('call-center.alertes') }}">Voir toutes les alertes &rsaquo;</a>
        </div>

        <div class="cc-stat-grid">
            @foreach($statTiles as $tile)
                <a href="{{ route('call-center.alertes', ['echeance' => $tile['key']]) }}" class="cc-stat cc-stat-{{ $tile['tone'] }}">
                    <span class="cc-stat-label"><span class="cc-dot"></span>{{ $tile['label'] }}</span>
                    <span class="cc-stat-value">{{ number_format((int) $tile['value'], 0, ',', ' ') }}</span>
                    <span class="cc-stat-hint">{{ $tile['hint'] }}</span>
                    @include('call-centers.partials.icon', ['name' => 'chevron'])
                </a>
            @endforeach
        </div>

        <div class="cc-card mb-4">
            <div class="cc-card-header" style="margin-bottom: 16px;">
                <div>
                    <h5 class="cc-card-title">Rechercher une alerte</h5>
                    <p class="cc-card-subtitle">Trouvez les alertes expirées ou presque expirées.</p>
                </div>
            </div>
            <form method="GET" action="{{ route('call-center.alertes') }}" class="cc-alert-search">
                <div>
                    <label class="cc-field-label" for="cc-alert-search">Recherche</label>
                    <input id="cc-alert-search" type="text" name="search" class="form-control cc-input-search" placeholder="Nom, mobile, véhicule, type">
                </div>
                <div>
                    <label class="cc-field-label" for="cc-alert-due">{{ $alertFilter['label'] }}</label>
                    <select id="cc-alert-due" name="echeance" class="form-control">
                        <option value="">Toutes</option>
                        @foreach($alertFilter['options'] as $option)
                            <option value="{{ $option['value'] }}" {{ $option['value'] === 'a_traiter' ? 'selected' : '' }}>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="cc-field-label" for="cc-alert-from">Expire à partir du</label>
                    <input id="cc-alert-from" type="date" name="fin_from" class="form-control">
                </div>
                <div>
                    <label class="cc-field-label" for="cc-alert-to">Expire jusqu'au</label>
                    <input id="cc-alert-to" type="date" name="fin_to" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary" style="height: 42px;">
                    @include('call-centers.partials.icon', ['name' => 'search'])
                    Rechercher
                </button>
            </form>
        </div>

        <div class="cc-card mb-4">
            <div class="cc-card-header" style="margin-bottom: 10px;">
                <div>
                    <h5 class="cc-card-title">Usagers à rappeler</h5>
                    <p class="cc-card-subtitle">Usagers ayant au moins une alerte expirée, non appelés en premier.</p>
                </div>
                <div class="cc-header-actions">
                    <span class="cc-count">{{ $usersToCall['total'] }} user(s)</span>
                    <a href="{{ route('call-center.alertes', ['echeance' => 'expirees']) }}" class="btn btn-sm btn-outline-primary">Tout voir</a>
                </div>
            </div>

            @if($usersToCall['items']->isNotEmpty())
                <ul class="cc-alert-list cc-call-grid">
                    @foreach($usersToCall['items'] as $contact)
                        @php
                            $contactName = trim(($contact->nom ?? '') . ' ' . ($contact->prenoms ?? '')) ?: 'Usager #' . $contact->id;
                            $contactCalled = (string) ($contact->call_center_deja_appele ?? '0') === '1';
                        @endphp
                        <li class="cc-alert-item">
                            <span style="min-width: 0;">
                                <a href="{{ route('call-center.users.alerts', $contact->id) }}" class="cc-alert-name">{{ $contactName }}</a>
                                <small>
                                    {{ $contact->nb_expirees }} alerte(s) expiree(s) · dernière le {{ \Carbon\Carbon::parse($contact->derniere_expiration)->format('d/m/Y') }}
                                </small>
                                <span class="d-block mt-1">
                                    @include('call-centers.partials.phone', ['mobile' => $contact->mobile ?? null, 'indicatif' => $contact->indicatif ?? null, 'whatsapp' => (string) ($contact->is_whatsapp ?? '0') === '1'])
                                </span>
                            </span>
                            <span class="cc-alert-item-end">
                                @if(property_exists($contact, 'call_center_deja_appele'))
                                    <span class="badge {{ $contactCalled ? 'badge-success' : 'badge-light' }}"><span class="cc-dot"></span>{{ $contactCalled ? 'Déjà appelé' : 'À appeler' }}</span>
                                @endif
                                @include('call-centers.partials.alert-due', ['days' => $contact->echeance])
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="cc-empty-state" style="padding: 28px 12px;">
                    <span>Aucun usager avec une alerte expirée.</span>
                </div>
            @endif
        </div>

        <div class="row mb-2">
            @foreach($alertBlocks as $block)
                <div class="col-lg-6 mb-4">
                    <div class="cc-card h-100">
                        <div class="cc-card-header" style="margin-bottom: 10px;">
                            <div>
                                <h5 class="cc-card-title">{{ $block['title'] }}</h5>
                                <p class="cc-card-subtitle">{{ $block['subtitle'] }}</p>
                            </div>
                            <a href="{{ route('call-center.alertes', ['echeance' => $block['filter']]) }}" class="btn btn-sm btn-outline-primary">Tout voir</a>
                        </div>

                        @if($block['items']->isNotEmpty())
                            <ul class="cc-alert-list">
                                @foreach($block['items'] as $alert)
                                    <li class="cc-alert-item">
                                        <span style="min-width: 0;">
                                            <a href="{{ $alert->user_id ? route('call-center.users.alerts', $alert->user_id) : '#' }}" class="cc-alert-name">
                                                {{ $alert->utilisateur !== '' ? $alert->utilisateur : 'Usager #' . $alert->user_id }}
                                            </a>
                                            <small>{{ collect([$alert->type_alerte ?? null, $alert->vehicule ?? null])->filter()->implode(' · ') }}</small>
                                            <span class="d-block mt-1">
                                                @include('call-centers.partials.phone', ['mobile' => $alert->mobile ?? null, 'indicatif' => $alert->indicatif ?? null, 'whatsapp' => (string) ($alert->is_whatsapp ?? '0') === '1'])
                                            </span>
                                        </span>
                                        <span class="cc-alert-item-end">
                                            @include('call-centers.partials.alert-due', ['days' => $alert->echeance])
                                            <small class="cc-muted">{{ \Carbon\Carbon::parse($alert->date_fin)->format('d/m/Y') }}</small>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="cc-empty-state" style="padding: 28px 12px;">
                                <span>{{ $block['empty'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="cc-section-head">
            <h2>Accès rapide</h2>
        </div>
    @endif

    <div class="cc-launch-grid">
        @foreach ($cards as $card)
            @php
                $style = $cardStyles[$card['label']] ?? ['icon' => 'dashboard', 'tone' => 'gray', 'hint' => 'Afficher la liste'];
            @endphp
            <a href="{{ $card['route'] }}" class="cc-launch">
                <span class="cc-tile cc-tile-lg cc-tone-{{ $style['tone'] }}">
                    @include('call-centers.partials.icon', ['name' => $style['icon']])
                </span>
                <span class="cc-launch-text">
                    <strong>{{ $card['label'] }}</strong>
                    <span>{{ $style['hint'] }}</span>
                </span>
                @include('call-centers.partials.icon', ['name' => 'chevron'])
            </a>
        @endforeach
    </div>
@endsection
