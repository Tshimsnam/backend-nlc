# Guide des Modes de Paiement

Ce système supporte 4 modes de paiement différents :

## 1. Paiement en Caisse (Cash)

Le mode le plus simple - génère un QR code que l'utilisateur présente à la caisse.

### Configuration
Aucune configuration requise. Ce mode fonctionne immédiatement.

### Flux
1. L'utilisateur choisit "Paiement en caisse"
2. Un ticket avec QR code est généré
3. L'utilisateur présente le QR code à la caisse
4. Un administrateur scanne et valide le paiement

---

## 2. MaxiCash

Agrégateur de paiement supportant Mobile Money, Cartes bancaires, PayPal, etc.

### Configuration

Ajoutez ces variables dans votre fichier `.env` :

```env
MAXICASH_MERCHANT_ID=votre_merchant_id
MAXICASH_MERCHANT_PASSWORD=votre_password
MAXICASH_WEBHOOK_SECRET=votre_secret
MAXICASH_SANDBOX=true
MAXICASH_API_URL=https://webapi-test.maxicashapp.com
MAXICASH_REDIRECT_BASE=https://api-testbed.maxicashapp.com
MAXICASH_LANGUAGE=fr
MAXICASH_SUCCESS_URL=https://votre-frontend.com/paiement/success
MAXICASH_FAILURE_URL=https://votre-frontend.com/paiement/failure
MAXICASH_CANCEL_URL=https://votre-frontend.com/paiement/cancel
MAXICASH_NOTIFY_URL=https://votre-api.com/api/webhooks/maxicash
```

### Production
Pour passer en production :
- Changez `MAXICASH_SANDBOX=false`
- Utilisez les URLs de production :
  - API: `https://webapi.maxicashapp.com`
  - Redirect: `https://api.maxicashapp.com`

### Webhook
Route : `POST /api/webhooks/maxicash`

---

## 3. M-Pesa (Safaricom - Kenya)

Service de paiement mobile populaire au Kenya.

### Configuration

Ajoutez ces variables dans votre fichier `.env` :

```env
MPESA_CONSUMER_KEY=votre_consumer_key
MPESA_CONSUMER_SECRET=votre_consumer_secret
MPESA_SHORTCODE=votre_shortcode
MPESA_PASSKEY=votre_passkey
MPESA_SANDBOX=true
MPESA_API_URL=https://sandbox.safaricom.co.ke
```

### Obtenir les identifiants

1. Créez un compte sur [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Créez une application
3. Obtenez vos Consumer Key et Consumer Secret
4. Configurez votre Shortcode et Passkey pour STK Push

### Production
Pour passer en production :
- Changez `MPESA_SANDBOX=false`
- Utilisez l'URL de production : `https://api.safaricom.co.ke`

### Webhook
Route : `POST /api/webhooks/mpesa`

### Particularités
- Nécessite un numéro de téléphone valide (format: 254XXXXXXXXX)
- Le paiement se fait directement sur le téléphone de l'utilisateur (STK Push)
- Pas de redirection vers une page externe

---

## 4. Orange Money

Service de paiement mobile d'Orange disponible dans plusieurs pays africains.

### Configuration

Ajoutez ces variables dans votre fichier `.env` :

```env
ORANGE_MONEY_MERCHANT_ID=votre_merchant_id
ORANGE_MONEY_MERCHANT_KEY=votre_merchant_key
ORANGE_MONEY_SANDBOX=true
ORANGE_MONEY_API_URL=https://api.orange.com/orange-money-webpay/dev/v1
```

### Obtenir les identifiants

1. Créez un compte sur [Orange Developer Portal](https://developer.orange.com/)
2. Souscrivez à l'API Orange Money
3. Obtenez votre Merchant ID et Merchant Key

### Production
Pour passer en production :
- Changez `ORANGE_MONEY_SANDBOX=false`
- Utilisez l'URL de production fournie par Orange

### Webhook
Route : `POST /api/webhooks/orange-money`

### Particularités
- Nécessite un numéro de téléphone valide
- Redirige vers une page Orange Money pour finaliser le paiement

---

## Utilisation dans l'API

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

### Créer un ticket avec un mode de paiement spécifique

```http
POST /api/events/{event}/tickets
Content-Type: application/json

{
  "event_price_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "254712345678",
  "pay_type": "mpesa",
  "success_url": "https://frontend.com/success",
  "failure_url": "https://frontend.com/failure",
  "cancel_url": "https://frontend.com/cancel"
}
```

Valeurs possibles pour `pay_type` :
- `cash` - Paiement en caisse
- `maxicash` - MaxiCash
- `mpesa` - M-Pesa
- `orange_money` - Orange Money

---

## Mode Sandbox

Tous les services de paiement supportent un mode sandbox pour les tests :

- Aucun paiement réel n'est effectué
- Les transactions sont simulées
- Utile pour le développement et les tests

Pour activer le mode sandbox, assurez-vous que les variables `*_SANDBOX` sont à `true`.

---

## Sécurité

### Webhooks

Tous les webhooks doivent être sécurisés :

1. **MaxiCash** : Utilisez `MAXICASH_WEBHOOK_SECRET` pour vérifier les signatures
2. **M-Pesa** : Vérifiez l'origine des requêtes (IP whitelisting)
3. **Orange Money** : Vérifiez les tokens de notification

### URLs publiques

Les webhooks nécessitent des URLs publiques accessibles depuis Internet :

- Utilisez Cloudflare Tunnel, ngrok, ou localtunnel en développement
- Utilisez un domaine HTTPS en production

---

## Dépannage

### Le paiement ne se déclenche pas

1. Vérifiez que les identifiants sont corrects dans `.env`
2. Vérifiez que le mode sandbox est activé si vous testez
3. Consultez les logs Laravel : `storage/logs/laravel.log`

### Le webhook ne fonctionne pas

1. Vérifiez que l'URL du webhook est accessible publiquement
2. Testez l'URL avec curl ou Postman
3. Vérifiez les logs du service de paiement

### Erreur "Gateway non supporté"

Vérifiez que la valeur de `pay_type` correspond à l'un des gateways disponibles :
- `cash`
- `maxicash`
- `mpesa`
- `orange_money`

---

## Support

Pour toute question ou problème :

1. Consultez la documentation officielle de chaque service
2. Vérifiez les logs Laravel
3. Contactez le support technique du service de paiement concerné
