# Nouveaux Modes de Paiement - M-Pesa et Orange Money

## Résumé des changements

Le système de paiement a été étendu pour supporter 4 modes de paiement :

1. **Paiement en caisse** (existant)
2. **MaxiCash** (existant)
3. **M-Pesa** (NOUVEAU)
4. **Orange Money** (NOUVEAU)

---

## Fichiers créés

### Services de paiement

1. **`app/Services/Payments/MpesaService.php`**
   - Gère les paiements M-Pesa (Safaricom - Kenya)
   - Utilise STK Push pour initier les paiements
   - Supporte le mode sandbox pour les tests

2. **`app/Services/Payments/OrangeMoneyService.php`**
   - Gère les paiements Orange Money
   - Redirige vers la page Orange Money pour finaliser
   - Supporte le mode sandbox pour les tests

3. **`app/Services/Payments/PaymentGatewayFactory.php`**
   - Factory pour créer les instances de services de paiement
   - Centralise la logique de sélection du gateway
   - Liste les gateways disponibles

### Webhooks

4. **`app/Http/Controllers/Webhooks/MpesaWebhookController.php`**
   - Traite les callbacks M-Pesa
   - Met à jour le statut des tickets

5. **`app/Http/Controllers/Webhooks/OrangeMoneyWebhookController.php`**
   - Traite les notifications Orange Money
   - Met à jour le statut des tickets

### Documentation

6. **`PAYMENT_GATEWAYS_GUIDE.md`**
   - Guide complet de configuration
   - Instructions pour chaque mode de paiement
   - Exemples d'utilisation de l'API

7. **`NOUVEAUX_MODES_PAIEMENT.md`** (ce fichier)
   - Résumé des changements
   - Liste des fichiers modifiés

---

## Fichiers modifiés

### Configuration

1. **`.env.example`**
   - Ajout des variables M-Pesa :
     ```env
     MPESA_CONSUMER_KEY=
     MPESA_CONSUMER_SECRET=
     MPESA_SHORTCODE=
     MPESA_PASSKEY=
     MPESA_SANDBOX=true
     MPESA_API_URL=https://sandbox.safaricom.co.ke
     ```
   - Ajout des variables Orange Money :
     ```env
     ORANGE_MONEY_MERCHANT_ID=
     ORANGE_MONEY_MERCHANT_KEY=
     ORANGE_MONEY_SANDBOX=true
     ORANGE_MONEY_API_URL=https://api.orange.com/orange-money-webpay/dev/v1
     ```

2. **`config/services.php`**
   - Ajout de la configuration M-Pesa
   - Ajout de la configuration Orange Money

### Contrôleurs

3. **`app/Http/Controllers/API/PaymentController.php`**
   - Utilise maintenant `PaymentGatewayFactory` au lieu de `MaxiCashService` directement
   - Supporte tous les gateways de paiement
   - Nouvelle méthode `gateways()` pour lister les modes disponibles

4. **`app/Http/Controllers/API/TicketController.php`**
   - Méthode `paymentModes()` mise à jour avec les 4 modes
   - Méthode `store()` modifiée pour supporter tous les gateways
   - Utilise `PaymentGatewayFactory` pour créer le service approprié

### Routes

5. **`routes/api.php`**
   - Ajout de la route webhook M-Pesa : `POST /api/webhooks/mpesa`
   - Ajout de la route webhook Orange Money : `POST /api/webhooks/orange-money`
   - Correction de la route payment-modes : `GET /api/events/{event}/tickets/payment-modes`

---

## Configuration requise

### 1. Copier les nouvelles variables d'environnement

```bash
# Copiez les nouvelles variables de .env.example vers .env
```

### 2. Configurer M-Pesa (optionnel)

Si vous voulez utiliser M-Pesa :

1. Créez un compte sur [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Créez une application et obtenez vos identifiants
3. Remplissez les variables `MPESA_*` dans `.env`

### 3. Configurer Orange Money (optionnel)

Si vous voulez utiliser Orange Money :

1. Créez un compte sur [Orange Developer Portal](https://developer.orange.com/)
2. Souscrivez à l'API Orange Money
3. Remplissez les variables `ORANGE_MONEY_*` dans `.env`

### 4. Mode Sandbox

Par défaut, tous les services sont en mode sandbox :
- Aucun paiement réel n'est effectué
- Les transactions sont simulées
- Parfait pour le développement et les tests

Pour activer le mode production, changez les variables `*_SANDBOX` à `false`.

---

## Utilisation de l'API

### Lister les modes de paiement disponibles

```http
GET /api/events/{event}/tickets/payment-modes
```

Réponse :
```json
[
  {
    "id": "cash",
    "label": "Paiement en caisse",
    "description": "Générez votre QR code et payez directement à la caisse.",
    "requires_phone": false
  },
  {
    "id": "maxicash",
    "label": "MaxiCash",
    "description": "Payez via MaxiCash (Mobile Money, Carte bancaire, PayPal, etc.)",
    "requires_phone": false
  },
  {
    "id": "mpesa",
    "label": "M-Pesa",
    "description": "Payez via M-Pesa (Safaricom - Kenya)",
    "requires_phone": true
  },
  {
    "id": "orange_money",
    "label": "Orange Money",
    "description": "Payez via Orange Money",
    "requires_phone": true
  }
]
```

### Créer un ticket avec M-Pesa

```http
POST /api/events/{event}/register
Content-Type: application/json

{
  "event_price_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "254712345678",
  "pay_type": "mpesa",
  "success_url": "https://frontend.com/success",
  "failure_url": "https://frontend.com/failure"
}
```

### Créer un ticket avec Orange Money

```http
POST /api/events/{event}/register
Content-Type: application/json

{
  "event_price_id": 1,
  "full_name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+225 07 12 34 56 78",
  "pay_type": "orange_money",
  "success_url": "https://frontend.com/success",
  "failure_url": "https://frontend.com/failure"
}
```

---

## Webhooks

### URLs des webhooks

Les services de paiement enverront des notifications à ces URLs :

- **MaxiCash** : `POST /api/webhooks/maxicash`
- **M-Pesa** : `POST /api/webhooks/mpesa`
- **Orange Money** : `POST /api/webhooks/orange-money`

### Configuration des webhooks

Pour que les webhooks fonctionnent, vous devez :

1. **En développement** : Utiliser un tunnel (Cloudflare, ngrok, localtunnel)
2. **En production** : Utiliser un domaine HTTPS

Exemple avec Cloudflare Tunnel :
```bash
cloudflared tunnel --url http://localhost:8000
```

Puis configurez l'URL du webhook dans le portail du service de paiement.

---

## Tests

### Mode Sandbox

Tous les services supportent le mode sandbox :

```env
MPESA_SANDBOX=true
ORANGE_MONEY_SANDBOX=true
MAXICASH_SANDBOX=true
```

En mode sandbox :
- Aucun paiement réel n'est effectué
- Les transactions sont simulées automatiquement
- Parfait pour tester l'intégration

### Tester M-Pesa

1. Assurez-vous que `MPESA_SANDBOX=true`
2. Créez un ticket avec `pay_type=mpesa`
3. Le système simulera un paiement réussi

### Tester Orange Money

1. Assurez-vous que `ORANGE_MONEY_SANDBOX=true`
2. Créez un ticket avec `pay_type=orange_money`
3. Le système simulera un paiement réussi

---

## Dépannage

### Erreur "Gateway de paiement non supporté"

Vérifiez que la valeur de `pay_type` est l'une des suivantes :
- `cash`
- `maxicash`
- `mpesa`
- `orange_money`

### Le webhook ne fonctionne pas

1. Vérifiez que l'URL du webhook est accessible publiquement
2. Testez l'URL avec curl :
   ```bash
   curl -X POST https://votre-api.com/api/webhooks/mpesa
   ```
3. Consultez les logs Laravel : `storage/logs/laravel.log`

### Numéro de téléphone invalide

Pour M-Pesa, le format doit être : `254XXXXXXXXX` (Kenya)
Pour Orange Money, utilisez le format international : `+225 XX XX XX XX XX`

---

## Prochaines étapes

1. Configurez les identifiants des services que vous souhaitez utiliser
2. Testez en mode sandbox
3. Mettez à jour le frontend pour afficher les nouveaux modes de paiement
4. Passez en production quand vous êtes prêt

---

## Support

Pour toute question :

1. Consultez `PAYMENT_GATEWAYS_GUIDE.md` pour plus de détails
2. Vérifiez les logs Laravel
3. Consultez la documentation officielle de chaque service
