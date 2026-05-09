# Documentation - Interface d'Envoi de Notifications Push Firebase

## Vue d'ensemble

Cette interface permet d'envoyer des notifications push Firebase à différents types de destinataires. Elle propose 4 modes d'envoi via des onglets distincts.

---

## 📱 Onglet 1 : Notification Individuelle

### Rôle
Envoyer une notification à **un seul utilisateur** spécifique.

### Quand l'utiliser
- Envoi personnalisé à un utilisateur précis
- Notifications ciblées (ex: confirmation de commande, message privé)
- Communication directe avec un utilisateur

### Champs du formulaire

#### 1. **Sélectionner un utilisateur** (Obligatoire)
- **Type** : Liste déroulante avec recherche (Select2)
- **Rôle** : Choisir l'utilisateur destinataire unique
- **Fonctionnalités** :
  - Recherche en temps réel par nom, prénom, email ou téléphone
  - Affichage des informations utilisateur (nom, prénom, email/téléphone)
  - Liste filtrée : uniquement les utilisateurs avec un token FCM valide
- **Note** : Si aucun utilisateur n'a de token FCM, le champ est désactivé

#### 2. **Titre** (Obligatoire)
- **Type** : Champ texte
- **Rôle** : Titre de la notification qui apparaîtra dans la barre de notification
- **Limite** : 255 caractères maximum
- **Exemple** : "Nouvelle commande", "Message important", "Rappel"

#### 3. **Message** (Obligatoire)
- **Type** : Zone de texte multiligne (4 lignes)
- **Rôle** : Contenu principal de la notification
- **Exemple** : "Votre commande #12345 a été confirmée et sera livrée demain."

#### 4. **Données supplémentaires (JSON optionnel)**
- **Type** : Zone de texte multiligne (3 lignes)
- **Rôle** : Données personnalisées envoyées avec la notification (non affichées à l'utilisateur)
- **Format** : JSON valide
- **Utilisation** : 
  - Navigation vers un écran spécifique de l'application
  - Passage de paramètres à l'application
  - Actions personnalisées
- **Exemple** :
  ```json
  {
    "action": "open_screen",
    "screen": "order_details",
    "order_id": "12345"
  }
  ```

---

## 👥 Onglet 2 : Notifications Groupées (Tokens)

### Rôle
Envoyer une notification à **plusieurs utilisateurs** sélectionnés simultanément.

### Quand l'utiliser
- Campagne marketing ciblée
- Notification à un groupe d'utilisateurs spécifiques
- Annonces importantes pour certains utilisateurs
- Promotions pour des utilisateurs sélectionnés

### Champs du formulaire

#### 1. **Sélectionner les utilisateurs** (Obligatoire)
- **Type** : Liste déroulante avec checkboxes multiples
- **Rôle** : Sélectionner un ou plusieurs utilisateurs destinataires
- **Fonctionnalités** :
  - **Barre de recherche** : Filtrer les utilisateurs en temps réel
  - **Checkboxes** : Sélection multiple avec cases à cocher
  - **Boutons d'action** :
    - **"Tout sélectionner"** : Sélectionne tous les utilisateurs visibles (y compris après filtrage)
    - **"Tout désélectionner"** : Désélectionne tous les utilisateurs
  - **Compteur dynamique** : Affiche le nombre d'utilisateurs sélectionnés
  - **Affichage** : Nom, prénom, email ou téléphone pour chaque utilisateur
- **Interface** :
  - Dropdown avec scroll automatique si beaucoup d'utilisateurs
  - Badge indiquant le nombre total d'utilisateurs disponibles
  - Compteur en temps réel des sélections

#### 2. **Titre** (Obligatoire)
- **Type** : Champ texte
- **Rôle** : Titre de la notification pour tous les destinataires
- **Limite** : 255 caractères maximum

#### 3. **Message** (Obligatoire)
- **Type** : Zone de texte multiligne (4 lignes)
- **Rôle** : Contenu de la notification envoyé à tous les utilisateurs sélectionnés

#### 4. **Données supplémentaires (JSON optionnel)**
- **Type** : Zone de texte multiligne (3 lignes)
- **Rôle** : Données personnalisées communes à tous les destinataires
- **Format** : JSON valide

---

## 🎯 Onglet 3 : Notification par Topic

### Rôle
Envoyer une notification à **tous les appareils abonnés à un topic Firebase**.

### Quand l'utiliser
- Notifications pour tous les utilisateurs d'un groupe (ex: premium_users, all_users)
- Annonces générales pour une catégorie d'utilisateurs
- Mises à jour importantes pour un segment d'utilisateurs

### Qu'est-ce qu'un Topic ?
Un topic Firebase est un canal de diffusion auquel les appareils mobiles peuvent s'abonner. Par exemple :
- `all_users` : Tous les utilisateurs
- `premium_users` : Utilisateurs premium
- `android_users` : Utilisateurs Android
- `ios_users` : Utilisateurs iOS

### Champs du formulaire

#### 1. **Nom du topic** (Obligatoire)
- **Type** : Champ texte
- **Rôle** : Nom du topic Firebase auquel envoyer la notification
- **Limite** : 255 caractères maximum
- **Exemples** : `all_users`, `premium_users`, `android_users`
- **Note** : Le topic doit exister et avoir des appareils abonnés

#### 2. **Titre** (Obligatoire)
- **Type** : Champ texte
- **Rôle** : Titre de la notification
- **Limite** : 255 caractères maximum

#### 3. **Message** (Obligatoire)
- **Type** : Zone de texte multiligne (4 lignes)
- **Rôle** : Contenu de la notification

#### 4. **Données supplémentaires (JSON optionnel)**
- **Type** : Zone de texte multiligne (3 lignes)
- **Rôle** : Données personnalisées
- **Format** : JSON valide

---

## 🎯 Onglet 4 : Notifications par Topics

### Rôle
Envoyer une notification à **plusieurs topics Firebase simultanément**.

### Quand l'utiliser
- Envoi à plusieurs groupes d'utilisateurs en une seule fois
- Campagne multi-segments
- Notifications pour différentes catégories d'utilisateurs

### Champs du formulaire

#### 1. **Noms des topics** (Obligatoire)
- **Type** : Zone de texte multiligne (4 lignes)
- **Rôle** : Liste des topics Firebase destinataires
- **Format** : 
  - Un topic par ligne, OU
  - Topics séparés par des virgules
- **Exemple** :
  ```
  all_users
  premium_users
  android_users
  ```
  ou
  ```
  all_users, premium_users, android_users
  ```

#### 2. **Titre** (Obligatoire)
- **Type** : Champ texte
- **Rôle** : Titre de la notification pour tous les topics
- **Limite** : 255 caractères maximum

#### 3. **Message** (Obligatoire)
- **Type** : Zone de texte multiligne (4 lignes)
- **Rôle** : Contenu de la notification

#### 4. **Données supplémentaires (JSON optionnel)**
- **Type** : Zone de texte multiligne (3 lignes)
- **Rôle** : Données personnalisées
- **Format** : JSON valide

---

## 🔧 Champs communs à tous les onglets

### Titre
- **Obligatoire** dans tous les onglets
- Apparaît dans la barre de notification de l'appareil
- Limité à 255 caractères
- Doit être clair et concis

### Message
- **Obligatoire** dans tous les onglets
- Contenu principal de la notification
- Visible par l'utilisateur
- Zone de texte de 4 lignes

### Données supplémentaires (JSON)
- **Optionnel** dans tous les onglets
- Données non visibles par l'utilisateur
- Utilisées par l'application mobile pour :
  - Navigation vers un écran spécifique
  - Exécution d'actions personnalisées
  - Passage de paramètres
- **Format requis** : JSON valide
- **Exemple d'utilisation** :
  ```json
  {
    "action": "open_screen",
    "screen": "profile",
    "user_id": "123",
    "type": "order_update"
  }
  ```

---

## 📊 Comparaison des onglets

| Onglet | Destinataires | Cas d'usage | Flexibilité |
|--------|---------------|-------------|-------------|
| **Individuelle** | 1 utilisateur | Communication personnalisée | ⭐⭐⭐⭐⭐ |
| **Groupées** | Plusieurs utilisateurs sélectionnés | Campagne ciblée | ⭐⭐⭐⭐ |
| **Par Topic** | Tous les abonnés d'un topic | Annonces de groupe | ⭐⭐⭐ |
| **Par Topics** | Plusieurs topics simultanément | Campagne multi-segments | ⭐⭐⭐ |

---

## ⚠️ Notes importantes

1. **Tokens FCM** : Seuls les utilisateurs avec un token FCM valide dans la base de données peuvent recevoir des notifications
2. **Topics** : Les topics doivent être créés et les appareils doivent s'y abonner via l'application mobile
3. **Format JSON** : Les données supplémentaires doivent être au format JSON valide, sinon une erreur sera affichée
4. **Validation** : Tous les champs obligatoires sont validés avant l'envoi
5. **Messages de retour** : Des messages de succès ou d'erreur s'affichent après chaque tentative d'envoi

---

## 🚀 Workflow recommandé

1. **Choisir l'onglet** approprié selon le type d'envoi
2. **Sélectionner les destinataires** (utilisateurs ou topics)
3. **Rédiger le titre** (court et accrocheur)
4. **Rédiger le message** (clair et informatif)
5. **Ajouter des données supplémentaires** si nécessaire (optionnel)
6. **Envoyer** la notification
7. **Vérifier** le message de retour (succès ou erreur)

---

## 💡 Conseils d'utilisation

- **Titre** : Gardez-le court (50-60 caractères maximum pour une meilleure visibilité)
- **Message** : Soyez concis mais informatif
- **Données JSON** : Testez le format JSON avant l'envoi pour éviter les erreurs
- **Sélection multiple** : Utilisez la recherche pour trouver rapidement des utilisateurs
- **Topics** : Vérifiez que les topics existent et ont des abonnés avant l'envoi

