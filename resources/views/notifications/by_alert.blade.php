@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .alert-info-card {
        border-left: 4px solid #17a2b8;
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
                    <h4 class="card-title mb-0">Notifications par Type d'Alerte</h4>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="nav-icon i-Arrow-Left"></i> Retour aux notifications
                    </a>
                </div>

                <!-- Section d'aide -->
                <div class="card card-body bg-light mb-3 alert-info-card">
                    <h5 class="mb-3"><i class="nav-icon i-Information text-primary"></i> Guide d'utilisation</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-primary"><i class="nav-icon i-Alert"></i> Notifications par Type d'Alerte</h6>
                            <p class="mb-2"><strong>Rôle :</strong> Envoyer automatiquement des notifications aux utilisateurs dont les alertes d'un type spécifique expirent dans un nombre de jours donné.</p>
                            <p class="mb-2"><strong>Quand l'utiliser :</strong> Rappels d'expiration d'assurance, vidange, visite technique, contrôle technique, etc.</p>
                            <p class="mb-2"><strong>Fonctionnement :</strong></p>
                            <ul class="mb-0">
                                <li>Sélectionnez le type d'alerte (Assurance, Vidange, etc.)</li>
                                <li>Définissez le nombre de jours avant expiration (ex: 7 jours)</li>
                                <li>Rédigez le titre et le message de la notification</li>
                                <li>Le système trouvera automatiquement tous les utilisateurs concernés</li>
                            </ul>
                            <p class="mt-2 mb-0"><strong>Exemple :</strong> Notifier tous les utilisateurs dont l'assurance expire dans 7 jours exactement.</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Envoyer des notifications selon les alertes qui expirent</h5>
                            <span class="badge badge-danger" data-toggle="tooltip" data-placement="top" title="Envoyez des notifications aux utilisateurs dont les alertes expirent dans X jours">
                                <i class="nav-icon i-Alert"></i> Alertes
                            </span>
                        </div>
                        <p class="text-muted mb-3">
                            <i class="nav-icon i-Info"></i> 
                            <strong>Usage :</strong> Envoyez automatiquement des notifications aux utilisateurs dont les alertes d'un type spécifique expirent dans un nombre de jours donné. Par exemple : notifier tous les utilisateurs dont l'assurance expire dans 7 jours.
                        </p>
                        <form action="{{ route('notifications.by-alert.send') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="type_alert_id">
                                    Type d'alerte <span class="text-danger">*</span>
                                    <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Sélectionnez le type d'alerte pour lequel vous souhaitez envoyer des notifications"></i>
                                </label>
                                <select class="form-control" id="type_alert_id" name="type_alert_id" required>
                                    <option value="">-- Sélectionner un type d'alerte --</option>
                                    @foreach($type_alerts as $type_alert)
                                        <option value="{{ $type_alert->id }}">{{ $type_alert->libelle }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    <i class="nav-icon i-Info"></i> Type d'alerte concerné (Assurance, Vidange, Visite technique, Contrôle technique, etc.)
                                </small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="days_before_expiration">
                                    Nombre de jours avant expiration <span class="text-danger">*</span>
                                    <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Nombre de jours avant l'expiration de l'alerte. Les utilisateurs dont l'alerte expire exactement dans ce nombre de jours recevront la notification."></i>
                                </label>
                                <input type="number" class="form-control" id="days_before_expiration" name="days_before_expiration" 
                                       placeholder="Ex: 7, 15, 30" min="0" max="365" required>
                                <small class="form-text text-muted">
                                    <i class="nav-icon i-Info"></i> 
                                    Exemple : <strong>7</strong> = notifier les utilisateurs dont l'alerte expire dans 7 jours exactement
                                    <br>Exemple : <strong>0</strong> = notifier les utilisateurs dont l'alerte expire aujourd'hui
                                    <br>Exemple : <strong>30</strong> = notifier les utilisateurs dont l'alerte expire dans 30 jours
                                </small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="title_alerts">
                                    Titre <span class="text-danger">*</span>
                                    <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Titre de la notification visible dans la barre de notification (max 255 caractères)"></i>
                                </label>
                                <input type="text" class="form-control" id="title_alerts" name="title"
                                       placeholder="Ex: Rappel d'expiration, Alerte importante..." maxlength="255" required>
                                <small class="form-text text-muted">Apparaît dans la barre de notification de l'appareil</small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="body_alerts">
                                    Message <span class="text-danger">*</span>
                                    <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Contenu principal de la notification visible par les utilisateurs concernés"></i>
                                </label>
                                <textarea class="form-control" id="body_alerts" name="body" rows="4"
                                          placeholder="Ex: Votre assurance expire dans 7 jours, pensez à la renouveler..." required></textarea>
                                <small class="form-text text-muted">Contenu principal visible par les utilisateurs dont l'alerte expire dans le nombre de jours spécifié</small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="data_alerts">
                                    Données supplémentaires (JSON optionnel)
                                    <i class="nav-icon i-Information text-info ml-1" data-toggle="tooltip" data-placement="top" title="Données personnalisées pour l'application mobile (navigation, actions, paramètres). Format JSON valide requis."></i>
                                </label>
                                <textarea class="form-control" id="data_alerts" name="data_json" rows="3"
                                          placeholder='{"action": "open_screen", "screen": "alerts", "alert_type": "assurance"}'></textarea>
                                <small class="form-text text-muted">
                                    <i class="nav-icon i-Info"></i> Format JSON valide. Utilisé pour la navigation et les actions dans l'application mobile.
                                    <br>Exemple : <code>{"action": "open_screen", "screen": "alerts"}</code>
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="nav-icon i-Send"></i> Envoyer les notifications
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion des données JSON dans le formulaire
$(document).ready(function() {
    $('form[action*="notifications-by-alert"]').on('submit', function(e) {
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

