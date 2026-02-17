# Correction du problème d'inscription MaxiCash (Erreur 422) - RÉSOLU ✅

## Problème identifié et résolu

Vous receviez une erreur 422 lors de l'inscription à un événement. Voici les causes et solutions:

### 1. ❌ URL API MaxiCash incorrecte (CAUSE PRINCIPALE)

**Problème**: L'URL de l'API MaxiCash dans `.env` était incorrecte
```env
# ❌ INCORRECT (ancien)
MAXICASH_API_URL=https://api-testbed.maxicashme.com/Merchant/api.asmx
MAXICASH_REDIRECT_BASE=https://api-testbed.maxicashme.com
```

**Solution**: Utiliser les URLs officielles de la documentation MaxiCash
```env
# ✅ CORRECT (nouveau)
MAXICASH_API_URL=https://webapi-test.maxicashapp.com
MAXICASH_REDIRECT_BASE=https://api-testbed.maxicashapp.com
```

**Erreurs corrigées**:
- Domaine: `maxicashme.com` → `maxicashapp.com`
- URL API: Retrait du suffixe `/Merchant/api.asmx`
- Le service appelle maintenant `/Integration/PayEntryWeb` correctement

### 2. ✅ Incohérence des adresses IP (CORRIGÉ)

**Problème**: Les URLs MaxiCash utilisaient une IP différente
- **Frontend**: `http://192.168.58.9:8080`
- **Backend**: `http://192.168.58.9:8000`
- **URLs MaxiCash dans .env**: `http://192.168.241.9:8080` ❌

**Solution**: Toutes les URLs utilisent maintenant `192.168.58.9`

## ✅ Corrections appliquées

### Fichier `.env`
```env
# MaxiCash (paiement événements)
MAXICASH_MERCHANT_ID=d8c40788ed214f8ca34b6a85957f36c6
MAXICASH_MERCHANT_PASSWORD=a3681a640e194d66beba4af72fa14674
MAXICASH_WEBHOOK_SECRET=
MAXICASH_SANDBOX=true
MAXICASH_API_URL=https://webapi-test.maxicashapp.com
MAXICASH_REDIRECT_BASE=https://api-testbed.maxicashapp.com
MAXICASH_LANGUAGE=fr
# URLs avec IP locale (frontend)
MAXICASH_SUCCESS_URL=http://192.168.58.9:8080/paiement/success
MAXICASH_FAILURE_URL=http://192.168.58.9:8080/paiement/failure
MAXICASH_CANCEL_URL=http://192.168.58.9:8080/paiement/cancel
# Webhook backend
MAXICASH_NOTIFY_URL=http://192.168.58.9:8000/api/webhooks/maxicash
```

### Fichier `MaxiCashService.php`
- ✅ URL API corrigée: `{$this->apiUrl}/Integration/PayEntryWeb`
- ✅ Validation stricte des paramètres (aucune valeur null)
- ✅ Gestion correcte du mode sandbox

## 🎯 Test de validation

Le test `php test-inscription-complete.php` confirme que tout fonctionne:

```
✓ Ticket créé avec succès
✓ Paiement initié avec succès
- Log ID: 97759
- Redirect URL: https://api-testbed.maxicashapp.com/payentryweb?logid=97759
```

## Validation requise par le backend

D'après `StoreTicketRequest.php`, voici les règles de validation:

| Champ | Requis | Type | Règles |
|-------|--------|------|--------|
| `event_price_id` | ✅ Oui | integer | Doit exister dans `event_prices` |
| `full_name` | ✅ Oui | string | Min: 3 caractères, Max: 255 |
| `email` | ✅ Oui | email | Format email valide, Max: 255 |
| `phone` | ✅ Oui | string | Min: 9 caractères, Max: 50 |
| `days` | ❌ Non | integer | Min: 1 (défaut: 1) |
| `pay_type` | ✅ Oui | string | Valeurs: `online` ou `cash` |
| `pay_sub_type` | ❌ Non | string | Max: 50 |
| `success_url` | ❌ Non | url | URL valide, Max: 500 |
| `cancel_url` | ❌ Non | url | URL valide, Max: 500 |
| `failure_url` | ❌ Non | url | URL valide, Max: 500 |

## Solution: Corriger votre code frontend

### Option 1: Utiliser les URLs par défaut (RECOMMANDÉ)

Ne pas envoyer `success_url`, `cancel_url`, `failure_url` dans le payload. Le backend utilisera automatiquement les URLs configurées dans `.env`:

```typescript
const payload = {
  event_price_id: selectedPrice.id,
  full_name: formData.fullName,
  email: formData.email,
  phone: formData.phone,
  days: 1,
  pay_type: 'online'
  // Ne pas inclure success_url, cancel_url, failure_url
};
```

### Option 2: Envoyer les URLs correctes

Si vous devez envoyer les URLs, assurez-vous qu'elles sont valides:

```typescript
const baseUrl = 'http://192.168.58.9:8080'; // Votre IP actuelle

const payload = {
  event_price_id: selectedPrice.id,
  full_name: formData.fullName,
  email: formData.email,
  phone: formData.phone,
  days: 1,
  pay_type: 'online',
  success_url: `${baseUrl}/paiement/success`,
  cancel_url: `${baseUrl}/paiement/cancel`,
  failure_url: `${baseUrl}/paiement/failure`
};
```

## Vérifications à faire

### 1. Vérifier que l'event_price_id existe

```bash
php artisan tinker
```

```php
// Vérifier les prix disponibles pour l'événement 1
\App\Models\EventPrice::where('event_id', 1)->get(['id', 'category', 'duration_type', 'amount']);
```

### 2. Tester avec cURL

```bash
curl -X POST http://192.168.58.9:8000/api/events/1/register \
  -H "Content-Type: application/json" \
  -d '{
    "event_price_id": 2,
    "full_name": "Franck Kapuya",
    "email": "franckkapuya13@gmail.com",
    "phone": "+243822902681",
    "days": 1,
    "pay_type": "online"
  }'
```

### 3. Vérifier les logs Laravel

```bash
tail -f storage/logs/laravel.log
```

## Flux complet de paiement

1. **Frontend** → Envoie le payload à `/api/events/1/register`
2. **Backend** → Valide les données (`StoreTicketRequest`)
3. **Backend** → Crée un ticket avec référence unique (ex: `XQECJYUN4O`)
4. **Backend** → Appelle MaxiCash via `MaxiCashService::initiatePaymentForTicket()`
5. **MaxiCash** → Retourne un `LogID`
6. **Backend** → Retourne `redirect_url` au frontend
7. **Frontend** → Redirige l'utilisateur vers MaxiCash
8. **Utilisateur** → Effectue le paiement sur MaxiCash
9. **MaxiCash** → Redirige vers `success_url?reference=XQECJYUN4O`
10. **Frontend** → Affiche la page de succès avec la référence

## Mode Sandbox

Si vous êtes en mode sandbox (`MAXICASH_SANDBOX=true`), le backend simule le paiement sans appeler MaxiCash. Vous serez redirigé directement vers la page de succès.

Pour tester avec le vrai MaxiCash:
```env
MAXICASH_SANDBOX=false
```

## Commandes utiles

```bash
# Redémarrer le serveur après modification du .env
php artisan config:clear
php artisan cache:clear

# Voir les routes disponibles
php artisan route:list --path=api/events

# Tester la connexion à la base de données
php artisan tinker
>>> \App\Models\Event::count()
>>> \App\Models\EventPrice::count()
```

## Résumé des corrections

✅ **Backend (.env)**: URLs MaxiCash corrigées avec la bonne IP (`192.168.58.9`)
⚠️ **Frontend**: Retirer les URLs du payload OU utiliser la bonne IP
⚠️ **Frontend**: Vérifier que `event_price_id` existe dans la base de données


## 📋 Payload frontend requis

Votre payload est déjà correct! Voici ce qui est attendu:

```json
{
  "event_price_id": 2,
  "full_name": "Franck Kapuya",
  "email": "franckkapuya13@gmail.com",
  "phone": "+243822902681",
  "days": 1,
  "pay_type": "online"
}
```

**Note**: Les URLs de callback (`success_url`, `cancel_url`, `failure_url`) sont optionnelles. Si vous ne les envoyez pas, le backend utilisera automatiquement celles configurées dans `.env`.

## Validation requise par le backend

D'après `StoreTicketRequest.php`, voici les règles de validation:

| Champ | Requis | Type | Règles |
|-------|--------|------|--------|
| `event_price_id` | ✅ Oui | integer | Doit exister dans `event_prices` |
| `full_name` | ✅ Oui | string | Min: 3 caractères, Max: 255 |
| `email` | ✅ Oui | email | Format email valide, Max: 255 |
| `phone` | ✅ Oui | string | Min: 9 caractères, Max: 50 |
| `days` | ❌ Non | integer | Min: 1 (défaut: 1) |
| `pay_type` | ✅ Oui | string | Valeurs: `online` ou `cash` |
| `success_url` | ❌ Non | url | URL valide, Max: 500 |
| `cancel_url` | ❌ Non | url | URL valide, Max: 500 |
| `failure_url` | ❌ Non | url | URL valide, Max: 500 |

## 🚀 Tester l'inscription

### Avec cURL
```bash
curl -X POST http://192.168.58.9:8000/api/events/1/register \
  -H "Content-Type: application/json" \
  -d '{
    "event_price_id": 2,
    "full_name": "Franck Kapuya",
    "email": "franckkapuya13@gmail.com",
    "phone": "+243822902681",
    "days": 1,
    "pay_type": "online"
  }'
```

### Réponse attendue
```json
{
  "success": true,
  "payment_mode": "online",
  "reference": "XQECJYUN4O",
  "redirect_url": "https://api-testbed.maxicashapp.com/payentryweb?logid=97759",
  "log_id": "97759",
  "message": "Redirection vers MaxiCash pour finaliser le paiement (Mobile Money, Visa, Carte ou PayPal)."
}
```

## 🔄 Flux complet de paiement

1. **Frontend** → Envoie le payload à `/api/events/1/register`
2. **Backend** → Valide les données (`StoreTicketRequest`)
3. **Backend** → Crée un ticket avec référence unique (ex: `XQECJYUN4O`)
4. **Backend** → Appelle MaxiCash via `MaxiCashService::initiatePaymentForTicket()`
5. **MaxiCash** → Retourne un `LogID`
6. **Backend** → Retourne `redirect_url` au frontend
7. **Frontend** → Redirige l'utilisateur vers MaxiCash
8. **Utilisateur** → Effectue le paiement sur MaxiCash
9. **MaxiCash** → Redirige vers `success_url?reference=XQECJYUN4O`
10. **Frontend** → Affiche la page de succès avec la référence

## 📚 Documentation MaxiCash officielle

D'après [developer.maxicashme.com](https://developer.maxicashme.com/):

### URLs API (Sandbox)
- **API**: `https://webapi-test.maxicashapp.com/Integration/PayEntryWeb`
- **Gateway**: `https://api-testbed.maxicashapp.com/payentryweb?logid={LogID}`

### URLs API (Production)
- **API**: `https://webapi.maxicashapp.com/Integration/PayEntryWeb`
- **Gateway**: `https://api.maxicashapp.com/payentryweb?logid={LogID}`

## ⚠️ Important

- Les montants doivent être envoyés en **centimes** (15.00 USD = 1500 centimes)
- La devise `USD` est convertie en `maxiDollar` automatiquement
- Le mode sandbox simule les paiements si `MAXICASH_MERCHANT_ID` est vide
- Pour la production, configurez `MAXICASH_SANDBOX=false`

## 🎉 Résumé

✅ **URL API MaxiCash corrigée** dans `.env`
✅ **IP cohérente** pour toutes les URLs de callback
✅ **Service MaxiCashService** mis à jour
✅ **Tests validés** avec succès

Votre backend est maintenant prêt à recevoir les inscriptions et à rediriger vers MaxiCash!
