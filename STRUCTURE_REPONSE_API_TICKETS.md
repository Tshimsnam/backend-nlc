# Structure des réponses API pour les tickets

## Endpoint: GET /api/tickets/{reference}

### Cas 1: Ticket trouvé (200)

```json
{
  "success": true,
  "reference": "3LN00ULCMK",
  "ticket": {
    "id": 1,
    "reference": "3LN00ULCMK",
    "physical_qr_id": null,
    "event_id": 1,
    "participant_id": null,
    "event_price_id": 1,
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+243 123 456 789",
    "category": "student_1day",
    "days": 1,
    "amount": "50.00",
    "currency": "USD",
    "pay_type": "maxicash",
    "pay_sub_type": null,
    "payment_status": "completed",
    "validated_by": null,
    "gateway_log_id": "LOG123",
    "qr_data": "{\"reference\":\"3LN00ULCMK\",\"event_id\":1}",
    "scan_count": 0,
    "first_scanned_at": null,
    "last_scanned_at": null,
    "created_at": "2024-02-22T10:00:00.000000Z",
    "updated_at": "2024-02-22T10:00:00.000000Z",
    "event": {
      "id": 1,
      "title": "Conférence 2024",
      "slug": "conference-2024",
      "date": "2024-03-15",
      "time": "09:00",
      "location": "Kinshasa"
    },
    "price": {
      "id": 1,
      "event_id": 1,
      "category": "student_1day",
      "duration_type": "1day",
      "amount": "50.00",
      "currency": "USD",
      "label": "Étudiant - 1 jour"
    }
  }
}
```

### Cas 2: Ticket non trouvé (404)

```json
{
  "success": false,
  "message": "Aucun ticket trouvé avec cette référence.",
  "reference": "3LN00ULCMK"
}
```

---

## Comment lire la réponse dans l'app mobile

### React Native / JavaScript

```javascript
const fetchTicket = async (reference) => {
  try {
    const response = await fetch(`${API_URL}/tickets/${reference}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });

    const data = await response.json();

    // Vérifier le succès
    if (data.success) {
      // ✅ Ticket trouvé
      console.log('Référence:', data.reference); // Accessible à la racine
      console.log('Nom:', data.ticket.full_name);
      console.log('Statut:', data.ticket.payment_status);
      console.log('Événement:', data.ticket.event.title);
      
      return {
        success: true,
        reference: data.reference, // ou data.ticket.reference
        ticket: data.ticket,
      };
    } else {
      // ❌ Ticket non trouvé
      console.log('Erreur:', data.message);
      
      return {
        success: false,
        message: data.message,
      };
    }
  } catch (error) {
    console.error('Erreur réseau:', error);
    return {
      success: false,
      message: 'Erreur de connexion',
    };
  }
};
```

### Exemple d'utilisation

```javascript
// Dans votre composant
const handleFetchTicket = async (reference) => {
  const result = await fetchTicket(reference);
  
  if (result.success) {
    // Afficher les détails du ticket
    setTicket(result.ticket);
    setReference(result.reference);
  } else {
    // Afficher l'erreur
    Alert.alert('Erreur', result.message);
  }
};
```

---

## Accès à la référence

La référence est maintenant disponible à **deux endroits** :

1. **À la racine** : `data.reference` (✅ Recommandé)
2. **Dans ticket** : `data.ticket.reference`

### Exemple de vérification robuste

```javascript
const getReference = (data) => {
  // Essayer d'abord à la racine
  if (data.reference) {
    return data.reference;
  }
  
  // Sinon, chercher dans ticket
  if (data.ticket && data.ticket.reference) {
    return data.ticket.reference;
  }
  
  // Aucune référence trouvée
  return null;
};

// Utilisation
const reference = getReference(data);
if (reference) {
  console.log('Référence:', reference);
} else {
  console.warn('⚠️ Pas de référence de billet disponible');
}
```

---

## Autres endpoints similaires

### GET /api/tickets/search?phone={phone}

**Succès (200):**
```json
{
  "success": true,
  "message": "2 ticket(s) trouvé(s).",
  "phone": "0827029543",
  "count": 2,
  "tickets": [
    {
      "reference": "TKT-123",
      "full_name": "John Doe",
      ...
    }
  ]
}
```

**Aucun résultat (404):**
```json
{
  "success": false,
  "message": "Aucun ticket trouvé pour ce numéro de téléphone.",
  "phone": "0827029543",
  "tickets": []
}
```

### POST /api/tickets/scan

**Succès (200):**
```json
{
  "success": true,
  "message": "Billet scanné avec succès",
  "ticket": {
    "reference": "TKT-123",
    ...
  },
  "scan_info": {
    "scan_count": 1,
    "is_first_scan": true,
    "last_scanned_at": "2024-02-22T10:00:00.000000Z"
  }
}
```

**Erreur (404):**
```json
{
  "success": false,
  "message": "Billet introuvable"
}
```

---

## Checklist pour l'app mobile

✅ Toujours vérifier `data.success` avant de traiter la réponse  
✅ Utiliser `data.reference` (à la racine) pour accéder à la référence  
✅ Gérer les cas d'erreur avec `data.message`  
✅ Vérifier le code HTTP (200 = succès, 404 = non trouvé)  
✅ Avoir un fallback pour `data.ticket.reference` si nécessaire  

---

## Débogage

Si vous voyez "⚠️ Pas de référence de billet disponible" :

1. Vérifiez que l'API retourne bien `success: true`
2. Vérifiez que `data.reference` existe
3. Vérifiez que `data.ticket.reference` existe en fallback
4. Utilisez `console.log(JSON.stringify(data, null, 2))` pour voir la structure complète

### Script de test

```bash
php test-ticket-response.php 3LN00ULCMK
```

Cela affichera la structure exacte de la réponse API.
