# Correction : Référence de billet disponible

## Problème
L'application mobile affichait :
```
⚠️ Pas de référence de billet disponible
```

Même si l'API retournait bien le ticket (status 200).

---

## Cause
La référence était uniquement dans `ticket.reference` et non à la racine de la réponse JSON.

L'app mobile cherchait probablement `data.reference` directement.

---

## Solution

### Modification dans TicketController

**Fichier :** `app/Http/Controllers/API/TicketController.php`  
**Méthode :** `show()`

**Avant :**
```php
return response()->json([
    'success' => true,
    'ticket' => $ticket,
]);
```

**Après :**
```php
return response()->json([
    'success' => true,
    'ticket' => $ticket,
    'reference' => $ticket->reference, // ✅ Ajouté
]);
```

---

## Structure de la réponse

### Ticket trouvé (200)
```json
{
  "success": true,
  "reference": "3LN00ULCMK",  ← ✅ Maintenant disponible à la racine
  "ticket": {
    "id": 1,
    "reference": "3LN00ULCMK",  ← Toujours disponible ici aussi
    "full_name": "John Doe",
    "email": "john@example.com",
    "payment_status": "completed",
    "event": {
      "title": "Conférence 2024"
    },
    ...
  }
}
```

### Ticket non trouvé (404)
```json
{
  "success": false,
  "message": "Aucun ticket trouvé avec cette référence.",
  "reference": "3LN00ULCMK"
}
```

---

## Code pour l'app mobile

### Accès à la référence (recommandé)

```javascript
const fetchTicket = async (reference) => {
  const response = await fetch(`${API_URL}/tickets/${reference}`);
  const data = await response.json();
  
  if (data.success) {
    // ✅ Accès direct à la référence
    console.log('Référence:', data.reference);
    console.log('Ticket:', data.ticket);
    
    return {
      success: true,
      reference: data.reference,
      ticket: data.ticket,
    };
  } else {
    return {
      success: false,
      message: data.message,
    };
  }
};
```

### Avec fallback (plus robuste)

```javascript
const getReference = (data) => {
  // Essayer d'abord à la racine
  return data.reference || data.ticket?.reference || null;
};

// Utilisation
const reference = getReference(data);
if (reference) {
  console.log('✅ Référence:', reference);
} else {
  console.warn('⚠️ Pas de référence disponible');
}
```

---

## Test

### Via cURL
```bash
curl -X GET "http://localhost:8000/api/tickets/3LN00ULCMK" \
  -H "Accept: application/json"
```

### Via script PHP
```bash
php test-ticket-response.php 3LN00ULCMK
```

### Résultat attendu
```
Status HTTP: 200

📦 Structure de la réponse:
{
  "success": true,
  "reference": "3LN00ULCMK",
  "ticket": { ... }
}

✅ Référence trouvée à la racine: 3LN00ULCMK
✅ Champ 'success': true
```

---

## Autres endpoints affectés

Cette amélioration pourrait aussi être appliquée à :

- `POST /api/tickets/scan` - Scan de billet
- `POST /api/tickets/{reference}/validate-cash` - Validation paiement
- `POST /api/tickets/physical` - Activation billet physique

Mais ces endpoints retournent déjà la référence dans leur structure de réponse.

---

## Vérification

✅ La référence est maintenant disponible à deux endroits :
  - `data.reference` (racine) ← Recommandé
  - `data.ticket.reference` (dans ticket) ← Fallback

✅ L'app mobile peut accéder facilement à la référence

✅ Rétrocompatible : l'ancienne méthode fonctionne toujours

---

## Documentation

Consultez `STRUCTURE_REPONSE_API_TICKETS.md` pour :
- Structure complète des réponses
- Exemples de code pour l'app mobile
- Guide de débogage
- Checklist de vérification
