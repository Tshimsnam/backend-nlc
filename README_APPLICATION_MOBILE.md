# Application Mobile - Gestion des Billets et Enregistrements

Application mobile pour la gestion des événements, permettant l'enregistrement des participants, la vérification des billets (physiques et en ligne), le contrôle d'accès et le suivi des performances des agents.

---

## 📱 Fonctionnalités

### 1. Authentification
- Connexion sécurisée pour les utilisateurs (agents, organisateurs, contrôleurs)
- Gestion des sessions avec JWT
- Déconnexion sécurisée
- Gestion des rôles et permissions

### 2. Gestion des Billets Physiques et En Ligne

#### 🔲 Billets Physiques (QR Codes Pré-générés)
- **Activation de billets physiques** : Associer un QR code physique à un participant
- Scan du QR code physique pour activation
- Validation du paiement en caisse
- Génération de billets physiques par lot pour les événements
- Identification unique via `physical_qr_id`

#### 💻 Billets En Ligne
- Billets achetés via le site web
- Paiement en ligne (MaxiCash, M-Pesa, Orange Money)
- QR code généré automatiquement après paiement
- Envoi par email du billet

### 3. Vérification et Validation de Billets

Trois méthodes de vérification :
- **Scan QR Code** : Scanner directement le QR code sur le billet (physique ou en ligne)
- **Numéro de téléphone** : Rechercher par numéro de téléphone
- **Numéro de référence** : Rechercher par référence du billet

Affichage des informations :
- Type de billet (🔲 Physique ou 💻 En ligne)
- Nom complet du participant
- Événement et détails
- Catégorie (medecin, parent, etudiant, etc.)
- Montant payé et devise
- Statut du paiement (completed, pending_cash, failed)
- Date d'achat
- **Nombre de scans** : Combien de fois le billet a été scanné
- **Premier scan** : Date et heure du premier scan
- **Dernier scan** : Date et heure du dernier scan
- **Agent validateur** : Qui a validé le billet

**Enregistrement automatique du scan :**
- Chaque scan est enregistré dans la base de données
- Le compteur de scans est incrémenté automatiquement
- L'agent qui a scanné est enregistré (`validated_by`)
- Le lieu du scan est enregistré (Entrée, VIP, etc.)
- Historique complet des scans disponible

### 4. Activation de Billets Physiques
- Scanner un QR code physique pré-généré
- Saisir les informations du participant
- Sélectionner le tarif de l'événement
- Valider le paiement en caisse
- Le billet physique est activé et associé au participant

### 5. Validation de Paiements en Caisse
- Valider les paiements en espèces pour les billets physiques
- Marquer le statut du billet comme `completed`
- Enregistrer l'agent qui a validé le paiement

### 6. Statistiques et Suivi des Agents
- **Tableau de bord personnel** : Voir ses propres statistiques
- **Total de validations** : Nombre de billets validés
- **Séparation physique/en ligne** : Statistiques distinctes
- **Revenus générés** : Total des revenus par type de billet
- **Évolution sur 30 jours** : Graphique des validations
- **Validations par événement** : Performance par événement
- **Historique des validations** : Liste des 20 dernières validations



---

## 🎯 Cas d'Utilisation

### Scénario 1 : Contrôle d'Accès avec Billet En Ligne
1. L'agent se connecte à l'application
2. Le participant présente son billet en ligne (QR code reçu par email)
3. L'agent scanne le QR code
4. L'application affiche les informations du billet (💻 En ligne)
5. L'agent valide l'accès si le paiement est confirmé
6. Le scan est enregistré avec l'agent validateur

### Scénario 2 : Activation d'un Billet Physique
1. Un participant arrive avec un QR code physique pré-imprimé
2. L'agent scanne le QR code physique
3. L'application détecte que c'est un billet physique non activé
4. L'agent saisit les informations du participant (nom, email, téléphone)
5. L'agent sélectionne le tarif de l'événement
6. Le participant effectue le paiement en caisse
7. L'agent valide le paiement
8. Le billet physique est activé et associé au participant
9. Le participant peut maintenant utiliser ce QR code pour entrer

### Scénario 3 : Validation de Paiement en Caisse
1. Un participant a acheté un billet en ligne avec paiement en caisse
2. Le participant arrive avec sa référence
3. L'agent recherche le billet par référence ou téléphone
4. L'agent vérifie que le statut est `pending_cash`
5. Le participant paie en espèces
6. L'agent valide le paiement dans l'application
7. Le statut passe à `completed`
8. L'agent qui a validé est enregistré dans `validated_by`

### Scénario 4 : Vérification Rapide par Téléphone
1. Un participant a perdu son billet physique
2. L'agent recherche par numéro de téléphone
3. Le système retrouve le billet (physique ou en ligne)
4. L'agent vérifie l'identité du participant
5. L'agent valide l'accès
6. Le scan est enregistré

### Scénario 5 : Consultation des Statistiques Agent
1. L'agent se connecte à l'application
2. L'agent accède à son tableau de bord personnel
3. L'application affiche :
   - Total de validations (physiques + en ligne)
   - Billets physiques validés (🔲 avec badge purple)
   - Billets en ligne validés (💻 avec badge blue)
   - Revenus générés par type
   - Graphique d'évolution sur 30 jours
   - Validations par événement
   - Historique des 20 dernières validations

---

## 🔧 Architecture Technique

### Stack Technologique Recommandée

#### Option 1 : React Native (Cross-platform)
```
- React Native
- React Navigation
- Axios (API calls)
- React Native Camera (QR Scanner)
- AsyncStorage (Local storage)
```

#### Option 2 : Flutter (Cross-platform)
```
- Flutter
- Provider/Riverpod (State management)
- Dio (API calls)
- qr_code_scanner (QR Scanner)
- shared_preferences (Local storage)
```

#### Option 3 : Native
- **Android** : Kotlin + Jetpack Compose
- **iOS** : Swift + SwiftUI

---

## 📡 API Backend

### Base URL
```
https://votre-api.com/api
```

### Endpoints Requis

#### 1. Authentification

**Login**
```http
POST /login
Content-Type: application/json

{
  "email": "agent@example.com",
  "password": "password123"
}

Response:
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Agent Name",
    "email": "agent@example.com",
    "role": "agent"
  }
}
```

**Logout**
```http
POST /logout
Authorization: Bearer {token}

Response:
{
  "message": "Déconnexion réussie"
}
```

#### 2. Vérification de Billet

**Par Référence**
```http
GET /api/tickets/{reference}
Authorization: Bearer {token}

Response:
{
  "reference": "ABC123XYZ",
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+243 812 345 678",
  "physical_qr_id": null,  // null = billet en ligne, non-null = billet physique
  "event": {
    "id": 1,
    "title": "Le Grand Salon de l'Autisme",
    "date": "2026-04-15",
    "end_date": "2026-04-16",
    "time": "08:00:00",
    "end_time": "16:00:00",
    "location": "Fleuve Congo Hôtel, Kinshasa"
  },
  "price": {
    "category": "medecin",
    "label": "Médecin - Événement complet",
    "amount": 50.00,
    "currency": "USD",
    "duration_type": "full_event"
  },
  "amount": 50.00,
  "currency": "USD",
  "payment_status": "completed",
  "pay_type": "maxicash",
  "validated_by": 5,  // ID de l'agent qui a validé
  "scan_count": 3,
  "first_scanned_at": "2026-02-18T10:00:00.000000Z",
  "last_scanned_at": "2026-02-18T14:30:00.000000Z",
  "created_at": "2026-02-16T10:30:00Z",
  "qr_data": "{\"reference\":\"ABC123XYZ\",\"event_id\":1}"
}
```

**Activer un Billet Physique**
```http
POST /api/physical-tickets/activate
Authorization: Bearer {token}
Content-Type: application/json

{
  "physical_qr_id": "PHY-QR-001-ABC123",
  "event_price_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+243812345678",
  "pay_type": "cash"
}

Response:
{
  "success": true,
  "message": "Billet physique activé avec succès",
  "ticket": {
    "id": 15,
    "reference": "TKT-20260218-ABC123",
    "physical_qr_id": "PHY-QR-001-ABC123",
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+243812345678",
    "event_id": 1,
    "event_price_id": 1,
    "amount": 50.00,
    "currency": "USD",
    "payment_status": "pending_cash",
    "pay_type": "cash",
    "qr_data": "{\"reference\":\"TKT-20260218-ABC123\",\"physical_qr_id\":\"PHY-QR-001-ABC123\",\"event\":\"Le Grand Salon de l'Autisme\",\"participant\":\"John Doe\",\"email\":\"john@example.com\",\"phone\":\"+243812345678\",\"amount\":\"50.00\",\"currency\":\"USD\",\"category\":\"medecin\",\"date\":\"2026-04-15\",\"location\":\"Fleuve Congo Hôtel, Kinshasa\"}",
    "event": {
      "id": 1,
      "title": "Le Grand Salon de l'Autisme",
      "date": "2026-04-15"
    },
    "price": {
      "category": "medecin",
      "label": "Médecin - Événement complet",
      "amount": 50.00
    }
  }
}
```

**Valider un Paiement en Caisse**
```http
POST /api/tickets/{reference}/validate-cash
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Paiement validé avec succès",
  "ticket": {
    "reference": "TKT-20260218-ABC123",
    "payment_status": "completed",
    "validated_by": 5,  // ID de l'agent qui a validé
    "updated_at": "2026-02-18T15:00:00.000000Z"
  }
}
```

**Scanner un Billet (Enregistre le scan)**
```http
POST /api/qr-scan
Authorization: Bearer {token}
Content-Type: application/json

{
  "qr_data": "{\"reference\":\"ABC123XYZ\",\"physical_qr_id\":null,\"event\":\"Le Grand Salon de l'Autisme\",\"participant\":\"John Doe\",\"email\":\"john@example.com\",\"phone\":\"+243812345678\",\"amount\":\"50.00\",\"currency\":\"USD\",\"category\":\"medecin\",\"date\":\"2026-04-15\",\"location\":\"Fleuve Congo Hôtel, Kinshasa\"}",
  "scan_location": "Entrée principale"
}

OU avec référence uniquement:

{
  "reference": "ABC123XYZ",
  "scan_location": "Entrée VIP"
}

OU avec téléphone:

{
  "phone": "+243812345678",
  "scan_location": "Entrée"
}

Response:
{
  "success": true,
  "message": "Billet scanné avec succès",
  "ticket": {
    "id": 1,
    "reference": "ABC123XYZ",
    "physical_qr_id": null,  // null = en ligne, non-null = physique
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+243 812 345 678",
    "category": "medecin",
    "amount": "50.00",
    "currency": "USD",
    "payment_status": "completed",
    "pay_type": "maxicash",
    "validated_by": 5,
    "scan_count": 3,
    "first_scanned_at": "2026-02-18T10:00:00.000000Z",
    "last_scanned_at": "2026-02-18T14:30:00.000000Z",
    "event": {
      "id": 1,
      "title": "Le Grand Salon de l'Autisme",
      "date": "2026-04-15",
      "time": "08:00:00",
      "location": "Fleuve Congo Hôtel, Kinshasa"
    },
    "price": {
      "label": "Médecin - Événement complet",
      "category": "medecin",
      "duration_type": "full_event",
      "amount": 50.00
    }
  },
  "scan_info": {
    "scan_count": 3,
    "is_first_scan": false,
    "last_scanned_at": "2026-02-18T14:30:00.000000Z"
  },
  "ticket_type": "online"  // "online" ou "physical"
}
```

**Historique des Scans d'un Billet**
```http
GET /api/tickets/{reference}/scans
Authorization: Bearer {token}

Response:
{
  "success": true,
  "ticket_reference": "ABC123XYZ",
  "ticket_type": "online",  // "online" ou "physical"
  "total_scans": 3,
  "scans": [
    {
      "id": 3,
      "scanned_at": "2026-02-18T14:30:00.000000Z",
      "scan_location": "Entrée principale",
      "scanned_by_user": {
        "id": 5,
        "name": "Agent Dupont",
        "email": "agent@example.com"
      }
    },
    {
      "id": 2,
      "scanned_at": "2026-02-18T12:00:00.000000Z",
      "scan_location": "Zone VIP",
      "scanned_by_user": {
        "id": 5,
        "name": "Agent Dupont",
        "email": "agent@example.com"
      }
    },
    {
      "id": 1,
      "scanned_at": "2026-02-18T10:00:00.000000Z",
      "scan_location": "Entrée principale",
      "scanned_by_user": {
        "id": 3,
        "name": "Agent Martin",
        "email": "martin@example.com"
      }
    }
  ]
}
```

**Statistiques de l'Agent Connecté**
```http
GET /api/agent/stats
Authorization: Bearer {token}

Response:
{
  "success": true,
  "agent": {
    "id": 5,
    "name": "Agent Dupont",
    "email": "agent@example.com"
  },
  "stats": {
    "total_validations": 150,
    "physical_validations": 80,
    "online_validations": 70,
    "total_revenue": 7500.00,
    "physical_revenue": 4000.00,
    "online_revenue": 3500.00,
    "average_per_validation": 50.00
  },
  "validations_evolution": [
    {
      "date": "2026-02-18",
      "total": 15,
      "physical": 8,
      "online": 7
    },
    {
      "date": "2026-02-17",
      "total": 12,
      "physical": 6,
      "online": 6
    }
    // ... 30 derniers jours
  ],
  "validations_by_event": [
    {
      "event_id": 1,
      "event_title": "Le Grand Salon de l'Autisme",
      "total": 50,
      "physical": 25,
      "online": 25,
      "revenue": 2500.00
    }
  ],
  "recent_validations": [
    {
      "reference": "ABC123XYZ",
      "ticket_type": "online",
      "full_name": "John Doe",
      "event_title": "Le Grand Salon de l'Autisme",
      "amount": 50.00,
      "currency": "USD",
      "validated_at": "2026-02-18T14:30:00.000000Z"
    }
    // ... 20 dernières validations
  ]
}
```

**Par Téléphone**
```http
GET /tickets/search?phone=+243812345678
Authorization: Bearer {token}

Response:
{
  "tickets": [
    {
      "reference": "ABC123XYZ",
      "full_name": "John Doe",
      "event": "Concert de Musique",
      "payment_status": "completed"
    }
  ]
}
```

#### 3. Enregistrement d'un Participant

**Enregistrer avec Référence**
```http
POST /tickets/{reference}/register
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Participant enregistré avec succès",
  "ticket": {
    "reference": "ABC123XYZ",
    "full_name": "John Doe",
    "registered_at": "2026-02-16T14:30:00Z"
  }
}
```

#### 4. Liste des Événements

**Obtenir les Événements Actifs**
```http
GET /api/events
Authorization: Bearer {token}

Response:
{
  "events": [
    {
      "id": 1,
      "title": "Le Grand Salon de l'Autisme",
      "slug": "grand-salon-autisme-2026",
      "description": "Deux jours de conférences et ateliers sur l'autisme",
      "date": "2026-04-15",
      "end_date": "2026-04-16",
      "time": "08:00:00",
      "end_time": "16:00:00",
      "location": "Fleuve Congo Hôtel, Kinshasa",
      "venue_details": "Salle de conférence principale",
      "capacity": 500,
      "organizer": "Never Limit Children (NLC)",
      "contact_phone": "+243 844 338 747",
      "contact_email": "info@nlcrdc.org",
      "registration_deadline": "2026-04-10",
      "prices": [
        {
          "id": 1,
          "category": "medecin",
          "label": "Médecin - Événement complet",
          "amount": 50.00,
          "currency": "USD",
          "duration_type": "full_event",
          "description": "Accès aux 2 jours"
        },
        {
          "id": 2,
          "category": "parent",
          "label": "Parent - Événement complet",
          "amount": 30.00,
          "currency": "USD",
          "duration_type": "full_event",
          "description": "Accès aux 2 jours"
        },
        {
          "id": 3,
          "category": "etudiant",
          "label": "Étudiant - Événement complet",
          "amount": 20.00,
          "currency": "USD",
          "duration_type": "full_event",
          "description": "Accès aux 2 jours"
        }
      ],
      "stats": {
        "total_tickets": 250,
        "physical_tickets": 120,
        "online_tickets": 130,
        "total_revenue": 10000.00
      }
    }
  ]
}
```

#### 5. Génération de QR Codes Physiques

**Générer des QR Codes Physiques pour un Événement**
```http
POST /api/events/{event_id}/generate-physical-qrs
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 100,
  "prefix": "PHY-QR-001"
}

Response:
{
  "success": true,
  "message": "100 QR codes physiques générés avec succès",
  "qr_codes": [
    {
      "physical_qr_id": "PHY-QR-001-ABC123",
      "qr_data": "{\"physical_qr_id\":\"PHY-QR-001-ABC123\",\"event_id\":1,\"event\":\"Le Grand Salon de l'Autisme\"}",
      "status": "available"
    }
    // ... 100 QR codes
  ],
  "download_url": "/api/events/1/physical-qrs/download"
}
```

---

## 📱 Écrans de l'Application

### 1. Écran de Connexion
```
┌─────────────────────────┐
│                         │
│    [Logo Application]   │
│                         │
│  ┌───────────────────┐  │
│  │ Email            │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Mot de passe     │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │   SE CONNECTER    │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

### 2. Écran d'Accueil (Dashboard)
```
┌─────────────────────────┐
│  Bonjour, Agent Name    │
│                         │
│  ┌─────────────────┐    │
│  │  📷 Scanner QR  │    │
│  └─────────────────┘    │
│                         │
│  ┌─────────────────┐    │
│  │  🔍 Rechercher  │    │
│  └─────────────────┘    │
│                         │
│  ┌─────────────────┐    │
│  │  📋 Historique  │    │
│  └─────────────────┘    │
│                         │
└─────────────────────────┘
```

### 3. Écran Scanner QR
```
┌─────────────────────────┐
│  ← Scanner le Billet    │
│                         │
│  ┌─────────────────┐    │
│  │                 │    │
│  │   [Caméra QR]   │    │
│  │                 │    │
│  │   ┌─────────┐   │    │
│  │   │         │   │    │
│  │   └─────────┘   │    │
│  │                 │    │
│  └─────────────────┘    │
│                         │
│  Positionnez le QR code │
│  dans le cadre          │
│                         │
└─────────────────────────┘
```

### 4. Écran Recherche
```
┌─────────────────────────┐
│  ← Rechercher un Billet │
│                         │
│  ┌───────────────────┐  │
│  │ Référence        │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Téléphone        │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │    RECHERCHER     │  │
│  └───────────────────┘  │
│                         │
│  OU                     │
│                         │
│  ┌───────────────────┐  │
│  │   📷 Scanner QR   │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

### 5. Écran Détails du Billet
```
┌─────────────────────────┐
│  ← Détails du Billet    │
│                         │
│  ✅ Paiement Confirmé   │
│                         │
│  💻 Billet En Ligne     │
│  (ou 🔲 Billet Physique)│
│                         │
│  Référence: ABC123XYZ   │
│  ─────────────────────  │
│                         │
│  👤 John Doe            │
│  📧 john@example.com    │
│  📱 +243 812 345 678    │
│                         │
│  🎫 Le Grand Salon de   │
│     l'Autisme           │
│  📅 15-16 Avril 2026    │
│  ⏰ 08H-16H             │
│  📍 Fleuve Congo Hôtel  │
│                         │
│  💰 50.00 USD           │
│  🏷️  Catégorie: Médecin │
│  💳 Paiement: MaxiCash  │
│                         │
│  📊 Scans: 3 fois       │
│  🕐 Premier: 18/02 10h  │
│  🕐 Dernier: 18/02 14h  │
│  👤 Validé par: Agent 5 │
│                         │
│  ┌───────────────────┐  │
│  │   ENREGISTRER     │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │  VOIR HISTORIQUE  │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

### 6. Écran Activation Billet Physique
```
┌─────────────────────────┐
│  ← Activer Billet       │
│     Physique            │
│                         │
│  🔲 QR Physique Scanné  │
│  PHY-QR-001-ABC123      │
│  ─────────────────────  │
│                         │
│  Événement              │
│  ┌───────────────────┐  │
│  │ Le Grand Salon de │  │
│  │ l'Autisme         │  │
│  └───────────────────┘  │
│                         │
│  Tarif                  │
│  ┌───────────────────┐  │
│  │ Médecin - 50 USD  │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Nom complet      │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Email            │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │ Téléphone        │  │
│  └───────────────────┘  │
│                         │
│  ┌───────────────────┐  │
│  │    ACTIVER        │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

### 7. Écran Statistiques Agent
```
┌─────────────────────────┐
│  ← Mes Statistiques     │
│                         │
│  👤 Agent Dupont        │
│  ─────────────────────  │
│                         │
│  📊 Total Validations   │
│  ┌─────────────────┐    │
│  │      150        │    │
│  └─────────────────┘    │
│                         │
│  🔲 Billets Physiques   │
│  ┌─────────────────┐    │
│  │   80 (53.3%)    │    │
│  │  4,000.00 USD   │    │
│  └─────────────────┘    │
│                         │
│  💻 Billets En Ligne    │
│  ┌─────────────────┐    │
│  │   70 (46.7%)    │    │
│  │  3,500.00 USD   │    │
│  └─────────────────┘    │
│                         │
│  📈 Évolution 30 jours  │
│  ┌─────────────────┐    │
│  │  [Graphique]    │    │
│  └─────────────────┘    │
│                         │
│  🎫 Par Événement       │
│  ┌─────────────────┐    │
│  │ Grand Salon     │    │
│  │ 50 validations  │    │
│  │ 2,500 USD       │    │
│  └─────────────────┘    │
│                         │
│  ┌───────────────────┐  │
│  │ VOIR HISTORIQUE   │  │
│  └───────────────────┘  │
│                         │
└─────────────────────────┘
```

---

## 🔐 Sécurité

### Authentification
- Utiliser JWT (JSON Web Token) pour l'authentification
- Stocker le token de manière sécurisée (Keychain iOS, Keystore Android)
- Expiration automatique du token après 24h
- Refresh token pour renouveler la session

### Permissions
```javascript
// Exemple de gestion des rôles
const permissions = {
  agent: ['scan', 'search', 'register'],
  admin: ['scan', 'search', 'register', 'create_client', 'validate_payment'],
  controller: ['scan', 'search']
};
```

### Données Sensibles
- Ne jamais stocker les mots de passe en local
- Chiffrer les données sensibles en cache
- Utiliser HTTPS pour toutes les communications

---

## 📦 Structure du Projet (React Native)

```
mobile-app/
├── src/
│   ├── screens/
│   │   ├── LoginScreen.js
│   │   ├── HomeScreen.js
│   │   ├── ScanQRScreen.js
│   │   ├── SearchScreen.js
│   │   ├── TicketDetailsScreen.js
│   │   ├── NewClientScreen.js
│   │   └── HistoryScreen.js
│   │
│   ├── components/
│   │   ├── Button.js
│   │   ├── Input.js
│   │   ├── TicketCard.js
│   │   ├── QRScanner.js
│   │   └── LoadingSpinner.js
│   │
│   ├── services/
│   │   ├── api.js
│   │   ├── auth.js
│   │   ├── tickets.js
│   │   └── storage.js
│   │
│   ├── navigation/
│   │   ├── AppNavigator.js
│   │   └── AuthNavigator.js
│   │
│   ├── utils/
│   │   ├── constants.js
│   │   ├── validators.js
│   │   └── formatters.js
│   │
│   └── store/
│       ├── authSlice.js
│       ├── ticketsSlice.js
│       └── store.js
│
├── assets/
│   ├── images/
│   └── fonts/
│
├── App.js
├── package.json
└── README.md
```

---

## 🚀 Installation et Configuration

### Prérequis
- Node.js 18+
- React Native CLI ou Expo CLI
- Android Studio (pour Android)
- Xcode (pour iOS)

### Installation

```bash
# Cloner le projet
git clone https://github.com/votre-repo/mobile-app.git
cd mobile-app

# Installer les dépendances
npm install

# iOS uniquement
cd ios && pod install && cd ..

# Lancer l'application
npm run android  # Pour Android
npm run ios      # Pour iOS
```

### Configuration

Créer un fichier `.env` :

```env
API_BASE_URL=https://votre-api.com/api
API_TIMEOUT=30000
```

---

## 📝 Exemples de Code

### 1. Service API

```javascript
// src/services/api.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://votre-api.com/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Intercepteur pour ajouter le token
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### 2. Authentification

```javascript
// src/services/auth.js
import api from './api';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const login = async (email, password) => {
  try {
    const response = await api.post('/login', { email, password });
    const { token, user } = response.data;
    
    // Sauvegarder le token
    await AsyncStorage.setItem('auth_token', token);
    await AsyncStorage.setItem('user', JSON.stringify(user));
    
    return { success: true, user };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur de connexion' 
    };
  }
};

export const logout = async () => {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('user');
  }
};
```

### 3. Vérification de Billet

```javascript
// src/services/tickets.js
import api from './api';

export const getTicketByReference = async (reference) => {
  try {
    const response = await api.get(`/api/tickets/${reference}`);
    return { success: true, ticket: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Billet non trouvé' 
    };
  }
};

export const scanTicket = async (qrData, scanLocation = 'Entrée') => {
  try {
    const response = await api.post('/api/qr-scan', {
      qr_data: qrData,
      scan_location: scanLocation
    });
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors du scan' 
    };
  }
};

export const scanTicketByReference = async (reference, scanLocation = 'Entrée') => {
  try {
    const response = await api.post('/api/qr-scan', {
      reference: reference,
      scan_location: scanLocation
    });
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors du scan' 
    };
  }
};

export const searchTicketByPhone = async (phone) => {
  try {
    const response = await api.get(`/api/tickets/search?phone=${phone}`);
    return { success: true, tickets: response.data.tickets };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Aucun billet trouvé' 
    };
  }
};

export const getTicketScanHistory = async (reference) => {
  try {
    const response = await api.get(`/api/tickets/${reference}/scans`);
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors de la récupération de l\'historique' 
    };
  }
};

export const registerParticipant = async (reference) => {
  try {
    const response = await api.post(`/api/tickets/${reference}/register`);
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur d\'enregistrement' 
    };
  }
};

// Nouvelles fonctions pour billets physiques
export const activatePhysicalTicket = async (physicalQrId, eventPriceId, participantData) => {
  try {
    const response = await api.post('/api/physical-tickets/activate', {
      physical_qr_id: physicalQrId,
      event_price_id: eventPriceId,
      full_name: participantData.full_name,
      email: participantData.email,
      phone: participantData.phone,
      pay_type: 'cash'
    });
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors de l\'activation' 
    };
  }
};

export const validateCashPayment = async (reference) => {
  try {
    const response = await api.post(`/api/tickets/${reference}/validate-cash`);
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors de la validation' 
    };
  }
};

// Statistiques de l'agent
export const getAgentStats = async () => {
  try {
    const response = await api.get('/api/agent/stats');
    return { success: true, data: response.data };
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Erreur lors de la récupération des statistiques' 
    };
  }
};
```

### 4. Scanner QR Code

```javascript
// src/components/QRScanner.js
import React from 'react';
import { RNCamera } from 'react-native-camera';
import { View, StyleSheet } from 'react-native';

const QRScanner = ({ onScan }) => {
  const handleBarCodeRead = (event) => {
    try {
      // Le QR code contient un JSON avec toutes les infos
      const data = JSON.parse(event.data);
      // Envoyer les données complètes pour enregistrer le scan
      onScan(event.data, data.reference);
    } catch (error) {
      // Si ce n'est pas du JSON, c'est peut-être juste la référence
      onScan(null, event.data);
    }
  };

  return (
    <View style={styles.container}>
      <RNCamera
        style={styles.camera}
        type={RNCamera.Constants.Type.back}
        onBarCodeRead={handleBarCodeRead}
        barCodeTypes={[RNCamera.Constants.BarCodeType.qr]}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  camera: {
    flex: 1,
  },
});

export default QRScanner;
```

### 5. Écran de Scan avec Enregistrement

```javascript
// src/screens/ScanQRScreen.js
import React, { useState } from 'react';
import { View, Text, StyleSheet, Alert } from 'react-native';
import QRScanner from '../components/QRScanner';
import { scanTicket, scanTicketByReference } from '../services/tickets';

const ScanQRScreen = ({ navigation }) => {
  const [scanning, setScanning] = useState(true);

  const handleScan = async (qrData, reference) => {
    if (!scanning) return;
    
    setScanning(false);

    try {
      let result;
      
      if (qrData) {
        // Scanner avec les données complètes du QR code
        result = await scanTicket(qrData, 'Entrée principale');
      } else {
        // Scanner avec juste la référence
        result = await scanTicketByReference(reference, 'Entrée principale');
      }

      if (result.success) {
        const { ticket, scan_info } = result.data;
        
        // Afficher une alerte si c'est le premier scan
        if (scan_info.is_first_scan) {
          Alert.alert(
            '✅ Premier Scan',
            `Bienvenue ${ticket.full_name}!\nC'est votre premier scan.`,
            [
              {
                text: 'OK',
                onPress: () => navigation.navigate('TicketDetails', { ticket, scan_info })
              }
            ]
          );
        } else {
          Alert.alert(
            '✅ Billet Scanné',
            `${ticket.full_name}\nScan #${scan_info.scan_count}`,
            [
              {
                text: 'Voir Détails',
                onPress: () => navigation.navigate('TicketDetails', { ticket, scan_info })
              },
              {
                text: 'Scanner Suivant',
                onPress: () => setScanning(true)
              }
            ]
          );
        }
      } else {
        Alert.alert('Erreur', result.message, [
          { text: 'Réessayer', onPress: () => setScanning(true) }
        ]);
      }
    } catch (error) {
      Alert.alert('Erreur', 'Une erreur est survenue', [
        { text: 'Réessayer', onPress: () => setScanning(true) }
      ]);
    }
  };

  return (
    <View style={styles.container}>
      {scanning ? (
        <QRScanner onScan={handleScan} />
      ) : (
        <View style={styles.loadingContainer}>
          <Text style={styles.loadingText}>Traitement...</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#000',
  },
  loadingText: {
    color: '#fff',
    fontSize: 18,
  },
});

export default ScanQRScreen;
```

### 5. Écran de Détails du Billet

```javascript
// src/screens/TicketDetailsScreen.js
import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Alert, ScrollView } from 'react-native';
import { registerParticipant, getTicketScanHistory, validateCashPayment } from '../services/tickets';

const TicketDetailsScreen = ({ route, navigation }) => {
  const { ticket, scan_info } = route.params;
  const [scanHistory, setScanHistory] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadScanHistory();
  }, []);

  const loadScanHistory = async () => {
    const result = await getTicketScanHistory(ticket.reference);
    if (result.success) {
      setScanHistory(result.data.scans);
    }
  };

  const handleRegister = async () => {
    const result = await registerParticipant(ticket.reference);
    
    if (result.success) {
      Alert.alert('Succès', 'Participant enregistré avec succès');
      navigation.goBack();
    } else {
      Alert.alert('Erreur', result.message);
    }
  };

  const handleValidateCash = async () => {
    Alert.alert(
      'Confirmer le paiement',
      'Le participant a-t-il payé en espèces?',
      [
        { text: 'Annuler', style: 'cancel' },
        {
          text: 'Confirmer',
          onPress: async () => {
            setLoading(true);
            const result = await validateCashPayment(ticket.reference);
            setLoading(false);
            
            if (result.success) {
              Alert.alert('Succès', 'Paiement validé avec succès');
              navigation.goBack();
            } else {
              Alert.alert('Erreur', result.message);
            }
          }
        }
      ]
    );
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed': return '#4CAF50';
      case 'pending_cash': return '#FF9800';
      case 'failed': return '#F44336';
      default: return '#9E9E9E';
    }
  };

  const getTicketTypeIcon = () => {
    return ticket.physical_qr_id ? '🔲' : '💻';
  };

  const getTicketTypeLabel = () => {
    return ticket.physical_qr_id ? 'Billet Physique' : 'Billet En Ligne';
  };

  const getTicketTypeBadgeColor = () => {
    return ticket.physical_qr_id ? '#8B5CF6' : '#3B82F6';
  };

  return (
    <ScrollView style={styles.container}>
      <View style={[styles.statusBadge, { backgroundColor: getStatusColor(ticket.payment_status) }]}>
        <Text style={styles.statusText}>
          {ticket.payment_status === 'completed' ? '✅ Paiement Confirmé' : 
           ticket.payment_status === 'pending_cash' ? '⏳ En Attente de Paiement' : 
           '❌ Paiement Échoué'}
        </Text>
      </View>

      <View style={[styles.typeBadge, { backgroundColor: getTicketTypeBadgeColor() }]}>
        <Text style={styles.typeText}>
          {getTicketTypeIcon()} {getTicketTypeLabel()}
        </Text>
      </View>

      <Text style={styles.reference}>Référence: {ticket.reference}</Text>
      {ticket.physical_qr_id && (
        <Text style={styles.physicalId}>QR Physique: {ticket.physical_qr_id}</Text>
      )}

      <View style={styles.section}>
        <Text style={styles.label}>👤 Participant</Text>
        <Text style={styles.value}>{ticket.full_name}</Text>
        <Text style={styles.subValue}>{ticket.email}</Text>
        <Text style={styles.subValue}>{ticket.phone}</Text>
      </View>

      <View style={styles.section}>
        <Text style={styles.label}>🎫 Événement</Text>
        <Text style={styles.value}>{ticket.event.title}</Text>
        <Text style={styles.subValue}>📅 {ticket.event.date} {ticket.event.end_date && `- ${ticket.event.end_date}`}</Text>
        {ticket.event.time && (
          <Text style={styles.subValue}>⏰ {ticket.event.time} {ticket.event.end_time && `- ${ticket.event.end_time}`}</Text>
        )}
        <Text style={styles.subValue}>📍 {ticket.event.location}</Text>
      </View>

      <View style={styles.section}>
        <Text style={styles.label}>💰 Paiement</Text>
        <Text style={styles.value}>{ticket.amount} {ticket.currency}</Text>
        {ticket.price && (
          <Text style={styles.subValue}>🏷️ {ticket.price.label}</Text>
        )}
        {ticket.pay_type && (
          <Text style={styles.subValue}>💳 Mode: {ticket.pay_type}</Text>
        )}
      </View>

      {scan_info && (
        <View style={styles.section}>
          <Text style={styles.label}>📊 Informations de Scan</Text>
          <Text style={styles.subValue}>Nombre de scans: {scan_info.scan_count}</Text>
          {scan_info.last_scanned_at && (
            <Text style={styles.subValue}>Dernier scan: {new Date(scan_info.last_scanned_at).toLocaleString('fr-FR')}</Text>
          )}
          {ticket.validated_by && (
            <Text style={styles.subValue}>👤 Validé par: Agent #{ticket.validated_by}</Text>
          )}
        </View>
      )}

      {scanHistory.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.label}>📜 Historique des Scans</Text>
          {scanHistory.slice(0, 5).map((scan, index) => (
            <View key={scan.id} style={styles.scanItem}>
              <Text style={styles.scanText}>
                {new Date(scan.scanned_at).toLocaleString('fr-FR')}
              </Text>
              <Text style={styles.scanSubText}>
                📍 {scan.scan_location} • 👤 {scan.scanned_by_user?.name}
              </Text>
            </View>
          ))}
        </View>
      )}

      {ticket.payment_status === 'pending_cash' && (
        <TouchableOpacity 
          style={[styles.button, styles.validateButton]} 
          onPress={handleValidateCash}
          disabled={loading}
        >
          <Text style={styles.buttonText}>
            {loading ? 'VALIDATION...' : 'VALIDER LE PAIEMENT EN CAISSE'}
          </Text>
        </TouchableOpacity>
      )}

      {ticket.payment_status === 'completed' && (
        <TouchableOpacity style={styles.button} onPress={handleRegister}>
          <Text style={styles.buttonText}>ENREGISTRER LE PARTICIPANT</Text>
        </TouchableOpacity>
      )}

      <TouchableOpacity 
        style={[styles.button, styles.secondaryButton]} 
        onPress={() => navigation.navigate('ScanHistory', { reference: ticket.reference })}
      >
        <Text style={[styles.buttonText, styles.secondaryButtonText]}>
          VOIR HISTORIQUE COMPLET
        </Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#fff',
  },
  statusBadge: {
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
    alignItems: 'center',
  },
  statusText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  typeBadge: {
    padding: 12,
    borderRadius: 10,
    marginBottom: 20,
    alignItems: 'center',
  },
  typeText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: 'bold',
  },
  reference: {
    fontSize: 18,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 10,
    color: '#333',
  },
  physicalId: {
    fontSize: 14,
    textAlign: 'center',
    marginBottom: 20,
    color: '#8B5CF6',
    fontWeight: '600',
  },
  section: {
    marginBottom: 20,
    padding: 15,
    backgroundColor: '#f5f5f5',
    borderRadius: 10,
  },
  label: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
    color: '#666',
  },
  value: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  subValue: {
    fontSize: 14,
    color: '#666',
    marginTop: 3,
  },
  scanItem: {
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  scanText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
  },
  scanSubText: {
    fontSize: 12,
    color: '#666',
    marginTop: 2,
  },
  button: {
    backgroundColor: '#2196F3',
    padding: 15,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 10,
  },
  validateButton: {
    backgroundColor: '#4CAF50',
  },
  secondaryButton: {
    backgroundColor: '#fff',
    borderWidth: 2,
    borderColor: '#2196F3',
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  secondaryButtonText: {
    color: '#2196F3',
  },
});

export default TicketDetailsScreen;
```

---

## 🧪 Tests

### Tests Unitaires
```bash
npm test
```

### Tests E2E
```bash
npm run test:e2e
```

---

## 📊 Métriques et Analytics

Intégrer des analytics pour suivre :
- Nombre de scans par jour
- Taux de réussite des vérifications
- Temps moyen de traitement
- Erreurs fréquentes

---

## 🔄 Synchronisation Offline

L'application devrait fonctionner en mode offline :
- Cache des billets récemment consultés
- File d'attente pour les enregistrements
- Synchronisation automatique quand la connexion revient

```javascript
// Exemple de gestion offline
import NetInfo from '@react-native-community/netinfo';

const syncQueue = async () => {
  const isConnected = await NetInfo.fetch().then(state => state.isConnected);
  
  if (isConnected) {
    const pendingActions = await AsyncStorage.getItem('pending_actions');
    // Traiter les actions en attente
  }
};
```

---

## 📱 Déploiement

### Android
```bash
cd android
./gradlew assembleRelease
```

### iOS
```bash
cd ios
xcodebuild -workspace App.xcworkspace -scheme App -configuration Release
```

---

## 🆘 Support et Maintenance

### Logs
- Utiliser un service de logging (Sentry, Crashlytics)
- Capturer les erreurs et exceptions
- Monitorer les performances

### Mises à Jour
- Utiliser CodePush pour les mises à jour OTA
- Versionning sémantique (1.0.0, 1.1.0, etc.)

---

## 📄 Licence

[Votre Licence]

---

## 👥 Contributeurs

[Liste des contributeurs]

---

## 📞 Contact

Pour toute question ou support :
- Email: support@votre-app.com
- Documentation API: https://api.votre-app.com/docs
