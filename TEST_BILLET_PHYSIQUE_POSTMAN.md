# Test de l'API Billet Physique sur Postman

## Prérequis
1. Avoir un token d'authentification (connexion via `/api/login`)
2. Avoir un événement avec des prix configurés

## Étape 1: Se connecter (obtenir le token)

**Endpoint:** `POST http://localhost:8000/api/login`

**Headers:**
```
Content-Type: application/json
X-API-Secret: votre_api_secret
```

**Body (JSON):**
```json
{
  "email": "agent@example.com",
  "password": "password123"
}
```

**Réponse:**
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxx",
  "user": {...}
}
```

Copiez le token pour les prochaines requêtes.

---

## Étape 2: Vérifier si le QR physique est déjà activé

**Endpoint:** `POST http://localhost:8000/api/tickets/physical/check`

**Headers:**
```
Authorization: Bearer {votre_token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "physical_qr_id": "PHY-1708345678-ABC123XYZ"
}
```

**Réponse si NON activé (200):**
```json
{
  "success": true,
  "is_activated": false,
  "message": "QR code disponible pour activation"
}
```

**Réponse si DÉJÀ activé (200):**
```json
{
  "success": true,
  "is_activated": true,
  "message": "Ce QR code a déjà été activé",
  "ticket": {
    "id": 123,
    "reference": "TKT-1708345678-XYZ123",
    "physical_qr_id": "PHY-1708345678-ABC123XYZ",
    "full_name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+243123456789",
    "amount": "50.00",
    "currency": "USD",
    "payment_status": "completed",
    "created_at": "2025-02-19T17:30:00.000000Z",
    "event": {...},
    "price": {...}
  },
  "participant": {
    "id": 45,
    "name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+243123456789",
    "category": "enseignant"
  }
}
```

---

## Étape 3: Récupérer les prix d'un événement

**Endpoint:** `GET http://localhost:8000/api/events/{eventId}/prices-for-physical`

**Headers:**
```
Authorization: Bearer {votre_token}
Accept: application/json
```

**Exemple:** `GET http://localhost:8000/api/events/1/prices-for-physical`

**Réponse:**
```json
{
  "success": true,
  "event": {
    "id": 1,
    "title": "Conférence 2025",
    "date": "2025-03-15 09:00:00",
    "location": "Kinshasa"
  },
  "prices": [
    {
      "id": 1,
      "category": "teacher",
      "duration_type": null,
      "amount": "50.00",
      "currency": "USD",
      "label": "Enseignants",
      "description": "Tarif réduit pour les enseignants",
      "display_label": "Enseignants - Tarif réduit pour les enseignants"
    },
    {
      "id": 2,
      "category": "student_1day",
      "duration_type": "1_day",
      "amount": "30.00",
      "currency": "USD",
      "label": "Étudiants 1 jour",
      "description": "Accès pour 1 journée",
      "display_label": "Étudiants 1 jour - Accès pour 1 journée"
    }
  ]
}
```

---

## Étape 4: Créer un ticket depuis un QR physique

**Endpoint:** `POST http://localhost:8000/api/tickets/physical`

**Headers:**
```
Authorization: Bearer {votre_token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "physical_qr_id": "PHY-1708345678-ABC123XYZ",
  "event_id": 1,
  "full_name": "Jean Dupont",
  "email": "jean.dupont@example.com",
  "phone": "+243123456789",
  "event_price_id": 1
}
```

**Réponse de succès (201):**
```json
{
  "success": true,
  "ticket": {
    "id": 123,
    "reference": "TKT-1708345678-XYZ123",
    "physical_qr_id": "PHY-1708345678-ABC123XYZ",
    "event_id": 1,
    "participant_id": 45,
    "event_price_id": 1,
    "full_name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+243123456789",
    "amount": "50.00",
    "currency": "USD",
    "pay_type": "cash",
    "payment_status": "completed",
    "qr_data": "{...}",
    "created_at": "2025-02-19T17:30:00.000000Z",
    "event": {
      "id": 1,
      "title": "Conférence 2025",
      ...
    },
    "participant": {
      "id": 45,
      "name": "Jean Dupont",
      "email": "jean.dupont@example.com",
      "phone": "+243123456789",
      "category": "teacher",
      ...
    },
    "price": {
      "id": 1,
      "category": "teacher",
      "amount": "50.00",
      "currency": "USD",
      ...
    }
  },
  "participant": {
    "id": 45,
    "event_id": 1,
    "user_id": 2,
    "name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+243123456789",
    "category": "teacher",
    "duration_type": null,
    "created_at": "2025-02-19T17:30:00.000000Z"
  },
  "message": "Billet physique activé avec succès"
}
```

**Erreurs possibles:**

### QR code déjà utilisé (409 Conflict)
```json
{
  "success": false,
  "already_used": true,
  "message": "Ce QR code a déjà été utilisé",
  "ticket": {
    "id": 123,
    "reference": "TKT-1708345678-XYZ123",
    "physical_qr_id": "PHY-1708345678-ABC123XYZ",
    "event_id": 1,
    "participant_id": 45,
    "full_name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+243123456789",
    "amount": "50.00",
    "currency": "USD",
    "pay_type": "cash",
    "payment_status": "completed",
    "created_at": "2025-02-19T17:30:00.000000Z",
    "event": {
      "id": 1,
      "title": "Conférence 2025",
      ...
    },
    "price": {
      "id": 1,
      "category": "teacher",
      "amount": "50.00",
      ...
    }
  },
  "participant": {
    "id": 45,
    "name": "Jean Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+243123456789",
    "category": "enseignant",
    "created_at": "2025-02-19T17:30:00.000000Z"
  }
}
```

**Note importante:** Quand un QR code a déjà été utilisé, l'API retourne un code 409 (Conflict) avec `already_used: true` et les informations complètes du billet existant. Cela permet à l'application mobile d'afficher les détails du participant qui a déjà activé ce billet.

### QR code invalide (400)
```json
{
  "success": false,
  "error": "QR code invalide. Le code doit commencer par PHY-"
}
```

### Prix invalide (400)
```json
{
  "success": false,
  "error": "Prix invalide pour cet événement"
}
```

### Non authentifié (401)
```json
{
  "message": "Unauthenticated."
}
```

---

## Notes importantes

1. **Format du physical_qr_id:** Doit toujours commencer par `PHY-` suivi d'un timestamp et d'un identifiant aléatoire
   - Exemple: `PHY-1708345678-ABC123XYZ`

2. **Unicité:** Chaque `physical_qr_id` ne peut être utilisé qu'une seule fois

3. **Authentification:** L'agent doit être connecté (token Bearer requis)

4. **Traçabilité:** Le système enregistre automatiquement l'ID de l'agent qui active le billet dans `participants.user_id`

5. **Statut:** Les billets physiques sont automatiquement validés (`payment_status = 'completed'`)

6. **Mode de paiement:** Toujours `cash` pour les billets physiques

---

## Workflow complet

```
1. Agent se connecte → Obtient token
2. Agent scanne QR physique → Récupère physical_qr_id et event_id
3. App vérifie le statut → POST /api/tickets/physical/check
   ↓
   3a. Si déjà activé → Afficher infos du propriétaire
   3b. Si non activé → Continuer le processus
4. App récupère les prix → GET /api/events/{eventId}/prices-for-physical
5. Agent remplit formulaire → Sélectionne prix, entre infos participant
6. App crée ticket → POST /api/tickets/physical
7. Ticket créé et validé → Participant peut entrer à l'événement
```

---

## Collection Postman

Vous pouvez importer cette collection dans Postman:

```json
{
  "info": {
    "name": "Billets Physiques API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "1. Login",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "X-API-Secret",
            "value": "{{api_secret}}"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"email\": \"agent@example.com\",\n  \"password\": \"password123\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/api/login",
          "host": ["{{base_url}}"],
          "path": ["api", "login"]
        }
      }
    },
    {
      "name": "2. Get Event Prices",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/events/1/prices-for-physical",
          "host": ["{{base_url}}"],
          "path": ["api", "events", "1", "prices-for-physical"]
        }
      }
    },
    {
      "name": "3. Create Physical Ticket",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"physical_qr_id\": \"PHY-1708345678-ABC123XYZ\",\n  \"event_id\": 1,\n  \"full_name\": \"Jean Dupont\",\n  \"email\": \"jean.dupont@example.com\",\n  \"phone\": \"+243123456789\",\n  \"event_price_id\": 1\n}"
        },
        "url": {
          "raw": "{{base_url}}/api/tickets/physical",
          "host": ["{{base_url}}"],
          "path": ["api", "tickets", "physical"]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    },
    {
      "key": "token",
      "value": ""
    },
    {
      "key": "api_secret",
      "value": "votre_api_secret"
    }
  ]
}
```
