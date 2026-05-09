# Configuration Firebase Push Notifications

## Installation

### 1. Installer le package Firebase Admin SDK

```bash
composer require kreait/firebase-php
```

### 2. Vérifier le fichier de credentials

Le fichier de credentials Firebase a été placé dans :
```
storage/app/firebase/touauto-4f8df-firebase-adminsdk-fbsvc-da305bbcbd.json
```

Assurez-vous que ce fichier existe et est accessible.

### 3. Accéder à l'interface

Une fois installé, accédez à l'interface de notifications via :
```
/notifications
```

## Utilisation

L'interface propose 4 types d'envoi de notifications :

### 1. Notification Individuelle
- Envoie une notification à un seul appareil
- Nécessite le token FCM de l'appareil

### 2. Notifications Groupées (Tokens)
- Envoie une notification à plusieurs appareils
- Entrez les tokens FCM séparés par des virgules ou un par ligne

### 3. Notification par Topic
- Envoie une notification à tous les appareils abonnés à un topic
- Exemple de topics : `all_users`, `premium_users`, etc.

### 4. Notifications par Topics
- Envoie une notification à plusieurs topics en même temps
- Entrez les noms des topics séparés par des virgules ou un par ligne

## Données supplémentaires (JSON)

Vous pouvez ajouter des données personnalisées au format JSON :
```json
{
  "action": "open_screen",
  "screen": "profile",
  "id": "123"
}
```

## Notes importantes

- Les tokens FCM doivent être valides et actifs
- Les topics doivent être créés et les appareils doivent s'y abonner
- Le format JSON des données supplémentaires doit être valide
- Les notifications sont envoyées en temps réel

## Configuration de la base de données

Pour utiliser la sélection d'utilisateurs dans l'interface, vous devez avoir un champ `fcm_token` dans la table `users`. Si ce champ n'existe pas, créez une migration :

```bash
php artisan make:migration add_fcm_token_to_users_table
```

Puis dans la migration :

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('fcm_token')->nullable()->after('telephone');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('fcm_token');
    });
}
```

Exécutez la migration :

```bash
php artisan migrate
```

L'application mobile doit enregistrer le token FCM dans ce champ lors de l'authentification ou de l'inscription.

## Structure des fichiers créés

- `app/Services/FirebaseNotificationService.php` - Service pour gérer les notifications
- `app/Http/Controllers/NotificationController.php` - Contrôleur pour l'interface
- `resources/views/notifications/index.blade.php` - Interface utilisateur
- Routes ajoutées dans `routes/web.php`

