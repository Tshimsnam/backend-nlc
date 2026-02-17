# Flux de Paiement Simplifié - M-Pesa et Orange Money

## Vue d'ensemble

Le système utilise maintenant un flux simplifié pour M-Pesa et Orange Money :

1. **Génération de référence** : L'utilisateur génère d'abord sa référence de billet
2. **Instructions de paiement** : On lui affiche les instructions pour payer manuellement
3. **Pas de redirection** : Tout se passe sur la même page

## Comparaison des flux

### MaxiCash (Flux avec redirection)
```
Utilisateur → Formulaire → Backend → MaxiCash API → Redirection → Paiement → Callback → Confirmation
```

### M-Pesa / Orange Money / Cash (Flux simplifié)
```
Utilisateur → Formulaire → Backend → Génération référence → Instructions → Paiement manuel → Validation
```

---

## Modes de paiement

### 1. Paiement en Caisse (Cash)
- Génère une référence
- Affiche un QR code
- L'utilisateur présente le QR à la caisse
- Un agent valide le paiement

### 2. M-Pesa (Paiement manuel)
- Génère une référence
- Affiche les instructions M-Pesa :
  - Composer `*1122#`
  - Choisir "5 - Mes paiements"
  - Entrer le numéro `097435`
  - Suivre les étapes
  - Entrer le montant
  - Valider avec PIN
- L'utilisateur effectue le paiement manuellement
- Le système attend la confirmation

### 3. Orange Money (Paiement manuel)
- Génère une référence
- Affiche les instructions Orange Money :
  - Composer `#144#`
  - Sélectionner "Paiement marchand"
  - Entrer le numéro marchand
  - Entrer le montant
  - Entrer la référence
  - Valider avec PIN
- L'utilisateur effectue le paiement manuellement
- Le système attend la confirmation

### 4. MaxiCash (Redirection automatique)
- Redirige vers MaxiCash
- L'utilisateur choisit son mode (Mobile Money, Carte, PayPal)
- Paiement sur la plateforme MaxiCash
- Redirection automatique après paiement

---

## Backend - Modifications

### TicketController.php

```php
// Pour cash, mpesa et orange_money : retourner directement le ticket
if (in_array($gateway, ['cash', 'mpesa', 'orange_money'])) {
    return response()->json([
        'success' => true,
        'payment_mode' => $gateway,
        'ticket' => [
            'reference' => $ticket->reference,
            'full_name' => $ticket->full_name,
            // ... autres infos
        ],
        'message' => 'Ticket créé avec succès. Suivez les instructions pour effectuer le paiement.',
    ], 201);
}

// Pour maxicash : redirection
$paymentService = PaymentGatewayFactory::create($gateway);
$result = $paymentService->initiatePaymentForTicket($ticket, $urls);
// ...
```

### Statut des tickets

Tous les modes manuels utilisent le statut `pending_cash` :

```php
'payment_status' => in_array($gateway, ['cash', 'mpesa', 'orange_money']) 
    ? 'pending_cash' 
    : 'pending',
```

---

## Frontend - Modifications

### EventInscriptionPage.tsx

#### Étape 4 : Soumission

```typescript
const handleSubmit = async () => {
  const res = await axios.post(`${API_URL}/events/${event.id}/register`, payload);

  if (res.data.success) {
    // Pour cash, mpesa et orange_money : afficher le billet avec instructions
    if (['cash', 'mpesa', 'orange_money'].includes(res.data.payment_mode)) {
      setPaymentMode(res.data.payment_mode);
      setTicketData(res.data.ticket);
      setStep(5); // Aller à l'étape 5
    } 
    // Pour maxicash : redirection
    else if (res.data.redirect_url) {
      window.location.href = res.data.redirect_url;
    }
  }
};
```

#### Étape 5 : Affichage conditionnel

```typescript
{step === 5 && ['cash', 'mpesa', 'orange_money'].includes(paymentMode || '') && ticketData && (
  <div>
    {/* Instructions M-Pesa */}
    {paymentMode === 'mpesa' && (
      <div className="bg-green-50">
        <h3>Paiement M-Pesa</h3>
        <ol>
          <li>Composez *1122#</li>
          <li>Choisissez 5 - Mes paiements</li>
          {/* ... */}
        </ol>
      </div>
    )}

    {/* Instructions Orange Money */}
    {paymentMode === 'orange_money' && (
      <div className="bg-orange-50">
        <h3>Paiement Orange Money</h3>
        <ol>
          <li>Composez #144#</li>
          {/* ... */}
        </ol>
      </div>
    )}

    {/* Instructions Cash */}
    {paymentMode === 'cash' && (
      <div className="bg-blue-50">
        <p>Présentez ce billet à la caisse</p>
      </div>
    )}

    {/* Billet avec QR code */}
    <div id="ticket-to-download">
      {/* ... */}
    </div>
  </div>
)}
```

---

## Instructions de paiement

### M-Pesa - Étapes détaillées

D'après l'image fournie, voici les étapes M-Pesa :

1. **Composez** : `*1122#` sur votre compte Dollar ou Franc
2. **Choisissez** : `5 - Mes paiements`
3. **Entrez le numéro** : `097435`
4. **Sélectionnez** : La raison de transaction
5. **Indiquez** : Le numéro de caisse
6. **Entrez** : Le montant
7. **Validez** : Avec votre PIN M-Pesa

Alternative : Utilisez l'application M-Pesa RDC (Google Play / App Store)

### Orange Money - Étapes

1. **Composez** : `#144#` sur votre téléphone Orange
2. **Sélectionnez** : Paiement marchand
3. **Entrez** : Le numéro marchand `[À CONFIGURER]`
4. **Entrez** : Le montant
5. **Entrez** : Votre référence (du billet)
6. **Validez** : Avec votre code PIN Orange Money

Alternative : Utilisez l'application Orange Money

---

## Configuration

### Variables d'environnement

Les services M-Pesa et Orange Money sont optionnels. Si les identifiants ne sont pas configurés, le système fonctionne quand même en mode "instructions manuelles".

```env
# M-Pesa (optionnel)
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=
MPESA_SANDBOX=true

# Orange Money (optionnel)
ORANGE_MONEY_MERCHANT_ID=
ORANGE_MONEY_MERCHANT_KEY=
ORANGE_MONEY_SANDBOX=true
```

### Numéros de paiement

À configurer dans le frontend ou via variables d'environnement :

- **M-Pesa** : `097435` (numéro de caisse)
- **Orange Money** : `[NUMERO_MARCHAND]` (à obtenir auprès d'Orange)

---

## Validation des paiements

### Pour les paiements manuels (Cash, M-Pesa, Orange Money)

Les paiements doivent être validés manuellement par un administrateur :

```http
POST /api/tickets/{reference}/validate-cash
Authorization: Bearer {admin_token}

Response:
{
  "success": true,
  "message": "Paiement validé avec succès",
  "ticket": {
    "reference": "ABC123XYZ",
    "status": "completed"
  }
}
```

### Liste des paiements en attente

```http
GET /api/tickets/pending-cash
Authorization: Bearer {admin_token}

Response:
{
  "success": true,
  "count": 5,
  "tickets": [...]
}
```

---

## Avantages du flux simplifié

### Pour M-Pesa et Orange Money

1. **Pas de dépendance API** : Fonctionne même si les APIs sont indisponibles
2. **Pas de configuration complexe** : Pas besoin de configurer les webhooks
3. **Contrôle total** : L'utilisateur effectue le paiement à son rythme
4. **Moins d'erreurs** : Pas de problèmes de timeout ou de redirection
5. **Traçabilité** : La référence permet de suivre le paiement

### Pour MaxiCash

1. **Automatisation** : Paiement et confirmation automatiques
2. **Plusieurs modes** : Mobile Money, Carte, PayPal en un seul flux
3. **Expérience fluide** : Redirection et retour automatiques

---

## Flux utilisateur complet

### Exemple : Paiement M-Pesa

1. **Étape 1** : L'utilisateur choisit son tarif
2. **Étape 2** : Il remplit ses informations
3. **Étape 3** : Il sélectionne "M-Pesa"
4. **Étape 4** : Il confirme et soumet
5. **Étape 5** : Il voit :
   - Sa référence : `ABC123XYZ`
   - Les instructions M-Pesa détaillées
   - Son billet avec QR code
   - Montant à payer : `50 USD`
6. **Action** : Il compose `*1122#` et suit les étapes
7. **Validation** : Un admin valide le paiement dans le système
8. **Confirmation** : L'utilisateur reçoit un email de confirmation

---

## Tests

### Test 1 : Paiement M-Pesa

1. Créez une inscription avec mode "mpesa"
2. Vérifiez que les instructions M-Pesa s'affichent
3. Vérifiez que le billet a un fond vert (couleur M-Pesa)
4. Vérifiez que la référence est générée
5. Vérifiez que le QR code contient `payment_mode: "mpesa"`

### Test 2 : Paiement Orange Money

1. Créez une inscription avec mode "orange_money"
2. Vérifiez que les instructions Orange Money s'affichent
3. Vérifiez que le billet a un fond orange
4. Vérifiez que la référence est dans les instructions

### Test 3 : Paiement Cash

1. Créez une inscription avec mode "cash"
2. Vérifiez que les instructions caisse s'affichent
3. Vérifiez que le billet a un fond bleu

### Test 4 : Paiement MaxiCash

1. Créez une inscription avec mode "maxicash"
2. Vérifiez la redirection vers MaxiCash
3. En mode sandbox, vérifiez le retour automatique

---

## Dépannage

### Les instructions ne s'affichent pas

**Problème** : L'étape 5 ne montre pas les instructions

**Solution** :
1. Vérifiez que `paymentMode` est bien défini
2. Vérifiez que `ticketData` contient les bonnes données
3. Ouvrez la console pour voir les erreurs

### Le billet ne se télécharge pas

**Problème** : Le PDF ne se génère pas

**Solution** :
1. Vérifiez que `html2canvas` et `jspdf` sont installés
2. Vérifiez que l'élément `#ticket-to-download` existe
3. Testez l'impression d'abord (plus simple)

### La référence n'est pas générée

**Problème** : Le backend ne retourne pas de référence

**Solution** :
1. Vérifiez les logs Laravel
2. Vérifiez que le ticket est bien créé en base
3. Vérifiez la réponse API dans la console

---

## Prochaines étapes

1. **Automatisation M-Pesa** : Intégrer l'API M-Pesa pour validation automatique
2. **Automatisation Orange Money** : Intégrer l'API Orange Money
3. **Notifications SMS** : Envoyer un SMS avec la référence
4. **Rappels** : Envoyer des rappels pour les paiements en attente
5. **Dashboard admin** : Interface pour valider les paiements manuellement

---

## Support

Pour toute question :
- Documentation API : `API_DOCUMENTATION.md`
- Guide des modes de paiement : `PAYMENT_GATEWAYS_GUIDE.md`
- Frontend : `FRONTEND_MODES_PAIEMENT.md`
