# Guide de Configuration - Système NLC (Neuro Learning Center)

## 📋 Vue d'ensemble

Ce système de gestion pour centre d'apprentissage neurologique a été entièrement configuré avec :

- ✅ **10 migrations de base de données** complètes
- ✅ **9 modèles Eloquent** avec toutes les relations
- ✅ **9 contrôleurs API** avec méthodes CRUD complètes
- ✅ **Routes API** organisées et documentées
- ✅ **3 seeders** avec données de test
- ✅ **Documentation API** complète

## 🗄️ Structure de la Base de Données

### Tables Créées

1. **users** (modifiée)
   - Gestion des utilisateurs avec 6 rôles différents
   - Champs: first_name, last_name, role, phone, is_active

2. **children**
   - Gestion des enfants du centre
   - Relations: parent (User), programs, appointments, reports, dossier

3. **programs**
   - Programmes personnalisés créés par Super Teachers
   - Relations: child, creator (User), courses

4. **courses**
   - Cours individuels dans les programmes
   - Relations: program, educator (User)

5. **appointments**
   - Rendez-vous entre enfants et professionnels
   - Relations: child, professional (User)

6. **messages**
   - Système de messagerie interne
   - Relations: sender (User), recipient (User)

7. **reports**
   - Rapports sur les enfants
   - Relations: child, author (User)

8. **notifications**
   - Notifications système
   - Relations: user

9. **dossiers**
   - Dossiers médicaux et éducatifs (1:1 avec enfant)
   - Relations: child

10. **settings**
    - Configuration de l'application

## 📦 Fichiers Créés/Modifiés

### Migrations (database/migrations/)
```
2025_10_16_164314_create_children_table.php
2025_10_16_164340_create_programs_table.php
2025_10_16_164450_create_courses_table.php
2025_10_16_164456_create_appointments_table.php
2025_10_16_164514_create_messages_table.php
2025_10_16_164524_create_reports_table.php
2025_10_16_164528_create_notifications_table.php
2025_10_16_164701_create_dossiers_table.php
2025_10_16_164704_create_settings_table.php
2025_10_16_164712_modify_users_table_for_nlc_system.php
```

### Modèles (app/Models/)
```
Child.php           - Gestion des enfants
Program.php         - Programmes personnalisés
Course.php          - Cours individuels
Appointment.php     - Rendez-vous
Message.php         - Messages internes
Report.php          - Rapports
Notification.php    - Notifications
Dossier.php         - Dossiers médicaux
Setting.php         - Paramètres système
User.php            - Modifié avec nouvelles relations
```

### Contrôleurs (app/Http/Controllers/)
```
ChildController.php
ProgramController.php
CourseController.php
AppointmentController.php
MessageController.php
ReportController.php
NotificationController.php
DossierController.php
SettingController.php
```

Chaque contrôleur contient :
- `index()` - Liste avec filtres
- `store()` - Création avec validation
- `show()` - Affichage détaillé
- `update()` - Modification
- `destroy()` - Suppression

### Seeders (database/seeders/)
```
ChildSeeder.php      - Enfants de test
ProgramSeeder.php    - Programmes de test
SettingSeeder.php    - Paramètres système
DatabaseSeeder.php   - Modifié pour inclure tous les seeders
```

### Routes (routes/api.php)
Toutes les routes API sont configurées avec:
- Authentication Sanctum
- Middleware de vérification
- Routes resourceful pour chaque entité

### Documentation
```
API_DOCUMENTATION.md - Documentation complète de l'API
NLC_SETUP_GUIDE.md   - Ce guide
```

## 🚀 Installation et Démarrage

### Étape 1: Exécuter les migrations

```bash
php artisan migrate
```

Cette commande va créer toutes les tables dans votre base de données.

### Étape 2: Exécuter les seeders

```bash
php artisan db:seed
```

Cela va créer :
- Les rôles système
- Un administrateur par défaut
- Des paramètres système
- Des enfants de test
- Des programmes de test

### Étape 3: Réinitialisation complète (optionnel)

Si vous voulez repartir de zéro :

```bash
php artisan migrate:fresh --seed
```

⚠️ **Attention**: Cette commande supprime TOUTES les données existantes!

## 🔑 Rôles Utilisateurs

Le système supporte 6 rôles :

1. **admin** - Administrateur système
   - Accès complet à toutes les fonctionnalités

2. **educator** - Éducateur
   - Gestion des cours assignés
   - Consultation des programmes

3. **specialist** - Spécialiste
   - Gestion des rendez-vous
   - Création de rapports

4. **super-teacher** - Super Enseignant
   - Création et validation de programmes
   - Supervision des cours

5. **receptionist** - Réceptionniste
   - Gestion des rendez-vous
   - Consultation des informations générales

6. **parent** - Parent
   - Consultation des informations de leurs enfants
   - Messagerie avec les professionnels

## 📊 Relations Entre les Tables

```
User (Parent)
  └── hasMany Children
        └── hasOne Dossier
        └── hasMany Programs
              └── hasMany Courses
                    └── belongsTo Educator (User)
        └── hasMany Appointments
              └── belongsTo Professional (User)
        └── hasMany Reports
              └── belongsTo Author (User)

User
  └── hasMany SentMessages
  └── hasMany ReceivedMessages
  └── hasMany Notifications
```

## 🔧 Configuration API

### Headers Requis

Pour toutes les requêtes authentifiées :

```http
Authorization: Bearer {token}
X-API-SECRET: {votre_secret_api}
Content-Type: application/json
Accept: application/json
```

### Obtenir un Token

1. **Créer un utilisateur** (avec X-API-SECRET):
```http
POST /api/users
```

2. **Se connecter** (avec X-API-SECRET):
```http
POST /api/login
```

La réponse contiendra le token Sanctum à utiliser.

## 📝 Exemples d'Utilisation

### Créer un Enfant

```bash
curl -X POST http://localhost:8000/api/children \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Sophie",
    "last_name": "Martin",
    "date_of_birth": "2018-05-15",
    "parent_id": 1,
    "medical_info": "Aucune allergie",
    "special_needs": "Troubles du spectre autistique",
    "status": "active"
  }'
```

### Créer un Programme

```bash
curl -X POST http://localhost:8000/api/programs \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Programme de langage",
    "description": "Programme intensif",
    "child_id": "uuid-child",
    "created_by": 1,
    "status": "pending",
    "start_date": "2025-01-01",
    "end_date": "2025-06-30",
    "objectives": ["Améliorer la communication"]
  }'
```

### Lister les Rendez-vous d'un Enfant

```bash
curl -X GET "http://localhost:8000/api/appointments?child_id=uuid-child" \
  -H "Authorization: Bearer {token}"
```

## 🔒 Sécurité et Permissions

### Protection des Suppressions

**IMPORTANT:** Toutes les routes `DELETE` sont réservées uniquement aux utilisateurs ayant le rôle `admin`.

Un middleware `AdminOnly` a été créé et appliqué à toutes les routes de suppression :
- `DELETE /api/children/{id}`
- `DELETE /api/programs/{id}`
- `DELETE /api/courses/{id}`
- `DELETE /api/appointments/{id}`
- `DELETE /api/messages/{id}`
- `DELETE /api/reports/{id}`
- `DELETE /api/notifications/{id}`
- `DELETE /api/dossiers/{id}`
- `DELETE /api/settings/{id}`

Si un utilisateur non-admin tente de supprimer une ressource, il recevra une erreur `403 Forbidden` :
```json
{
  "message": "Accès refusé. Seuls les administrateurs peuvent effectuer cette action."
}
```

### Middleware Créé

**Fichier:** `app/Http/Middleware/AdminOnly.php`

Ce middleware vérifie que l'utilisateur connecté a le rôle `admin` avant d'autoriser l'action de suppression.

**Enregistrement:** Le middleware est enregistré dans `bootstrap/app.php` avec l'alias `admin.only`.

## 🎯 Fonctionnalités Principales

### 1. Gestion des Enfants
- Création et modification des fiches enfants
- Suivi du statut (actif, inactif, diplômé, transféré)
- Informations médicales et besoins spéciaux
- Dossier médical et éducatif complet

### 2. Programmes Personnalisés
- Création par Super Teachers
- Workflow de validation (pending → approved/rejected → active → completed)
- Objectifs personnalisés
- Dates de début et fin

### 3. Cours
- Assignation d'éducateurs
- Planning et durée
- Matériel nécessaire
- Suivi du statut

### 4. Rendez-vous
- 5 types : consultation, thérapie, évaluation, suivi, réunion parent
- Gestion du statut complet
- Localisation et notes

### 5. Messagerie Interne
- Communication entre tous les utilisateurs
- Niveaux de priorité
- Pièces jointes (JSON)
- Statut de lecture automatique

### 6. Rapports
- 6 types : progrès, incident, évaluation, médical, comportemental, académique
- Observations structurées
- Recommandations
- Niveau de confidentialité

### 7. Notifications
- 6 types : rendez-vous, message, rapport, système, rappel, alerte
- URLs d'action
- Métadonnées personnalisées
- Marquage automatique comme lu

### 8. Dossiers
- Un dossier unique par enfant
- Historique médical complet
- Allergies et médicaments
- Contacts d'urgence
- Objectifs éducatifs
- Notes comportementales
- Documents attachés

### 9. Paramètres Système
- Configuration centralisée
- 5 catégories : général, sécurité, notifications, sauvegarde, organisation
- Paramètres publics/privés

## 🛡️ Validation des Données

Tous les contrôleurs incluent une validation complète :
- Types de données
- Longueurs maximales
- Champs requis
- Relations (foreign keys)
- Valeurs ENUM
- Dates (format et cohérence)

## 📈 Pagination

Toutes les listes sont paginées par défaut :
- 15 éléments par page
- Format Laravel standard

Exemple de réponse :
```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 15,
  "total": 42,
  "last_page": 3
}
```

## 🔍 Filtres Disponibles

Chaque endpoint `index` supporte des filtres spécifiques :

- **Children**: `parent_id`, `status`
- **Programs**: `child_id`, `status`, `created_by`
- **Courses**: `program_id`, `educator_id`, `status`
- **Appointments**: `child_id`, `professional_id`, `appointment_type`, `status`
- **Messages**: `sender_id`, `recipient_id`, `is_read`, `priority`
- **Reports**: `child_id`, `author_id`, `report_type`, `is_confidential`
- **Notifications**: `user_id`, `type`, `is_read`
- **Dossiers**: `child_id`
- **Settings**: `category`, `is_public`

## 🎨 Bonnes Pratiques

### 1. UUID vs ID
- Les tables principales utilisent des UUID pour plus de sécurité
- Les relations avec `users` utilisent des ID classiques

### 2. Soft Deletes
Vous pouvez ajouter le soft delete si nécessaire :
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasUuids, SoftDeletes;
}
```

### 3. Eager Loading
Les contrôleurs chargent automatiquement les relations nécessaires pour éviter le problème N+1.

### 4. Timestamps
Tous les modèles ont `created_at` et `updated_at` gérés automatiquement.

## 🐛 Résolution de Problèmes

### Erreur: "Class not found"
```bash
composer dump-autoload
```

### Erreur de migration
```bash
php artisan migrate:rollback
php artisan migrate
```

### Réinitialiser les seeders
```bash
php artisan db:seed --class=NomDuSeeder
```

### Vérifier les routes
```bash
php artisan route:list
```

## 📚 Ressources Supplémentaires

- [Documentation Laravel](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- API_DOCUMENTATION.md - Documentation complète des endpoints

## 🎉 Prochaines Étapes

1. Tester les endpoints avec Postman ou Insomnia
2. Ajouter des middleware de permissions spécifiques aux rôles
3. Implémenter l'upload de fichiers pour les documents
4. Ajouter des événements et listeners pour les notifications automatiques
5. Créer des factories pour générer des données de test
6. Ajouter des tests unitaires et d'intégration
7. Implémenter la recherche avancée
8. Ajouter des statistiques et tableaux de bord

## ✅ Checklist de Vérification

- [x] Migrations créées
- [x] Modèles avec relations
- [x] Contrôleurs CRUD
- [x] Routes API configurées
- [x] Seeders fonctionnels
- [x] Documentation complète
- [x] Middleware AdminOnly créé
- [x] Protection des suppressions (admin uniquement)
- [ ] Tests écrits
- [ ] Permissions par rôle (pour autres actions)
- [ ] Upload de fichiers
- [ ] Notifications en temps réel

---

**Développé pour le Neuro Learning Center (NLC)**

Pour toute question, consultez la documentation API ou contactez l'équipe de développement.

