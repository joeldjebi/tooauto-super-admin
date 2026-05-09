@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Style pour la liste déroulante des utilisateurs */
    #userDropdownButton {
        min-height: 45px;
        padding: 10px 15px;
        font-size: 14px;
        border-radius: 6px;
        transition: all 0.3s ease;
        background: #fff;
        border: 2px solid #e0e0e0 !important;
    }
    #userDropdownButton:hover {
        border-color: #007bff !important;
        box-shadow: 0 2px 4px rgba(0,123,255,0.1);
    }
    #userDropdownButton:focus {
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    #userDropdownMenu {
        padding: 0;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-top: 5px;
    }

    /* Barre de recherche */
    #searchUsers {
        border: none;
        border-radius: 0;
        padding: 12px 15px;
        font-size: 14px;
        background: #f8f9fa;
    }
    #searchUsers:focus {
        box-shadow: none;
        background: #fff;
        border-bottom: 2px solid #007bff;
    }
    #searchUsers::placeholder {
        color: #6c757d;
    }

    /* Items utilisateurs */
    .user-item {
        padding: 12px 35px;
        margin: 0;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .user-item:last-child {
        border-bottom: none;
    }
    .user-item:hover {
        background: linear-gradient(90deg, #f8f9ff 0%, #ffffff 100%);
        padding-left: 20px;
    }
    .user-item .form-check {
        margin: 0;
        display: flex;
        align-items: center;
    }
    .user-item .form-check-input {
        cursor: pointer;
        width: 18px;
        height: 18px;
        margin-right: 12px;
        margin-top: 0;
        border: 2px solid #007bff;
        transition: all 0.2s ease;
    }
    .user-item .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    .user-item .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    .user-item .form-check-label {
        cursor: pointer;
        width: 100%;
        font-size: 14px;
        color: #333;
        font-weight: 500;
        display: flex;
        align-items: center;
        margin: 0;
    }
    .user-item .form-check-label small {
        margin-left: 8px;
        font-weight: 400;
        color: #6c757d;
    }

    /* Badge de compteur */
    #selectedCount {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 12px;
        font-weight: 600;
        margin-left: 8px;
    }

    /* Boutons d'action */
    #selectAllUsers, #deselectAllUsers {
        border-radius: 5px;
        padding: 6px 12px;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    #selectAllUsers:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,123,255,0.2);
    }
    #deselectAllUsers:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(108,117,125,0.2);
    }

    /* Card footer */
    .card-footer.bg-light {
        background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%) !important;
        border-top: 1px solid #e9ecef;
    }

    /* Animation pour le dropdown */
    .dropdown-menu.show {
        animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Style pour les items filtrés */
    .user-item[style*="display: none"] {
        display: none !important;
    }
</style>

<div class="row">
    <div class="col-lg-12 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card text-left">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Notifications Push Firebase</h4>
                    <button type="button" class="btn btn-outline-info btn-sm" data-toggle="collapse" data-target="#helpSection" aria-expanded="false" aria-controls="helpSection">
                        <i class="nav-icon i-Information"></i> Aide
                    </button>
                </div>

                <!-- Section d'aide -->
                <div class="collapse mb-3" id="helpSection">
                    <div class="card card-body bg-light">
                        <h5 class="mb-3"><i class="nav-icon i-Information text-primary"></i> Guide d'utilisation</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary"><i class="nav-icon i-User"></i> Notification Individuelle</h6>
                                <p class="mb-2"><strong>Rôle :</strong> Envoyer une notification à un seul utilisateur spécifique.</p>
                                <p class="mb-2"><strong>Quand l'utiliser :</strong> Messages personnalisés, confirmations individuelles, notifications ciblées.</p>
                                <p class="mb-0"><strong>Champs :</strong> Sélection d'un utilisateur, titre, message, données JSON (optionnel).</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary"><i class="nav-icon i-Users"></i> Notifications Groupées</h6>
                                <p class="mb-2"><strong>Rôle :</strong> Envoyer une notification à plusieurs utilisateurs sélectionnés.</p>
                                <p class="mb-2"><strong>Quand l'utiliser :</strong> Campagnes marketing ciblées, annonces pour un groupe spécifique.</p>
                                <p class="mb-0"><strong>Fonctionnalités :</strong> Sélection multiple avec checkboxes, recherche, boutons de sélection rapide.</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary"><i class="nav-icon i-Target"></i> Notification par Topic</h6>
                                <p class="mb-2"><strong>Rôle :</strong> Envoyer à tous les appareils abonnés à un topic Firebase.</p>
                                <p class="mb-2"><strong>Quand l'utiliser :</strong> Annonces pour une catégorie d'utilisateurs (ex: premium_users, all_users).</p>
                                <p class="mb-0"><strong>Exemple :</strong> Topic "premium_users" pour notifier tous les utilisateurs premium.</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary"><i class="nav-icon i-Targets"></i> Notifications par Topics</h6>
                                <p class="mb-2"><strong>Rôle :</strong> Envoyer à plusieurs topics simultanément.</p>
                                <p class="mb-2"><strong>Quand l'utiliser :</strong> Campagnes multi-segments, notifications pour plusieurs groupes.</p>
                                <p class="mb-0"><strong>Format :</strong> Un topic par ligne ou séparés par des virgules.</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading"><i class="nav-icon i-Alert"></i> Notifications par Type d'Alerte</h6>
                                    <p class="mb-2"><strong>Fonctionnalité séparée :</strong> Pour envoyer des notifications basées sur les alertes qui expirent, utilisez la page dédiée.</p>
                                    <a href="{{ route('notifications.by-alert.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="nav-icon i-Alert"></i> Accéder aux notifications par type d'alerte
                                    </a>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-info"><i class="nav-icon i-Info"></i> Champs communs</h6>
                                <ul class="mb-0">
                                    <li><strong>Titre :</strong> Titre de la notification (obligatoire, max 255 caractères)</li>
                                    <li><strong>Message :</strong> Contenu principal de la notification (obligatoire)</li>
                                    <li><strong>Données JSON :</strong> Données personnalisées pour l'application mobile (optionnel, format JSON valide)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglets -->
                <ul class="nav nav-tabs mb-3" id="notificationTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="individual-tab" data-toggle="tab" data-target="#individual" type="button" role="tab" aria-controls="individual" aria-selected="true">
                            Notification Individuelle
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="multiple-tab" data-toggle="tab" data-target="#multiple" type="button" role="tab" aria-controls="multiple" aria-selected="false">
                            Notifications Groupées (Tokens)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="topic-tab" data-toggle="tab" data-target="#topic" type="button" role="tab" aria-controls="topic" aria-selected="false">
                            Notification par Topic
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="topics-tab" data-toggle="tab" data-target="#topics" type="button" role="tab" aria-controls="topics" aria-selected="false">
                            Notifications par Topics
                        </button>
                    </li>
                </ul>

                <!-- Contenu des onglets -->
                <div class="tab-content" id="notificationTabsContent">
                    <!-- Notification Individuelle -->
                    <div class="tab-pane fade show active" id="individual" role="tabpanel" aria-labelledby="individual-tab">
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Envoyer une notification à un appareil</h5>
                                    <span class="badge badge-info" data-toggle="tooltip" data-placement="top" title="Sélectionnez un utilisateur unique pour recevoir la notification">
                                        <i class="nav-icon i-Information"></i> Individuel
                                    </span>
                                </div>
                                <p class="text-muted mb-3">
                                    <i class="nav-icon i-Info"></i>
                                    <strong>Usage :</strong> Envoyez une notification personnalisée à un seul utilisateur. Idéal pour les messages individuels, confirmations de commande, ou notifications ciblées.
                                </p>
                                <form action="{{ route('notifications.send-device') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="user_id_individual">Sélectionner un utilisateur <span class="text-danger">*</span></label>
                                        @if($users->isEmpty())
                                            <div class="alert alert-warning">
                                                <strong>Attention :</strong> Aucun utilisateur avec un token FCM trouvé dans la base de données.
                                                <br>Assurez-vous que les utilisateurs ont enregistré leur token FCM dans le champ <code>fcm_token</code> de la table <code>users</code>.
                                            </div>
                                            <select class="form-control select2" id="user_id_individual" name="user_id" disabled style="width: 100%;">
                                                <option>Aucun utilisateur disponible</option>
                                            </select>
                                        @else
                                            <div class="card border">
                                                <div class="card-body p-2">
                                                    <select class="form-control select2" id="user_id_individual" name="user_id" required style="width: 100%;">
                                                        <option value="">-- Sélectionner un utilisateur --</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}">
                                                                {{ $user->nom }} {{ $user->prenoms }}
                                                                @if($user->email)
                                                                    ({{ $user->email }})
                                                                @elseif($user->mobile)
                                                                    ({{ $user->mobile }})
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                        <small class="form-text text-muted mt-2">
                                            <i class="nav-icon i-User"></i>
                                            Sélectionnez un utilisateur dans la liste.
                                            @if(!$users->isEmpty())
                                                {{ $users->count() }} utilisateur(s) avec token FCM disponible(s).
                                            @endif
                                        </small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="title_individual">
                                            Titre <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Titre de la notification visible dans la barre de notification (max 255 caractères)"></i>
                                        </label>
                                        <input type="text" class="form-control" id="title_individual" name="title"
                                               placeholder="Ex: Nouvelle commande, Message important..." maxlength="255" required>
                                        <small class="form-text text-muted">Apparaît dans la barre de notification de l'appareil</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="body_individual">
                                            Message <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Contenu principal de la notification visible par l'utilisateur"></i>
                                        </label>
                                        <textarea class="form-control" id="body_individual" name="body" rows="4"
                                                  placeholder="Ex: Votre commande #12345 a été confirmée..." required></textarea>
                                        <small class="form-text text-muted">Contenu principal visible par l'utilisateur</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="data_individual">
                                            Données supplémentaires (JSON optionnel)
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Données personnalisées pour l'application mobile (navigation, actions, paramètres). Format JSON valide requis."></i>
                                        </label>
                                        <textarea class="form-control" id="data_individual" name="data_json" rows="3"
                                                  placeholder='{"action": "open_screen", "screen": "order_details", "order_id": "12345"}'></textarea>
                                        <small class="form-text text-muted">
                                            <i class="nav-icon i-Info"></i> Format JSON valide. Utilisé pour la navigation et les actions dans l'application mobile.
                                            <br>Exemple : <code>{"action": "open_screen", "screen": "profile"}</code>
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-primary" @if($users->isEmpty()) disabled @endif>Envoyer la notification</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Groupées (Tokens) -->
                    <div class="tab-pane fade" id="multiple" role="tabpanel" aria-labelledby="multiple-tab">
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Envoyer une notification à plusieurs appareils</h5>
                                    <span class="badge badge-primary" data-toggle="tooltip" data-placement="top" title="Sélectionnez plusieurs utilisateurs pour recevoir la notification simultanément">
                                        <i class="nav-icon i-Users"></i> Groupé
                                    </span>
                                </div>
                                <p class="text-muted mb-3">
                                    <i class="nav-icon i-Info"></i>
                                    <strong>Usage :</strong> Envoyez une notification à plusieurs utilisateurs sélectionnés. Utilisez la recherche pour filtrer, les boutons pour sélectionner rapidement, et les checkboxes pour choisir les destinataires.
                                </p>
                                <form action="{{ route('notifications.send-multiple') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0">
                                                Sélectionner les utilisateurs <span class="text-danger">*</span>
                                            </label>
                                            @if(!$users->isEmpty())
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary" id="selectAllUsers">
                                                        <i class="nav-icon i-Check"></i> Tout sélectionner
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" id="deselectAllUsers">
                                                        <i class="nav-icon i-Close"></i> Tout désélectionner
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        @if($users->isEmpty())
                                            <div class="alert alert-warning">
                                                <strong>Attention :</strong> Aucun utilisateur avec un token FCM trouvé dans la base de données.
                                                <br>Assurez-vous que les utilisateurs ont enregistré leur token FCM dans le champ <code>fcm_token</code> de la table <code>users</code>.
                                            </div>
                                        @else
                                            <div class="card border shadow-sm">
                                                <div class="card-body p-0">
                                                    <div class="dropdown" style="position: relative;">
                                                        <button class="btn btn-light btn-block text-left dropdown-toggle d-flex justify-content-between align-items-center" type="button" id="userDropdownButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span id="dropdownButtonText" class="flex-grow-1 text-left">
                                                                <i class="nav-icon i-Users mr-2"></i>
                                                                <span>Sélectionner des utilisateurs...</span>
                                                            </span>
                                                            <span class="badge badge-secondary ml-2" id="selectedCount">0</span>
                                                            <i class="nav-icon i-Arrow-Down ml-2"></i>
                                                        </button>
                                                        <div class="dropdown-menu w-100" id="userDropdownMenu" style="max-height: 350px; overflow-y: auto;">
                                                            <div class="px-3 py-3 border-bottom bg-light">
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text bg-white border-right-0">
                                                                            <i class="nav-icon i-Search"></i>
                                                                        </span>
                                                                    </div>
                                                                    <input type="text" class="form-control border-left-0" id="searchUsers" placeholder="Rechercher un utilisateur...">
                                                                </div>
                                                            </div>
                                                            <div class="py-2" style="max-height: 280px; overflow-y: auto;">
                                                                @foreach($users as $user)
                                                                    <div class="form-check user-item" data-user-name="{{ strtolower($user->nom . ' ' . $user->prenoms . ' ' . ($user->email ?? '') . ' ' . ($user->mobile ?? '')) }}">
                                                                        <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                                                        <label class="form-check-label" for="user_{{ $user->id }}">
                                                                            <strong>{{ $user->nom }} {{ $user->prenoms }}</strong>
                                                                            @if($user->email)
                                                                                <small class="text-muted d-block mt-1">
                                                                                    <i class="nav-icon i-Email"></i> {{ $user->email }}
                                                                                </small>
                                                                            @elseif($user->mobile)
                                                                                <small class="text-muted d-block mt-1">
                                                                                    <i class="nav-icon i-Phone"></i> {{ $user->mobile }}
                                                                                </small>
                                                                            @endif
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-light p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted mb-0">
                                                            <i class="nav-icon i-Information text-primary"></i>
                                                            Cliquez pour ouvrir la liste et sélectionner des utilisateurs
                                                        </small>
                                                        <span class="badge badge-info badge-pill" id="totalUsers">
                                                            <i class="nav-icon i-Users"></i> {{ $users->count() }} disponible(s)
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <small class="form-text text-muted mt-2">
                                            @if(!$users->isEmpty())
                                                <i class="nav-icon i-Users"></i>
                                                {{ $users->count() }} utilisateur(s) avec token FCM disponible(s).
                                            @endif
                                        </small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="title_multiple">
                                            Titre <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Titre de la notification visible dans la barre de notification (max 255 caractères)"></i>
                                        </label>
                                        <input type="text" class="form-control" id="title_multiple" name="title"
                                               placeholder="Ex: Nouvelle promotion, Annonce importante..." maxlength="255" required>
                                        <small class="form-text text-muted">Apparaît dans la barre de notification de l'appareil</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="body_multiple">
                                            Message <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Contenu principal de la notification visible par tous les destinataires"></i>
                                        </label>
                                        <textarea class="form-control" id="body_multiple" name="body" rows="4"
                                                  placeholder="Ex: Nouvelle promotion disponible, profitez-en maintenant !" required></textarea>
                                        <small class="form-text text-muted">Contenu principal visible par tous les utilisateurs sélectionnés</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="data_multiple">
                                            Données supplémentaires (JSON optionnel)
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Données personnalisées pour l'application mobile (navigation, actions, paramètres). Format JSON valide requis."></i>
                                        </label>
                                        <textarea class="form-control" id="data_multiple" name="data_json" rows="3"
                                                  placeholder='{"action": "open_screen", "screen": "promotions"}'></textarea>
                                        <small class="form-text text-muted">
                                            <i class="nav-icon i-Info"></i> Format JSON valide. Utilisé pour la navigation et les actions dans l'application mobile.
                                            <br>Exemple : <code>{"action": "open_screen", "screen": "promotions"}</code>
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-primary" @if($users->isEmpty()) disabled @endif>Envoyer les notifications</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notification par Topic -->
                    <div class="tab-pane fade" id="topic" role="tabpanel" aria-labelledby="topic-tab">
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Envoyer une notification à un topic (groupe)</h5>
                                    <span class="badge badge-success" data-toggle="tooltip" data-placement="top" title="Envoyez à tous les appareils abonnés à un topic Firebase">
                                        <i class="nav-icon i-Target"></i> Topic
                                    </span>
                                </div>
                                <p class="text-muted mb-3">
                                    <i class="nav-icon i-Info"></i>
                                    <strong>Usage :</strong> Envoyez une notification à tous les appareils abonnés à un topic Firebase (ex: "all_users", "premium_users"). Les topics doivent être créés et les appareils doivent s'y abonner via l'application mobile.
                                </p>
                                <form action="{{ route('notifications.send-topic') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="topic_single">
                                            Nom du topic <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Nom du topic Firebase auquel les appareils sont abonnés. Le topic doit exister et avoir des abonnés."></i>
                                        </label>
                                        <input type="text" class="form-control" id="topic_single" name="topic"
                                               placeholder="Ex: all_users, premium_users, android_users" maxlength="255" required>
                                        <small class="form-text text-muted">
                                            <i class="nav-icon i-Info"></i> Nom du topic Firebase. Exemples : <code>all_users</code>, <code>premium_users</code>, <code>android_users</code>
                                        </small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="title_topic">
                                            Titre <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Titre de la notification visible dans la barre de notification (max 255 caractères)"></i>
                                        </label>
                                        <input type="text" class="form-control" id="title_topic" name="title"
                                               placeholder="Ex: Annonce importante, Mise à jour..." maxlength="255" required>
                                        <small class="form-text text-muted">Apparaît dans la barre de notification de tous les appareils abonnés au topic</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="body_topic">
                                            Message <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Contenu principal de la notification visible par tous les abonnés au topic"></i>
                                        </label>
                                        <textarea class="form-control" id="body_topic" name="body" rows="4"
                                                  placeholder="Ex: Nouvelle fonctionnalité disponible pour tous les utilisateurs premium !" required></textarea>
                                        <small class="form-text text-muted">Contenu principal visible par tous les abonnés au topic</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="data_topic">
                                            Données supplémentaires (JSON optionnel)
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Données personnalisées pour l'application mobile (navigation, actions, paramètres). Format JSON valide requis."></i>
                                        </label>
                                        <textarea class="form-control" id="data_topic" name="data_json" rows="3"
                                                  placeholder='{"action": "open_screen", "screen": "announcements"}'></textarea>
                                        <small class="form-text text-muted">
                                            <i class="nav-icon i-Info"></i> Format JSON valide. Utilisé pour la navigation et les actions dans l'application mobile.
                                            <br>Exemple : <code>{"action": "open_screen", "screen": "announcements"}</code>
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Envoyer la notification</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications par Topics -->
                    <div class="tab-pane fade" id="topics" role="tabpanel" aria-labelledby="topics-tab">
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Envoyer une notification à plusieurs topics</h5>
                                    <span class="badge badge-warning" data-toggle="tooltip" data-placement="top" title="Envoyez simultanément à plusieurs topics Firebase">
                                        <i class="nav-icon i-Targets"></i> Multi-Topics
                                    </span>
                                </div>
                                <p class="text-muted mb-3">
                                    <i class="nav-icon i-Info"></i>
                                    <strong>Usage :</strong> Envoyez une notification à plusieurs topics en une seule fois. Entrez les noms des topics séparés par des virgules ou un par ligne (ex: "all_users, premium_users" ou un par ligne).
                                </p>
                                <form action="{{ route('notifications.send-topics') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="topics_multiple">
                                            Noms des topics <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Liste des topics Firebase destinataires. Un topic par ligne ou séparés par des virgules."></i>
                                        </label>
                                        <textarea class="form-control" id="topics_multiple" name="topics" rows="4"
                                                  placeholder="all_users&#10;premium_users&#10;android_users" required></textarea>
                                        <small class="form-text text-muted">
                                            <i class="nav-icon i-Info"></i> Un topic par ligne ou séparés par des virgules. Exemple :
                                            <br><code>all_users, premium_users</code> ou un par ligne
                                        </small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="title_topics">
                                            Titre <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Titre de la notification visible dans la barre de notification (max 255 caractères)"></i>
                                        </label>
                                        <input type="text" class="form-control" id="title_topics" name="title"
                                               placeholder="Ex: Campagne multi-segments, Annonce générale..." maxlength="255" required>
                                        <small class="form-text text-muted">Apparaît dans la barre de notification de tous les appareils abonnés aux topics</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="body_topics">
                                            Message <span class="text-danger">*</span>
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Contenu principal de la notification visible par tous les abonnés aux topics"></i>
                                        </label>
                                        <textarea class="form-control" id="body_topics" name="body" rows="4"
                                                  placeholder="Ex: Nouvelle campagne disponible pour plusieurs segments d'utilisateurs !" required></textarea>
                                        <small class="form-text text-muted">Contenu principal visible par tous les abonnés aux topics sélectionnés</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="data_topics">
                                            Données supplémentaires (JSON optionnel)
                                            <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Données personnalisées pour l'application mobile (navigation, actions, paramètres). Format JSON valide requis."></i>
                                        </label>
                                        <textarea class="form-control" id="data_topics" name="data_json" rows="3"
                                                  placeholder='{"action": "open_screen", "screen": "campaign"}'></textarea>
                                        <small class="form-text text-muted">
                                            <i class="nav-icon i-Info"></i> Format JSON valide. Utilisé pour la navigation et les actions dans l'application mobile.
                                            <br>Exemple : <code>{"action": "open_screen", "screen": "campaign"}</code>
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Envoyer les notifications</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialisation de Select2 pour la sélection multiple
$(document).ready(function() {
    // Initialiser Select2 pour le select des utilisateurs (notification individuelle)
    if ($('#user_id_individual').length && !$('#user_id_individual').prop('disabled')) {
        $('#user_id_individual').select2({
            placeholder: 'Rechercher et sélectionner un utilisateur...',
            allowClear: true,
            language: {
                noResults: function() {
                    return "Aucun résultat trouvé";
                },
                searching: function() {
                    return "Recherche en cours...";
                }
            }
        });
    }

    // Le code pour les boutons de sélection est maintenant dans le script après le footer

    // Gestion des données JSON dans les formulaires (tous les formulaires de notifications)
    $('form[action*="notifications"]').on('submit', function(e) {
        const dataTextarea = $(this).find('textarea[name="data_json"]');
        if (dataTextarea.length && dataTextarea.val().trim()) {
            try {
                const jsonData = JSON.parse(dataTextarea.val());
                // Créer un champ caché pour envoyer les données
                $(this).append('<input type="hidden" name="data" value=\'' + JSON.stringify(jsonData) + '\'>');
            } catch (error) {
                e.preventDefault();
                alert('Erreur dans le format JSON des données supplémentaires: ' + error.message);
                return false;
            }
        }
    });
});
</script>

@include('layouts.footer')

<script>
// Script pour les notifications - doit être après le chargement de jQuery
(function() {
    // Attendre que jQuery soit chargé
    function initNotifications() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initNotifications, 100);
            return;
        }

        var $ = jQuery;

        console.log('jQuery chargé, initialisation des notifications...');

        // Gestion de la liste déroulante avec checkboxes pour les utilisateurs
        $('#userDropdownMenu').on('click', function(e) {
            e.stopPropagation();
        });

        // Fonction pour mettre à jour le compteur et le texte du bouton
        function updateSelectedCount() {
            console.log('updateSelectedCount appelé');
            const selected = $('.user-checkbox:checked');
            const count = selected.length;

            // Mettre à jour le badge
            $('#selectedCount').text(count);

            // Mettre à jour le texte du bouton et le style du badge
            if (count === 0) {
                $('#dropdownButtonText').html('<i class="nav-icon i-Users mr-2"></i><span>Sélectionner des utilisateurs...</span>');
                $('#selectedCount').removeClass('badge-primary').addClass('badge-secondary');
            } else if (count === 1) {
                const userName = selected.closest('.user-item').find('label strong').text().trim();
                $('#dropdownButtonText').html('<i class="nav-icon i-Users mr-2"></i><span>' + userName + '</span>');
                $('#selectedCount').removeClass('badge-secondary').addClass('badge-primary');
            } else {
                $('#dropdownButtonText').html('<i class="nav-icon i-Users mr-2"></i><span>' + count + ' utilisateur(s) sélectionné(s)</span>');
                $('#selectedCount').removeClass('badge-secondary').addClass('badge-primary');
            }
        }

        // Mettre à jour le compteur lors des changements de checkboxes
        $(document).on('change', '.user-checkbox', function() {
            updateSelectedCount();
        });

        // Recherche dans la liste
        $('#searchUsers').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.user-item').each(function() {
                const userName = $(this).data('user-name');
                if (userName.indexOf(searchTerm) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Bouton "Tout sélectionner"
        $(document).on('click', '#selectAllUsers', function(e) {
            console.log('Bouton "Tout sélectionner" cliqué');
            e.preventDefault();
            e.stopPropagation();

            const checkboxes = $('.user-checkbox:visible');
            console.log('Nombre de checkboxes visibles:', checkboxes.length);

            // Sélectionner tous les checkboxes visibles
            checkboxes.each(function(index) {
                console.log('Sélection checkbox', index, ':', $(this).val());
                $(this).prop('checked', true).trigger('change');
            });

            console.log('Mise à jour du compteur...');
            updateSelectedCount();
            console.log('Compteur mis à jour');
        });

        // Bouton "Tout désélectionner"
        $(document).on('click', '#deselectAllUsers', function(e) {
            console.log('Bouton "Tout désélectionner" cliqué');
            e.preventDefault();
            e.stopPropagation();

            const checkboxes = $('.user-checkbox');
            console.log('Nombre total de checkboxes:', checkboxes.length);

            // Désélectionner tous les checkboxes
            checkboxes.each(function(index) {
                console.log('Désélection checkbox', index, ':', $(this).val());
                $(this).prop('checked', false).trigger('change');
            });

            console.log('Mise à jour du compteur...');
            updateSelectedCount();
            console.log('Compteur mis à jour');
        });

        // Initialiser le compteur au chargement
        console.log('Initialisation du compteur...');
        updateSelectedCount();

        // Vérifier si les boutons existent
        setTimeout(function() {
            console.log('=== VÉRIFICATION DES BOUTONS ===');
            console.log('Bouton selectAllUsers existe:', $('#selectAllUsers').length > 0);
            console.log('Bouton deselectAllUsers existe:', $('#deselectAllUsers').length > 0);
            console.log('Nombre de checkboxes:', $('.user-checkbox').length);
        }, 1000);
    }

    // Démarrer l'initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
})();

// Initialiser les tooltips Bootstrap
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

