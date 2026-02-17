# Système de réservation en 2 étapes

## 🎯 Concept

Au lieu de demander toutes les informations d'un coup, vous pouvez maintenant:

1. **Étape 1**: Générer une référence avec juste `event_price_id`
2. **Étape 2**: Compléter les informations et procéder au paiement

## ✅ Avantages

- ✅ Réserver une place rapidement
- ✅ Remplir les informations plus tard
- ✅ Partager la référence avant de payer
- ✅ Meilleure expérience utilisateur

---

## 📋 Étape 1: Créer une réservation

### Endpoint
```
POST /api/events/{event_id}/reserve
```

### Champs requis
| Champ | Type | Obligatoire | Description |
|-------|------|-------------|-------------|
| `event_price_id` | integer | ✅ Oui | ID du tarif sélectionné |

### Exemple de requête

```bash
POST http://192.168.58.9:8000/api/events/1/reserve
Content-Type: application/json

{
  "event_price_id": 2
}
```

### Réponse (HTTP 201)

```json
{
  "success": true,
  "message": "Référence générée avec succès. Complétez vos informations pour finaliser.",
  "reservation": {
    "reference": "K7M9PQWXYZ",
    "event": {
      "id": 1,
      "title": "Le trouble du spectre autistique et la scolarité"
    },
    "price": {
      "id": 2,
      "category": "etudiant",
      "amount": "15.00",
      "currency": "USD"
    },
    "status": "reserved",
    "expires_at": "2026-02-12T22:30:00+00:00"
  }
}
```

### Avec cURL

```bash
curl -X POST http://192.168.58.9:8000/api/events/1/reserve \
  -H "Content-Type: application/json" \
  -d '{"event_price_id": 2}'
```

---

## 📋 Étape 2: Compléter la réservation

### Endpoint
```
POST /api/reservations/{reference}/complete
```

### Champs requis
| Champ | Type | Obligatoire | Règles |
|-------|------|-------------|--------|
| `full_name` | string | ✅ Oui | Min: 3, Max: 255 |
| `email` | email | ✅ Oui | Format email valide |
| `phone` | string | ✅ Oui | Min: 9, Max: 50 |
| `pay_type` | string | ✅ Oui | `online` ou `cash` |
| `days` | integer | ❌ Non | Min: 1, Défaut: 1 |
| `success_url` | url | ❌ Non | URL de succès |
| `cancel_url` | url | ❌ Non | URL d'annulation |
| `failure_url` | url | ❌ Non | URL d'échec |

### Exemple de requête

```bash
POST http://192.168.58.9:8000/api/reservations/K7M9PQWXYZ/complete
Content-Type: application/json

{
  "full_name": "Franck Kapuya",
  "email": "franckkapuya13@gmail.com",
  "phone": "+243822902681",
  "pay_type": "online",
  "days": 1
}
```

### Réponse (HTTP 200) - Paiement en ligne

```json
{
  "success": true,
  "payment_mode": "online",
  "reference": "K7M9PQWXYZ",
  "redirect_url": "https://api-testbed.maxicashapp.com/payentryweb?logid=97765",
  "log_id": "97765",
  "message": "Redirection vers MaxiCash pour finaliser le paiement."
}
```

### Réponse (HTTP 200) - Paiement en caisse

```json
{
  "success": true,
  "payment_mode": "cash",
  "ticket": {
    "reference": "K7M9PQWXYZ",
    "full_name": "Franck Kapuya",
    "email": "franckkapuya13@gmail.com",
    "phone": "+243822902681",
    "event": "Le trouble du spectre autistique et la scolarité",
    "category": "etudiant",
    "amount": "15.00",
    "currency": "USD",
    "status": "pending_cash",
    "qr_data": "{\"reference\":\"K7M9PQWXYZ\",\"event_id\":1,\"amount\":\"15.00\",\"currency\":\"USD\"}"
  },
  "message": "Ticket créé avec succès. Présentez ce QR code à la caisse pour finaliser votre paiement."
}
```

### Avec cURL

```bash
curl -X POST http://192.168.58.9:8000/api/reservations/K7M9PQWXYZ/complete \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Franck Kapuya",
    "email": "franckkapuya13@gmail.com",
    "phone": "+243822902681",
    "pay_type": "online"
  }'
```

---

## 📋 Vérifier une réservation

### Endpoint
```
GET /api/reservations/{reference}
```

### Exemple de requête

```bash
GET http://192.168.58.9:8000/api/reservations/K7M9PQWXYZ
```

### Réponse (HTTP 200)

```json
{
  "success": true,
  "reservation": {
    "reference": "K7M9PQWXYZ",
    "status": "reserved",
    "is_completed": false,
    "event": {
      "id": 1,
      "title": "Le trouble du spectre autistique et la scolarité"
    },
    "price": {
      "category": "etudiant",
      "amount": "15.00",
      "currency": "USD"
    },
    "participant": {
      "full_name": null,
      "email": null,
      "phone": null
    },
    "created_at": "2026-02-12T21:00:00+00:00"
  }
}
```

---

## 🔄 Flux complet

### Scénario 1: Réservation puis paiement en ligne

```
1. POST /api/events/1/reserve
   Body: {"event_price_id": 2}
   → Réponse: {"reference": "K7M9PQWXYZ", "status": "reserved"}

2. GET /api/reservations/K7M9PQWXYZ
   → Vérifier que la réservation existe

3. POST /api/reservations/K7M9PQWXYZ/complete
   Body: {
     "full_name": "Franck Kapuya",
     "email": "franckkapuya13@gmail.com",
     "phone": "+243822902681",
     "pay_type": "online"
   }
   → Réponse: {"redirect_url": "https://api-testbed.maxicashapp.com/..."}

4. Rediriger l'utilisateur vers MaxiCash

5. Après paiement: Redirection vers success_url?reference=K7M9PQWXYZ
```

### Scénario 2: Réservation puis paiement en caisse

```
1. POST /api/events/1/reserve
   Body: {"event_price_id": 2}
   → Réponse: {"reference": "K7M9PQWXYZ", "status": "reserved"}

2. POST /api/reservations/K7M9PQWXYZ/complete
   Body: {
     "full_name": "Franck Kapuya",
     "email": "franckkapuya13@gmail.com",
     "phone": "+243822902681",
     "pay_type": "cash"
   }
   → Réponse: {"qr_data": "...", "status": "pending_cash"}

3. Afficher le QR code à l'utilisateur

4. L'utilisateur présente le QR code à la caisse

5. Admin valide le paiement: POST /api/tickets/K7M9PQWXYZ/validate-cash
```

---

## 📊 Statuts des réservations

| Statut | Description |
|--------|-------------|
| `reserved` | Réservation créée, informations non complétées |
| `pending` | Informations complétées, paiement en ligne en attente |
| `pending_cash` | Informations complétées, paiement en caisse en attente |
| `completed` | Paiement validé |
| `failed` | Paiement échoué |
| `cancelled` | Réservation annulée |

---

## 🧪 Tests

### Test 1: Créer une réservation

```bash
curl -X POST http://192.168.58.9:8000/api/events/1/reserve \
  -H "Content-Type: application/json" \
  -d '{"event_price_id": 2}'
```

### Test 2: Vérifier la réservation

```bash
curl http://192.168.58.9:8000/api/reservations/K7M9PQWXYZ
```

### Test 3: Compléter la réservation

```bash
curl -X POST http://192.168.58.9:8000/api/reservations/K7M9PQWXYZ/complete \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Franck Kapuya",
    "email": "franckkapuya13@gmail.com",
    "phone": "+243822902681",
    "pay_type": "online"
  }'
```

---

## ⚠️ Important

### Expiration des réservations
- Les réservations expirent après **30 minutes** par défaut
- Vous pouvez implémenter un système de nettoyage automatique
- Ajoutez un champ `expires_at` dans la table `tickets` si nécessaire

### Validation
- Une réservation ne peut être complétée qu'une seule fois
- Le statut doit être `reserved` pour pouvoir compléter
- Tous les champs obligatoires doivent être fournis à l'étape 2

### Sécurité
- La référence est unique et aléatoire (10 caractères)
- Pas besoin d'authentification pour créer une réservation
- Pas besoin d'authentification pour compléter une réservation

---

## 🎨 Exemple d'interface utilisateur

### Page 1: Sélection du tarif
```
┌─────────────────────────────────────┐
│  Sélectionnez votre tarif           │
│                                     │
│  ○ Étudiant - 15.00 USD             │
│  ○ Médecin - 50.00 USD              │
│  ○ Parent - 15.00 USD               │
│                                     │
│  [Réserver ma place]                │
└─────────────────────────────────────┘
```

### Page 2: Référence générée
```
┌─────────────────────────────────────┐
│  Votre référence: K7M9PQWXYZ        │
│                                     │
│  Votre place est réservée!          │
│  Complétez vos informations pour    │
│  finaliser votre inscription.       │
│                                     │
│  [Continuer]                        │
└─────────────────────────────────────┘
```

### Page 3: Formulaire d'inscription
```
┌─────────────────────────────────────┐
│  Référence: K7M9PQWXYZ              │
│                                     │
│  Nom complet: [____________]        │
│  Email: [____________]              │
│  Téléphone: [____________]          │
│                                     │
│  Mode de paiement:                  │
│  ○ Paiement en ligne                │
│  ○ Paiement en caisse               │
│                                     │
│  [Finaliser l'inscription]          │
└─────────────────────────────────────┘
```

---

## 📚 Comparaison avec le système classique

### Système classique (1 étape)
```
POST /api/events/1/register
Body: {
  "event_price_id": 2,
  "full_name": "Franck Kapuya",
  "email": "franckkapuya13@gmail.com",
  "phone": "+243822902681",
  "pay_type": "online"
}
```

### Nouveau système (2 étapes)
```
1. POST /api/events/1/reserve
   Body: {"event_price_id": 2}

2. POST /api/reservations/K7M9PQWXYZ/complete
   Body: {
     "full_name": "Franck Kapuya",
     "email": "franckkapuya13@gmail.com",
     "phone": "+243822902681",
     "pay_type": "online"
   }
```

**Avantage**: L'utilisateur peut réserver rapidement et remplir les informations plus tard!

---

## 🎉 Résumé

✅ **Étape 1**: Générer une référence avec juste `event_price_id`
✅ **Étape 2**: Compléter avec `full_name`, `email`, `phone`, `pay_type`
✅ **Vérification**: Vérifier le statut d'une réservation à tout moment
✅ **Flexible**: Supporte paiement en ligne ET en caisse
✅ **Simple**: API REST claire et intuitive

**Les deux systèmes coexistent**: Vous pouvez utiliser l'ancien système (`/register`) ou le nouveau (`/reserve` + `/complete`)!
