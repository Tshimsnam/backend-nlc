# Documentation API - Neuro Learning Center (NLC)

## Vue d'ensemble

Cette API permet de gérer un centre d'apprentissage neurologique avec les fonctionnalités suivantes :
- Gestion des utilisateurs (parents, éducateurs, spécialistes, super-teachers, réceptionnistes)
- Gestion des enfants
- Gestion des programmes personnalisés
- Gestion des cours
- Gestion des rendez-vous
- Système de messagerie interne
- Rapports et évaluations
- Notifications
- Dossiers médicaux et éducatifs
- Paramètres du système

## Base URL
```
http://localhost:8000/api
```

## Authentication

Toutes les routes (sauf `/login`, `/register` et `/set-password`) nécessitent une authentification via Sanctum.

### Headers requis
```
Authorization: Bearer {token}
X-API-SECRET: {votre_secret_api}
Content-Type: application/json
Accept: application/json
```

## ⚠️ Restrictions de Suppression

**IMPORTANT:** Toutes les routes `DELETE` sont réservées uniquement aux utilisateurs ayant le rôle `admin`. 

Si un utilisateur non-admin tente de supprimer une ressource, il recevra :
```json
{
  "message": "Accès refusé. Seuls les administrateurs peuvent effectuer cette action."
}
```
**Code HTTP:** `403 Forbidden`

## Routes API

### Authentification

#### Inscription
```http
POST /api/users
```
**Headers:** `X-API-SECRET` requis

**Body:**
```json
{
  "name": "John Doe",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "role": "parent",
  "phone": "+33123456789",
  "is_active": true
}
```

#### Connexion
```http
POST /api/login
```
**Headers:** `X-API-SECRET` requis

**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Déconnexion
```http
POST /api/logout
```
**Auth:** Requis

---

### Enfants (Children)

#### Lister tous les enfants
```http
GET /api/children
```
**Paramètres de requête:**
- `parent_id` (optionnel) - Filtrer par parent
- `status` (optionnel) - Filtrer par statut (active, inactive, graduated, transferred)

#### Créer un enfant
```http
POST /api/children
```
**Body:**
```json
{
  "first_name": "Sophie",
  "last_name": "Martin",
  "date_of_birth": "2018-05-15",
  "parent_id": 1,
  "medical_info": "Aucune allergie",
  "special_needs": "Troubles du spectre autistique",
  "status": "active"
}
```

#### Voir un enfant
```http
GET /api/children/{id}
```

#### Mettre à jour un enfant
```http
PUT /api/children/{id}
PATCH /api/children/{id}
```

#### Supprimer un enfant
```http
DELETE /api/children/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Programmes

#### Lister tous les programmes
```http
GET /api/programs
```
**Paramètres de requête:**
- `child_id` (optionnel)
- `status` (optionnel) - pending, approved, rejected, active, completed
- `created_by` (optionnel)

#### Créer un programme
```http
POST /api/programs
```
**Body:**
```json
{
  "title": "Programme d'apprentissage du langage",
  "description": "Programme intensif...",
  "child_id": "uuid-child",
  "created_by": 1,
  "status": "pending",
  "start_date": "2025-01-01",
  "end_date": "2025-06-30",
  "objectives": [
    "Améliorer la communication",
    "Augmenter le vocabulaire"
  ]
}
```

#### Voir un programme
```http
GET /api/programs/{id}
```

#### Mettre à jour un programme
```http
PUT /api/programs/{id}
PATCH /api/programs/{id}
```

#### Supprimer un programme
```http
DELETE /api/programs/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Cours

#### Lister tous les cours
```http
GET /api/courses
```
**Paramètres de requête:**
- `program_id` (optionnel)
- `educator_id` (optionnel)
- `status` (optionnel) - scheduled, in_progress, completed, cancelled, rescheduled

#### Créer un cours
```http
POST /api/courses
```
**Body:**
```json
{
  "title": "Séance de langage",
  "description": "Exercices de prononciation",
  "program_id": "uuid-program",
  "educator_id": 2,
  "duration_minutes": 60,
  "materials": ["Cartes images", "Miroir"],
  "objectives": ["Prononciation des sons"],
  "status": "scheduled",
  "scheduled_at": "2025-01-15 10:00:00"
}
```

#### Voir un cours
```http
GET /api/courses/{id}
```

#### Mettre à jour un cours
```http
PUT /api/courses/{id}
PATCH /api/courses/{id}
```

#### Supprimer un cours
```http
DELETE /api/courses/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Rendez-vous

#### Lister tous les rendez-vous
```http
GET /api/appointments
```
**Paramètres de requête:**
- `child_id` (optionnel)
- `professional_id` (optionnel)
- `appointment_type` (optionnel) - consultation, therapy, evaluation, follow_up, parent_meeting
- `status` (optionnel) - scheduled, confirmed, in_progress, completed, cancelled, no_show

#### Créer un rendez-vous
```http
POST /api/appointments
```
**Body:**
```json
{
  "child_id": "uuid-child",
  "professional_id": 3,
  "appointment_type": "consultation",
  "scheduled_at": "2025-01-20 14:00:00",
  "duration_minutes": 60,
  "status": "scheduled",
  "notes": "Première consultation",
  "location": "Cabinet A"
}
```

#### Voir un rendez-vous
```http
GET /api/appointments/{id}
```

#### Mettre à jour un rendez-vous
```http
PUT /api/appointments/{id}
PATCH /api/appointments/{id}
```

#### Supprimer un rendez-vous
```http
DELETE /api/appointments/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Messages

#### Lister tous les messages
```http
GET /api/messages
```
**Paramètres de requête:**
- `sender_id` (optionnel)
- `recipient_id` (optionnel)
- `is_read` (optionnel) - true/false
- `priority` (optionnel) - low, normal, high, urgent

#### Envoyer un message
```http
POST /api/messages
```
**Body:**
```json
{
  "sender_id": 1,
  "recipient_id": 2,
  "subject": "Demande de rendez-vous",
  "content": "Bonjour, je souhaiterais...",
  "priority": "normal",
  "attachments": []
}
```

#### Voir un message
```http
GET /api/messages/{id}
```
*Note: Marque automatiquement le message comme lu*

#### Mettre à jour un message
```http
PUT /api/messages/{id}
PATCH /api/messages/{id}
```

#### Supprimer un message
```http
DELETE /api/messages/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Rapports

#### Lister tous les rapports
```http
GET /api/reports
```
**Paramètres de requête:**
- `child_id` (optionnel)
- `author_id` (optionnel)
- `report_type` (optionnel) - progress, incident, evaluation, medical, behavioral, academic
- `is_confidential` (optionnel) - true/false

#### Créer un rapport
```http
POST /api/reports
```
**Body:**
```json
{
  "child_id": "uuid-child",
  "author_id": 1,
  "report_type": "progress",
  "title": "Rapport de progression - Janvier 2025",
  "content": "L'enfant a montré des progrès...",
  "observations": {
    "langage": "Amélioration significative",
    "social": "Interactions positives"
  },
  "recommendations": "Continuer les exercices quotidiens",
  "is_confidential": false
}
```

#### Voir un rapport
```http
GET /api/reports/{id}
```

#### Mettre à jour un rapport
```http
PUT /api/reports/{id}
PATCH /api/reports/{id}
```

#### Supprimer un rapport
```http
DELETE /api/reports/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Notifications

#### Lister toutes les notifications
```http
GET /api/notifications
```
**Paramètres de requête:**
- `user_id` (optionnel)
- `type` (optionnel) - appointment, message, report, system, reminder, alert
- `is_read` (optionnel) - true/false

#### Créer une notification
```http
POST /api/notifications
```
**Body:**
```json
{
  "user_id": 1,
  "title": "Nouveau message",
  "message": "Vous avez reçu un nouveau message",
  "type": "message",
  "action_url": "/messages/123",
  "metadata": {
    "message_id": "123"
  }
}
```

#### Voir une notification
```http
GET /api/notifications/{id}
```
*Note: Marque automatiquement la notification comme lue*

#### Mettre à jour une notification
```http
PUT /api/notifications/{id}
PATCH /api/notifications/{id}
```

#### Supprimer une notification
```http
DELETE /api/notifications/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Dossiers

#### Lister tous les dossiers
```http
GET /api/dossiers
```
**Paramètres de requête:**
- `child_id` (optionnel)

#### Créer un dossier
```http
POST /api/dossiers
```
**Body:**
```json
{
  "child_id": "uuid-child",
  "medical_history": [
    {
      "date": "2020-01-15",
      "event": "Diagnostic initial"
    }
  ],
  "allergies": ["Arachides", "Lactose"],
  "medications": [
    {
      "name": "Vitamine D",
      "dosage": "1000 UI/jour"
    }
  ],
  "emergency_contacts": [
    {
      "name": "Marie Martin",
      "relation": "Mère",
      "phone": "+33123456789"
    }
  ],
  "educational_goals": [
    "Autonomie personnelle",
    "Compétences sociales"
  ],
  "behavioral_notes": "Calme et coopératif",
  "documents": []
}
```

#### Voir un dossier
```http
GET /api/dossiers/{id}
```

#### Mettre à jour un dossier
```http
PUT /api/dossiers/{id}
PATCH /api/dossiers/{id}
```

#### Supprimer un dossier
```http
DELETE /api/dossiers/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

### Paramètres

#### Lister tous les paramètres
```http
GET /api/settings
```
**Paramètres de requête:**
- `category` (optionnel) - general, security, notifications, backup, organization
- `is_public` (optionnel) - true/false

#### Créer un paramètre
```http
POST /api/settings
```
**Body:**
```json
{
  "key": "app_timezone",
  "value": {
    "timezone": "Europe/Paris"
  },
  "category": "general",
  "description": "Fuseau horaire de l'application",
  "is_public": true
}
```

#### Voir un paramètre
```http
GET /api/settings/{id}
```

#### Mettre à jour un paramètre
```http
PUT /api/settings/{id}
PATCH /api/settings/{id}
```

#### Supprimer un paramètre
```http
DELETE /api/settings/{id}
```
**🔒 Réservé aux Administrateurs uniquement**

---

## Rôles Utilisateurs

L'application supporte les rôles suivants :
- `admin` - Administrateur système
- `educator` - Éducateur
- `specialist` - Spécialiste
- `super-teacher` - Super enseignant (créateur de programmes)
- `receptionist` - Réceptionniste
- `parent` - Parent d'un enfant

## Installation et Migration

### 1. Exécuter les migrations
```bash
php artisan migrate
```

### 2. Exécuter les seeders
```bash
php artisan db:seed
```

Ou pour exécuter des seeders spécifiques :
```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=SettingSeeder
php artisan db:seed --class=ChildSeeder
php artisan db:seed --class=ProgramSeeder
```

### 3. Réinitialiser et réensemencer la base de données
```bash
php artisan migrate:fresh --seed
```

## Codes de Statut HTTP

- `200 OK` - Requête réussie
- `201 Created` - Ressource créée avec succès
- `422 Unprocessable Entity` - Erreurs de validation
- `404 Not Found` - Ressource non trouvée
- `401 Unauthorized` - Non authentifié
- `403 Forbidden` - Non autorisé

## Format des Réponses

### Succès
```json
{
  "message": "Opération réussie",
  "data": {
    // données de la ressource
  }
}
```

### Erreur de validation
```json
{
  "errors": {
    "field_name": [
      "Message d'erreur"
    ]
  }
}
```

## Notes Importantes

1. Tous les UUID sont générés automatiquement pour les tables qui les utilisent (children, programs, courses, appointments, messages, reports, notifications, dossiers, settings)
2. Les timestamps `created_at` et `updated_at` sont gérés automatiquement
3. Les champs JSON sont automatiquement encodés/décodés par Laravel
4. La pagination par défaut retourne 15 éléments par page
5. Les relations sont chargées automatiquement dans les réponses pour faciliter l'utilisation

## Support

Pour toute question ou problème, veuillez contacter l'équipe de développement.

