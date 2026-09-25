# TooAuto Super Admin - Relais technique pour agent IA

Derniere mise a jour : 17 septembre 2026.

Ce document explique les evolutions metier recentes, leur implementation et les invariants a conserver. Il ne remplace pas l'inspection du code ni du schema de production. Certaines tables ont ete creees manuellement sur le serveur et peuvent ne pas avoir de migration locale.

## 1. Demarrage rapide

Le projet est une application Laravel 10, PHP 8.1+, Blade et MySQL. Firebase Cloud Messaging est gere par `kreait/firebase-php` 8.x.

Points d'entree utiles :

- Routes : `routes/web.php`
- Menu super admin : `resources/views/layouts/menu.blade.php`
- Layout call center : `resources/views/call-centers/layout.blade.php`
- Scheduler : `app/Console/Kernel.php`
- Configuration locale : `.env` (ne jamais documenter ou afficher ses secrets)
- Deploiement Plesk/FTP : `DEPLOIEMENT_FTP.md`

Avant toute modification :

```bash
git status --short
git diff
php artisan schedule:list
```

Le depot peut contenir du travail utilisateur non commite. Ne pas le supprimer ou le restaurer automatiquement.

## 2. Lavages et commerciaux

### Besoin couvert

La page `/demande-lavages` liste les comptes de lavage et les informations de leur station. Elle accepte les filtres texte, commercial, station, statut et periode. Depuis `/commerciaux`, l'action `Lavages` ouvre cette liste avec `commercial_id` preselectionne. Les actions des commerciaux sont regroupees dans un menu deroulant.

### Modele de donnees reel

- `lavages` contient l'administrateur du lavage : nom, prenom, mobile, email, mot de passe, statut et `created_by`.
- `station_de_lavages` contient l'etablissement : nom, contact, adresse, coordonnees, logo, statut et `created_by`.
- Dans les deux tables, `created_by` represente l'identifiant du commercial qui a enregistre le lavage.

Il n'existe pas toujours de cle etrangere directe entre ces deux tables. `DashboardController::indexDemandeLavage()` cherche donc, dans cet ordre :

1. `lavages.station_de_lavage_id` ou `lavages.station_id` ;
2. `station_de_lavages.lavage_id` ;
3. a defaut, l'egalite des deux champs `created_by`.

Cette compatibilite repose sur `Schema::hasTable()` et `Schema::hasColumn()`. La conserver tant que tous les environnements n'ont pas un schema unifie. Attention : la jointure de secours par `created_by` peut produire plusieurs lignes si un commercial a cree plusieurs comptes et plusieurs stations.

### Fichiers principaux

- `app/Http/Controllers/DashboardController.php` : `indexDemandeLavage`, `updateLavage`, `destroyLavage`, `resolveLavageStationId`.
- `resources/views/lavages/demandes.blade.php` : filtres, pagination, logo, modification et suppression.
- `resources/views/commercials/index.blade.php` : menu d'actions du commercial.
- `routes/web.php` : routes `demande-lavages.index`, `lavages.update`, `lavages.destroy`.

Le logo est stocke dans `public/station_de_lavage/logo`. La mise a jour supprime l'ancien fichier connu puis enregistre le nouveau. Le mot de passe n'est remplace que si une nouvelle valeur non vide est fournie, et il est hache avec `Hash::make()`.

La suppression actuelle efface le compte dans `lavages`, mais pas automatiquement la station associee. Ne pas modifier cette regle sans validation metier.

## 3. Call center

Le call center dispose de pages distinctes protegees par le guard `call_center`. L'approche de suivi d'appel a ete ajoutee a :

- `/call-center/station-services`
- `/call-center/station-de-lavages`

Chaque ligne permet de marquer l'entite comme appelee ou non et d'enregistrer un commentaire. Les colonnes sont :

- `call_center_deja_appele` : booleen ;
- `call_center_commentaire` : texte nullable ;
- `call_center_called_at` : date nullable, mise a `now()` lorsque l'entite est marquee appelee.

Fichiers principaux :

- `app/Http/Controllers/CallCenterSpaceController.php`
- `resources/views/call-centers/table.blade.php`
- `database/migrations/2026_08_26_000002_add_call_center_follow_up_to_station_tables.php`

Le controleur teste la presence des colonnes avant de les selectionner ou de les filtrer. Cela permet a l'interface de rester utilisable avant migration, mais sans les fonctions de suivi.

Sur `/etablissements`, `is_electrique` indique si l'etablissement accepte les vehicules electriques (`1` oui, `0` non). L'administrateur peut modifier cette valeur depuis la fiche detail et filtrer la liste sur ce critere. Le controleur et les vues verifient la presence de la colonne pour rester compatibles avant migration.

## 4. Cartes privilege et reductions

### Regle metier

Une definition de carte de reduction est liee a un forfait usager. Lorsqu'un abonnement actif correspond a ce forfait, une carte usager est generee. Ses dates de debut et de fin reprennent celles de l'abonnement. Une carte peut appliquer un pourcentage ou un montant fixe.

Chaque utilisation doit etre historisee avec :

- l'usager et sa carte ;
- l'abonnement et le forfait ;
- la reduction appliquee et les montants avant/apres ;
- `applied_by_id`, l'identifiant de l'acteur qui applique la reduction ;
- `establishment_type`, parmi `etablissement`, `lavage`, `station` ;
- `establishment_id` ;
- la date et les notes eventuelles.

### Tables

`reduction_cards` definit la carte : forfait, nom, type et valeur de reduction, description, statut et createur.

`user_reduction_cards` attribue la carte : carte, usager, abonnement, forfait, codes uniques, dates et statut. L'unicite `reduction_card_id + abonnement_usager_id` empeche une double attribution pour le meme abonnement.

`reduction_card_histories` conserve chaque utilisation. L'index polymorphe utilise le nom court `rch_establishment_idx`, car le nom genere automatiquement par Laravel depassait la limite MySQL de 64 caracteres.

La migration de reference est `database/migrations/2026_08_26_000001_create_reduction_card_tables.php`. En production, le proprietaire peut executer des requetes SQL manuellement et sans cles etrangeres. Toujours comparer la migration au schema reel avant une evolution.

### Flux de code

- `ReductionCardController` fournit trois pages paginees : definitions, cartes attribuees, historique.
- `ReductionCardService::syncForCard()` attribue ou desactive les cartes lors d'une modification de definition.
- `ReductionCardService::syncForAbonnement()` est appele lors de la creation ou modification d'un abonnement usager.
- `ReductionCardService::assignCard()` genere `card_code` et `qr_code`, puis copie les dates de l'abonnement.
- `ReductionCardService::recordUsage()` calcule le montant de reduction, le plafonne au montant initial et cree l'historique.

Routes principales : `/cartes-reduction`, `/cartes-reduction/usagers`, `/cartes-reduction/historique`. La page `/usagers/{id}` affiche les details de la carte privilege. La liste des cartes usagers affiche le telephone et permet l'activation/desactivation.

La page `/usagers` permet aussi de filtrer les usagers par forfait d'abonnement et par periode de date d'expiration. Les criteres forfait/date sont appliques au meme abonnement, et la relation chargee est contrainte pour que l'abonnement affiche corresponde au filtre.

Une definition avec historique n'est pas supprimee : elle est desactivee afin de conserver la tracabilite.

## 5. Notifications Firebase

### Interface

Le module est partage entre le super admin et le call center :

- `/notification-send` : ciblage et apercu ;
- `/notification-send/programmes` : formulaire de creation et liste paginee des campagnes ;
- `/notification-send/logs` : historique par destinataire.

Les memes chemins existent sous `/call-center`. La route `/notification-send/create` est conservee pour compatibilite, mais redirige vers `/notification-send/programmes`; il n'existe plus de page de creation separee.

Le formulaire permet : tous les usagers, plusieurs usagers choisis dans un menu Bootstrap a cases a cocher, ou le ciblage par expiration d'alerte. La liste de selection ne charge que les usagers ayant un `fcm_token` non vide.

### Architecture

- `NotificationCampaignController` gere l'autorisation, la validation, les filtres, les pages et les actions envoyer/annuler.
- `NotificationCampaignService` construit l'audience, traite les campagnes dues et journalise les envois.
- `FirebaseNotificationService` encapsule Kreait et l'appel `sendMulticast()`.
- `SendDueNotificationCampaigns` expose `notification-campaigns:send-due --limit=50`.
- `NotificationCampaign` et `NotificationCampaignLog` decrivent les tables manuelles `notification_campaigns` et `notification_campaign_logs`.

Les fichiers de vues sont dans `resources/views/notification_send`. Le formulaire se trouve directement dans `programmes.blade.php`. Ne pas recreer un ecran intermediaire sans demande explicite.

### Etats et audiences

Etats de campagne : `draft`, `scheduled`, `sending`, `sent`, `failed`, `cancelled`.

Audiences : `all_users`, `selected_users`, `alert_expiration`. Les filtres sont enregistres en JSON dans `audience_filters`. L'audience est recalculee au moment de l'envoi et exige toujours un token FCM non vide.

### Traitement de plusieurs campagnes

`NotificationCampaignService::sendDueCampaigns()` recupere jusqu'a 50 identifiants dus puis reserve chaque campagne par une mise a jour conditionnelle : son statut doit encore etre `scheduled` et sa date doit etre arrivee. Cette reservation atomique evite qu'un cron et une ouverture de page envoient deux fois la meme campagne.

Chaque campagne est traitee independamment. Une exception marque uniquement cette campagne `failed` et la boucle continue. Ne pas replacer cette logique dans le controleur ou la commande.

Les destinataires sont traites par blocs de 400. Une campagne sans cible ou avec zero succes devient `failed`; une campagne ayant au moins un succes devient `sent`, avec les compteurs de succes et d'echec.

Le controleur appelle aussi le traitement des campagnes dues sur les pages de ciblage et de programmes. C'est un mecanisme de secours; le cron reste obligatoire.

### Scheduler et cron

Laravel planifie la commande chaque minute dans `app/Console/Kernel.php` :

```php
$schedule->command('notification-campaigns:send-due --limit=50')->everyMinute();
```

Le verrou global `withoutOverlapping()` a ete retire pour ce traitement. La reservation atomique protege des doublons et permet au passage suivant de prendre une autre campagne si un envoi est long.

Le serveur doit executer :

```cron
* * * * * cd /chemin/absolu/du/projet && php artisan schedule:run >> /dev/null 2>&1
```

Cette ligne doit etre ajoutee avec `crontab -e` ou la tache planifiee Plesk. Elle ne doit jamais etre collee directement dans le shell comme une commande ordinaire.

Le credential Firebase attendu est `storage/app/firebase/touauto-4f8df-firebase-adminsdk-fbsvc-da305bbcbd.json`. Ne jamais le commiter ni l'inclure dans la documentation.

## 6. Layout SAP et sidebar

Un correctif global a ete ajoute a la fin de `public/dist-assets/css/main.css` pour stabiliser la sidebar des layouts larges, notamment `/sap/dashboard` : largeur fixe de 120 px, positionnement a gauche, contenu principal decale de 120 px et remise a zero sur mobile.

Ce CSS cible `.layout-sidebar-large`. Avant de le modifier, verifier les dashboards super admin et SAP sur ordinateur et mobile. Le fichier est un asset distribue directement; une recompilation ou un remplacement complet du theme pourrait ecraser le correctif.

## 7. Verification et tests

Verification PHP ciblee :

```bash
php -l app/Services/NotificationCampaignService.php
php -l app/Http/Controllers/NotificationCampaignController.php
php artisan view:cache
php artisan schedule:list
```

Commande de diagnostic des campagnes dues :

```bash
php artisan notification-campaigns:send-due --limit=50
```

Attention : cette commande envoie reellement toutes les campagnes dues. Avant de l'executer en production, verifier leur nombre et leur audience.

Pour tester plusieurs campagnes sans envoyer de push reel : remplacer `FirebaseNotificationService` par un faux service dans un test, creer plusieurs campagnes dues dans une transaction, appeler `sendDueCampaigns(50)`, verifier tous les statuts, puis annuler la transaction. Le test effectue lors de la correction a traite trois campagnes dans le meme passage.

`php artisan route:list` peut echouer si une classe de controleur historique referencee dans les routes est absente. Dans ce cas, verifier les routes concernees directement dans `routes/web.php` et ne pas attribuer automatiquement l'erreur au module modifie.

## 8. Deploiement manuel

Preparation locale :

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Creer l'archive en conservant `vendor` et en excluant les secrets et fichiers volatils :

```bash
zip -r tooauto-super-admin-prod.zip . \
  -x ".git/*" \
  -x ".env" \
  -x ".env.backup" \
  -x ".env.production" \
  -x "node_modules/*" \
  -x "storage/logs/*.log" \
  -x "storage/framework/cache/data/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*" \
  -x "tests/*"
```

Apres transfert et extraction sur le serveur :

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Verifier ensuite le cron, les droits en ecriture de `storage` et `bootstrap/cache`, le credential Firebase et les variables `.env`. Ne pas remplacer le `.env` de production par le fichier local.

## 9. Checklist de reprise

1. Lire ce document et le skill `.codex/skills/tooauto-maintenance/SKILL.md`.
2. Examiner `git status`, les derniers commits et les modifications non commitees.
3. Identifier le guard concerne : super admin ou `call_center`.
4. Verifier les colonnes reelles avant de modifier une requete de compatibilite.
5. Mettre a jour ensemble route, controleur/service, vue, menu et migration si necessaire.
6. Conserver pagination et filtres sur toutes les listes potentiellement volumineuses.
7. Tester le chemin nominal, les donnees absentes et les erreurs partielles.
8. Pour Firebase, ne jamais utiliser un vrai destinataire sans autorisation explicite.
9. Mettre a jour ce document si le flux ou un invariant change.

## 10. Limites connues et prochaines ameliorations possibles

- Les tables de campagnes de notification n'ont pas de migration versionnee dans le depot. Ajouter une migration de reference, compatible avec le schema de production, reduirait le risque de divergence.
- La liaison lavage/station par `created_by` est une solution de compatibilite. Une vraie cle `station_de_lavage_id` rendrait les mises a jour et suppressions non ambigues.
- Une campagne interrompue brutalement peut rester en statut `sending`. Il n'existe pas encore de mecanisme automatique de reprise, car une relance aveugle risquerait des notifications en double.
- Les logs par usager utilisent le resultat global du multicast pour leur statut. Une exploitation detaillee des resultats Kreait par token ameliorerait la precision des logs.
- La couverture automatisee est limitee. Les prochains changements sensibles devraient ajouter des tests d'integration autour du ciblage et des transitions de statut.
