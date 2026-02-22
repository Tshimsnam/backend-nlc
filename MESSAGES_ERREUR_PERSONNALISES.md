# Messages d'erreur personnalisés

## Problème résolu
Avant, quand un ticket, événement ou autre ressource n'était pas trouvé, Laravel retournait l'erreur générique :
```
"No query results for model [App\Models\Ticket]."
```

Maintenant, des messages d'erreur clairs et personnalisés sont retournés en français.

---

## Contrôleurs modifiés

### 1. TicketController (`app/Http/Controllers/API/TicketController.php`)

#### Méthode `show()` - Afficher un ticket
**Avant :** `firstOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Aucun ticket trouvé avec cette référence.",
  "reference": "TKT-123456"
}
```

#### Méthode `validateCashPayment()` - Valider un paiement
**Avant :** `firstOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Aucun ticket trouvé avec cette référence.",
  "reference": "TKT-123456"
}
```

#### Méthode `sendNotification()` - Envoyer une notification
**Avant :** `firstOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Aucun ticket trouvé avec cette référence.",
  "reference": "TKT-123456"
}
```

#### Méthode `store()` - Créer un ticket
**Avant :** `firstOrFail()` sur EventPrice → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Prix invalide pour cet événement.",
  "event_price_id": 123
}
```

---

### 2. QRScanController (`app/Http/Controllers/API/QRScanController.php`)

#### Méthode `scan()` - Scanner un billet
✅ Déjà bien implémenté avec messages personnalisés
```json
{
  "success": false,
  "message": "Billet introuvable"
}
```

#### Méthode `getScanHistory()` - Historique des scans
✅ Déjà bien implémenté
```json
{
  "success": false,
  "message": "Billet introuvable"
}
```

#### Méthode `getEventScanStats()` - Statistiques de scan
**Avant :** `findOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Événement introuvable.",
  "event_id": 123
}
```

---

### 3. PhysicalTicketController (`app/Http/Controllers/API/PhysicalTicketController.php`)

#### Méthode `getEventPrices()` - Prix d'un événement
**Avant :** `findOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Événement introuvable",
  "event_id": 123
}
```

---

### 4. ReservationController (`app/Http/Controllers/API/ReservationController.php`)

#### Méthode `createReservation()` - Créer une réservation
**Avant :** `firstOrFail()` sur EventPrice → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Prix invalide pour cet événement.",
  "event_price_id": 123
}
```

#### Méthode `completeReservation()` - Compléter une réservation
**Avant :** `firstOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Réservation introuvable ou déjà complétée.",
  "reference": "TKT-123456"
}
```

#### Méthode `checkReservation()` - Vérifier une réservation
**Avant :** `firstOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Réservation introuvable.",
  "reference": "TKT-123456"
}
```

---

### 5. EventScanController (`app/Http/Controllers/API/EventScanController.php`)

#### Méthode `recordScan()` - Enregistrer un scan
**Avant :** `firstOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Événement introuvable.",
  "slug": "conference-2024"
}
```

#### Méthode `getEventScans()` - Statistiques de scans
**Avant :** `firstOrFail()` → Erreur générique  
**Après :** Message personnalisé
```json
{
  "success": false,
  "message": "Événement introuvable.",
  "slug": "conference-2024"
}
```

---

## Format standard des réponses d'erreur

Toutes les réponses d'erreur suivent maintenant ce format :

```json
{
  "success": false,
  "message": "Message d'erreur clair en français",
  "reference": "Valeur recherchée (optionnel)"
}
```

**Code HTTP :** 404 (Not Found)

---

## Avantages

✅ Messages d'erreur clairs et compréhensibles  
✅ En français pour les utilisateurs  
✅ Inclut la valeur recherchée pour le débogage  
✅ Format JSON cohérent  
✅ Meilleure expérience utilisateur dans l'app mobile  
✅ Plus facile à gérer côté frontend  

---

## Test

### Tester avec cURL
```bash
# Recherche d'un ticket inexistant
curl -X GET "http://localhost:8000/api/tickets/TKT-INEXISTANT"

# Scan d'un billet inexistant
curl -X POST "http://localhost:8000/api/tickets/scan" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"reference": "TKT-INEXISTANT"}'
```

### Réponse attendue
```json
{
  "success": false,
  "message": "Aucun ticket trouvé avec cette référence.",
  "reference": "TKT-INEXISTANT"
}
```

---

## Notes

- Les autres contrôleurs (non-API) conservent `findOrFail()` pour l'instant
- Seuls les endpoints utilisés par l'application mobile ont été modifiés
- Le code HTTP 404 est toujours retourné pour la compatibilité
- Le champ `success: false` permet une détection facile côté frontend
