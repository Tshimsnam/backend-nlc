# URLs Officielles MaxiCash

Source: [developer.maxicashme.com](https://developer.maxicashme.com/)

## Environnement Sandbox (Test)

### API Endpoints
- **Base URL**: `https://webapi-test.maxicashapp.com`
- **PayEntryWeb**: `https://webapi-test.maxicashapp.com/Integration/PayEntryWeb`
- **Merchant API**: `https://api-testbed.maxicashapp.com/Merchant/api.asmx`

### Gateway (Redirection utilisateur)
- **Base URL**: `https://api-testbed.maxicashapp.com`
- **PayEntryWeb Redirect**: `https://api-testbed.maxicashapp.com/payentryweb?logid={LogID}`
- **PayEntry**: `https://api-testbed.maxicashapp.com/PayEntry`
- **PayEntryPost**: `https://api-testbed.maxicashapp.com/PayEntryPost`

## Environnement Production (Live)

### API Endpoints
- **Base URL**: `https://webapi.maxicashapp.com`
- **PayEntryWeb**: `https://webapi.maxicashapp.com/Integration/PayEntryWeb`
- **Merchant API**: `https://api.maxicashapp.com/Merchant/api.asmx`

### Gateway (Redirection utilisateur)
- **Base URL**: `https://api.maxicashapp.com`
- **PayEntryWeb Redirect**: `https://api.maxicashapp.com/payentryweb?logid={LogID}`
- **PayEntry**: `https://api.maxicashapp.com/PayEntry`
- **PayEntryPost**: `https://api.maxicashapp.com/PayEntryPost`

## Configuration Laravel (.env)

### Sandbox
```env
MAXICASH_SANDBOX=true
MAXICASH_API_URL=https://webapi-test.maxicashapp.com
MAXICASH_REDIRECT_BASE=https://api-testbed.maxicashapp.com
```

### Production
```env
MAXICASH_SANDBOX=false
MAXICASH_API_URL=https://webapi.maxicashapp.com
MAXICASH_REDIRECT_BASE=https://api.maxicashapp.com
```

## Méthodes d'intégration

### 1. PayEntryWeb (REST API) - RECOMMANDÉ
Flux en 2 étapes:
1. **Étape 1**: Appel API POST à `/Integration/PayEntryWeb` → Retourne un `LogID`
2. **Étape 2**: Redirection vers `/payentryweb?logid={LogID}`

**Avantages**:
- Architecture REST
- Contrôle total du flux
- Idéal pour applications web/mobile modernes

### 2. PayEntryPost (Form POST)
Soumission directe d'un formulaire HTML vers `/PayEntryPost`

**Avantages**:
- Simple à implémenter
- Pas besoin d'API REST
- Idéal pour sites web simples

### 3. PayEntry (QueryString)
Redirection avec paramètres dans l'URL

**Avantages**:
- Très simple
- Pas de formulaire nécessaire

## Paramètres requis (PayEntryWeb)

```json
{
  "PayType": "MaxiCash",
  "MerchantID": "votre_merchant_id",
  "MerchantPassword": "votre_merchant_password",
  "Amount": "1500",  // En centimes (15.00 USD = 1500)
  "Currency": "maxiDollar",  // ou "maxiRand"
  "Language": "fr",  // ou "en"
  "Reference": "UNIQUE_REF",
  "SuccessURL": "https://votre-site.com/success",
  "FailureURL": "https://votre-site.com/failure",
  "CancelURL": "https://votre-site.com/cancel",
  "NotifyURL": "https://votre-site.com/webhook",  // Optionnel mais recommandé
  "Email": "user@example.com",  // Optionnel
  "Telephone": "+243123456789"  // Optionnel (requis pour Mobile Money)
}
```

## Réponse PayEntryWeb

### Succès
```json
{
  "SessionToken": null,
  "ResponseStatus": "success",
  "ResponseError": "",
  "ResponseData": "123456",
  "ResponseDesc": "LogID",
  "TransactionID": "sample string 6",
  "LogID": "123456",
  "Reference": null
}
```

### Erreur
```json
{
  "ResponseStatus": "error",
  "ResponseError": "Message d'erreur",
  "ResponseData": null
}
```

## Devises supportées

| Code | Nom | Équivalence |
|------|-----|-------------|
| `maxiDollar` | MaxiDollar | 1:1 avec USD |
| `maxiRand` | MaxiRand | 1:1 avec ZAR |
| `USD` | US Dollar | Converti en maxiDollar |
| `ZAR` | South African Rand | Converti en maxiRand |

## Modes de paiement disponibles

Via MaxiCash Gateway, l'utilisateur peut payer avec:
- **MaxiCash Wallet** (compte MaxiCash)
- **Mobile Money** (Airtel, Orange, Vodacom, etc.)
- **Carte bancaire** (Visa, Mastercard)
- **PayPal**
- **Pepele Mobile**

## Webhooks (NotifyURL)

MaxiCash envoie une notification à votre `NotifyURL` avec ces paramètres:

```
?PayType=MaxiCash
&Amount=1500
&Currency=maxiDollar
&Reference=UNIQUE_REF
&TransactionID=123456
&Status=Success
&LogID=123456
```

**Important**: Validez toujours les webhooks en vérifiant la signature ou en appelant l'API de vérification.

## Sécurité

1. **Ne jamais exposer** `MerchantID` et `MerchantPassword` côté client
2. **Toujours valider** les webhooks côté serveur
3. **Utiliser HTTPS** pour toutes les URLs de callback
4. **Vérifier** le statut de la transaction via l'API après réception du webhook

## Support

- **Email**: info@maxicashapp.com
- **Documentation**: https://developer.maxicashme.com/
- **Télécharger l'app**: Disponible sur iOS et Android

## Notes importantes

- Les montants sont **toujours en centimes** (15.00 USD = 1500)
- Le `Reference` doit être **unique** par transaction
- Le `LogID` est valide pendant **24 heures**
- En sandbox, utilisez les identifiants de test fournis par MaxiCash
- Pour passer en production, contactez MaxiCash pour obtenir vos identifiants live

---

**Dernière mise à jour**: Février 2026
**Source**: Documentation officielle MaxiCash
