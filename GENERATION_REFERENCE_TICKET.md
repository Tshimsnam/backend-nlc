# Génération du numéro de référence (ticket)

## 🎯 Comment le numéro de référence est généré

Le numéro de référence du ticket est généré **automatiquement** par le backend lors de la création du ticket.

### Méthode de génération

Dans `TicketController.php` (ligne 48):
```php
'reference' => strtoupper(Str::random(10)),
```

**Résultat**: Une chaîne aléatoire de 10 caractères en majuscules
- Exemple: `XQECJYUN4O`, `T5AECQ2T4W`, `P0VKBZWQ2L`

## 📋 Champs OBLIGATOIRES pour créer un ticket

Pour générer un numéro de référence, vous devez envoyer ces champs obligatoires:

| Champ | Type | Obligatoire | Règles | Description |
|-------|------|-------------|--------|-------------|
| `event_price_id` | integer | ✅ Oui | Doit exister dans `event_prices` | ID du tarif sélectionné |
| `full_name` | string | ✅ Oui | Min: 3, Max: 255 | Nom complet du participant |
| `email` | email | ✅ Oui | Format email valide, Max: 255 | Email du participant |
| `phone` | string | ✅ Oui | Min: 9, Max: 50 | Téléphone du participant |
| `pay_type` | string | ✅ Oui | Valeurs: `online` ou `cash` | Mode de paiement |
| `days` | integer | ❌ Non | Min: 1, Défaut: 1 | Nombre de jours |
| `pay_sub_type` | string | ❌ Non | Max: 50 | Sous-type de paiement (optionnel) |
| `success_url` | url | ❌ Non | URL valide, Max: 500 | URL de succès (optionnel) |
| `cancel_url` | url | ❌ Non | URL valide, Max: 500 | URL d'annulation (optionnel) |
| `failure_url` | url | ❌ Non | URL valide, Max: 500 | URL d'échec (optionnel) |

## 📝 Exemple de payload minimal

```json
{
  "event_price_id": 2,
  "full_name": "Franck Kapuya",
  "email": "franckkapuya13@gmail.com",
  "phone": "+243822902681",
  "pay_type": "online"
}
```

## 🔄 Processus de création du ticket

### Étape 1: Validation des données
Le backend valide les données avec `StoreTicketRequest`:
- Vérifie que tous les champs obligatoires sont présents
- Vérifie que `event_price_id` existe dans la base de données
- Vérifie que `email` est un email valide
- Vérifie que `phone` a au moins 9 caractères
- Vérifie que `pay_type` est `online` ou `cash`

### Étape 2: Récupération du prix
```php
$price = EventPrice::where('id', $validated['event_price_id'])
    ->where('event_id', $event->id)
    ->firstOrFail();
```

### Étape 3: Création du ticket
```php
$ticket = Ticket::create([
    'event_id' => $event->id,
    'event_price_id' => $price->id,
    'full_name' => $validated['full_name'],
    'email' => $validated['email'],
    'phone' => $validated['phone'],
    'category' => $price->category,
    'days' => $validated['days'] ?? 1,
    'amount' => $price->amount,
    'currency' => $price->currency,
    'reference' => strtoupper(Str::random(10)), // ← GÉNÉRATION ICI
    'pay_type' => $validated['pay_type'],
    'payment_status' => $validated['pay_type'] === 'cash' ? 'pending_cash' : 'pending',
]);
```

### Étape 4: Retour de la référence
Le backend retourne la référence générée dans la réponse:
```json
{
  "success": true,
  "reference": "T5AECQ2T4W",
  "redirect_url": "https://api-testbed.maxicashapp.com/payentryweb?logid=97761"
}
```

## 🎯 Champs automatiquement remplis

Ces champs sont remplis automatiquement par le backend:

| Champ | Source | Description |
|-------|--------|-------------|
| `event_id` | Route parameter | ID de l'événement |
| `event_price_id` | Payload | ID du tarif |
| `category` | EventPrice | Catégorie du tarif (médecin, étudiant, etc.) |
| `amount` | EventPrice | Montant du tarif |
| `currency` | EventPrice | Devise du tarif (USD, etc.) |
| `reference` | Généré | Référence unique du ticket |
| `payment_status` | Calculé | `pending_cash` ou `pending` |
| `days` | Payload ou défaut | Nombre de jours (défaut: 1) |

## 📊 Exemple complet

### Requête
```bash
POST http://192.168.58.9:8000/api/events/1/register
Content-Type: application/json

{
  "event_price_id": 2,
  "full_name": "Franck Kapuya",
  "email": "franckkapuya13@gmail.com",
  "phone": "+243822902681",
  "days": 1,
  "pay_type": "online"
}
```

### Réponse (succès)
```json
{
  "success": true,
  "payment_mode": "online",
  "reference": "T5AECQ2T4W",
  "redirect_url": "https://api-testbed.maxicashapp.com/payentryweb?logid=97761",
  "log_id": "97761",
  "message": "Redirection vers MaxiCash pour finaliser le paiement"
}
```

### Ticket créé en base de données
```php
Ticket {
  id: 10,
  event_id: 1,
  event_price_id: 2,
  full_name: "Franck Kapuya",
  email: "franckkapuya13@gmail.com",
  phone: "+243822902681",
  category: "etudiant",
  days: 1,
  amount: "15.00",
  currency: "USD",
  reference: "T5AECQ2T4W", // ← RÉFÉRENCE GÉNÉRÉE
  pay_type: "online",
  payment_status: "pending",
  gateway_log_id: "97761",
  created_at: "2026-02-12 21:30:00",
  updated_at: "2026-02-12 21:30:00"
}
```

## 🔍 Vérifier un ticket par référence

### Endpoint
```
GET /api/tickets/{reference}
```

### Exemple
```bash
GET http://192.168.58.9:8000/api/tickets/T5AECQ2T4W
```

### Réponse
```json
{
  "id": 10,
  "reference": "T5AECQ2T4W",
  "full_name": "Franck Kapuya",
  "email": "franckkapuya13@gmail.com",
  "phone": "+243822902681",
  "amount": "15.00",
  "currency": "USD",
  "payment_status": "pending",
  "event": {
    "id": 1,
    "title": "Le trouble du spectre autistique et la scolarité"
  }
}
```

## 🎨 Format de la référence

### Caractéristiques
- **Longueur**: 10 caractères
- **Format**: Lettres et chiffres en majuscules
- **Exemple**: `T5AECQ2T4W`, `XQECJYUN4O`, `P0VKBZWQ2L`
- **Unicité**: Chaque référence est unique (probabilité de collision très faible)

### Pourquoi ce format?
- ✅ Facile à lire et à communiquer
- ✅ Pas de confusion avec des caractères similaires (0/O, 1/I)
- ✅ Suffisamment long pour éviter les collisions
- ✅ Facile à saisir manuellement si nécessaire

## 🔄 Alternative: Numéro de ticket séquentiel

Si vous préférez un numéro séquentiel (ex: `TKT-EVNT-000001`), vous pouvez utiliser `TicketService::generateTicketNumber()`:

```php
// Dans TicketController.php, remplacer:
'reference' => strtoupper(Str::random(10)),

// Par:
'reference' => $this->ticketService->generateTicketNumber($event),
```

**Format**: `TKT-{SLUG}-{SEQUENCE}`
- Exemple: `TKT-TROU-000001`, `TKT-TROU-000002`

## 📋 Résumé

### Champs obligatoires pour générer une référence:
1. ✅ `event_price_id` (integer)
2. ✅ `full_name` (string, min: 3)
3. ✅ `email` (email valide)
4. ✅ `phone` (string, min: 9)
5. ✅ `pay_type` (`online` ou `cash`)

### Champs optionnels:
- `days` (défaut: 1)
- `pay_sub_type`
- `success_url`, `cancel_url`, `failure_url`

### Génération automatique:
- ✅ Référence unique de 10 caractères
- ✅ Montant et devise depuis `EventPrice`
- ✅ Catégorie depuis `EventPrice`
- ✅ Statut de paiement selon `pay_type`

---

**Note**: La référence est générée automatiquement par le backend. Vous n'avez pas besoin de la fournir dans le payload.
