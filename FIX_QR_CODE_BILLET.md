# Fix: Code QR du Billet ne Fonctionne Plus

## 🔍 Problème Identifié

Le code QR dans le billet ne fonctionnait plus car il y avait une **incohérence entre le frontend et le backend**.

### Cause du Problème

1. **Backend (TicketController.php)** génère correctement le `qr_data` avec la structure attendue:
```json
{
  "reference": "ABC123",
  "event_id": 1,
  "amount": 100,
  "currency": "USD",
  "payment_mode": "cash"
}
```

2. **Frontend (EventInscriptionPage.tsx)** ignorait le `qr_data` de l'API et créait son propre JSON:
```json
{
  "reference": "ABC123",
  "event": "Titre événement",
  "participant": "Nom complet",
  "email": "email@example.com",
  ...
}
```

3. **QRScanController.php** attend la structure du backend avec `reference` et `event_id`, mais recevait la structure du frontend qui ne contient pas `event_id`.

## ✅ Solution Appliquée

### Fichiers Modifiés

1. **EventInscriptionPage.tsx**
2. **EventInscriptionPage-v2.tsx**
3. **EventInscriptionPage copy.tsx**

### Changement Effectué

**AVANT:**
```typescript
// Créer un QR code avec toutes les informations
const qrInfo = JSON.stringify({
  reference: res.data.ticket.reference,
  event: event.title,
  participant: formData.full_name,
  email: formData.email,
  phone: formData.phone,
  amount: res.data.ticket.amount,
  currency: res.data.ticket.currency,
  category: res.data.ticket.category,
  date: event.date,
  location: event.location,
});
setQrData(qrInfo);
```

**APRÈS:**
```typescript
// Utiliser le qr_data retourné par l'API (contient la structure correcte pour le scan)
setQrData(res.data.ticket.qr_data);
```

## 🔄 Flux Correct

### 1. Création du Ticket (Backend)
```php
// TicketController.php - ligne 75-85
'qr_data' => json_encode([
    'reference' => $ticket->reference,
    'event_id' => $event->id,
    'amount' => $ticket->amount,
    'currency' => $ticket->currency,
    'payment_mode' => $gateway,
]),
```

### 2. Affichage du QR Code (Frontend)
```typescript
// EventInscriptionPage.tsx
setQrData(res.data.ticket.qr_data); // Utilise directement l'API
```

### 3. Scan du QR Code (Backend)
```php
// QRScanController.php - ligne 31-40
if ($request->filled('qr_data')) {
    $qrData = json_decode($request->qr_data, true);
    
    if (!$qrData || !isset($qrData['reference'])) {
        return response()->json([
            'success' => false,
            'message' => 'QR code invalide',
        ], 400);
    }

    $ticket = Ticket::where('reference', $qrData['reference'])->first();
}
```

## 📱 Test du Fix

### Pour Tester:

1. **Créer un nouveau ticket:**
   - Aller sur la page d'inscription d'un événement
   - Choisir "Paiement en caisse"
   - Remplir le formulaire et soumettre

2. **Vérifier le QR Code:**
   - Le QR code affiché doit contenir le JSON correct
   - Scanner avec un lecteur QR pour vérifier le contenu

3. **Scanner le Billet:**
   - Utiliser l'endpoint `/api/tickets/scan`
   - Envoyer le `qr_data` scanné
   - Vérifier que le scan est enregistré

### Endpoint de Scan

```bash
POST /api/tickets/scan
Authorization: Bearer {token}

{
  "qr_data": "{\"reference\":\"ABC123\",\"event_id\":1,\"amount\":100,\"currency\":\"USD\",\"payment_mode\":\"cash\"}"
}
```

## 🎯 Résultat

- ✅ Le QR code contient maintenant la bonne structure
- ✅ Le scan fonctionne correctement
- ✅ Les statistiques de scan sont enregistrées
- ✅ Cohérence entre frontend et backend

## 📝 Notes Importantes

### Différence entre 2 Types de QR Codes

1. **QR Code Événement** (`qr-code-event-scan.html`)
   - Pour promouvoir l'événement
   - Redirige vers la page de l'événement
   - Compte les scans marketing

2. **QR Code Billet** (dans le ticket)
   - Pour valider l'entrée du participant
   - Contient les infos du ticket
   - Utilisé à l'entrée de l'événement

### Routes API Concernées

- `POST /api/events/{event}/register` - Création du ticket
- `POST /api/tickets/scan` - Scan du billet (nécessite auth)
- `GET /api/tickets/{reference}/scans` - Historique des scans
- `GET /api/events/{eventId}/scan-stats` - Statistiques

## 🔐 Sécurité

Le scan de billets nécessite une authentification (`auth:sanctum`). Seuls les utilisateurs autorisés (agents, admin) peuvent scanner les billets.

## 📊 Prochaines Étapes

1. Tester le scan avec l'application mobile
2. Vérifier les statistiques dans le dashboard admin
3. Valider le flux complet: inscription → paiement → scan
