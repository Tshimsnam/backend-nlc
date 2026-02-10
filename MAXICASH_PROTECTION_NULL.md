# Protection contre les valeurs NULL - MaxiCash

## 🎯 Problème résolu

L'erreur "Object reference not set to an instance of an object" est causée par l'envoi de valeurs **null ou vides** à l'API MaxiCash. MaxiCash tente d'utiliser ces valeurs et plante avec une NullReferenceException.

## ✅ Protections mises en place

### 1. Validation au niveau de la Request (Couche 1)

**Fichier**: `app/Http/Requests/StoreTicketRequest.php`

Validation stricte **avant** que les données n'atteignent le contrôleur:

```php
'event_price_id' => ['required', 'integer', 'exists:event_prices,id'],
'full_name' => ['required', 'string', 'max:255', 'min:3'],
'email' => ['required', 'email', 'max:255'],
'phone' => ['required', 'string', 'max:50', 'min:9'],
'pay_type' => ['required', 'string', 'in:mobile_money,credit_card,maxicash,paypal'],
```

**Protection**:
- ✅ Tous les champs obligatoires sont vérifiés
- ✅ Format email validé
- ✅ Téléphone minimum 9 caractères
- ✅ Type de paiement limité aux valeurs acceptées
- ✅ URLs validées si fournies

### 2. Validation au niveau du Service (Couche 2)

**Fichier**: `app/Services/Payments/MaxiCashService.php`

#### 2.1 Validation des paramètres du ticket

```php
if (empty($ticket->reference)) {
    return ['success' => false, 'message' => 'Référence du ticket manquante'];
}

if (empty($ticket->amount) || $ticket->amount <= 0) {
    return ['success' => false, 'message' => 'Montant du ticket invalide'];
}

if (empty($ticket->currency)) {
    return ['success' => false, 'message' => 'Devise du ticket manquante'];
}
```

#### 2.2 Validation des identifiants MaxiCash

```php
if (empty($this->merchantId) || empty($this->merchantPassword)) {
    if (!$this->sandbox) {
        return ['success' => false, 'message' => 'Identifiants MaxiCash manquants'];
    }
}
```

#### 2.3 Validation des URLs de callback

```php
if (empty($successUrl) || !filter_var($successUrl, FILTER_VALIDATE_URL)) {
    return ['success' => false, 'message' => 'SuccessURL invalide ou manquante'];
}

if (empty($failureUrl) || !filter_var($failureUrl, FILTER_VALIDATE_URL)) {
    return ['success' => false, 'message' => 'FailureURL invalide ou manquante'];
}
```

#### 2.4 Conversion et validation des valeurs

```php
$amountCents = (int) round((float) $ticket->amount * 100);
if ($amountCents <= 0) {
    return ['success' => false, 'message' => 'Montant invalide après conversion'];
}

$currency = $this->normalizeCurrency($ticket->currency);
if (empty($currency)) {
    return ['success' => false, 'message' => 'Devise invalide'];
}
```

#### 2.5 Construction du payload avec cast explicite

```php
$payload = [
    'PayType' => 'MaxiCash',
    'MerchantID' => (string) $this->merchantId,        // Cast explicite
    'MerchantPassword' => (string) $this->merchantPassword,
    'Amount' => (string) $amountCents,
    'Currency' => (string) $currency,
    'Language' => (string) $language,
    'Reference' => (string) $ticket->reference,
    'SuccessURL' => (string) $successUrl,
    'FailureURL' => (string) $failureUrl,
    'CancelURL' => (string) $cancelUrl,
];
```

#### 2.6 Ajout conditionnel des champs optionnels

```php
// NotifyURL: ajouté UNIQUEMENT si valide
if (!empty($notifyUrl) && filter_var($notifyUrl, FILTER_VALIDATE_URL)) {
    $payload['NotifyURL'] = (string) $notifyUrl;
}

// Email: ajouté UNIQUEMENT si valide
if (!empty($ticket->email) && filter_var($ticket->email, FILTER_VALIDATE_EMAIL)) {
    $payload['Email'] = (string) $ticket->email;
}

// Telephone: ajouté UNIQUEMENT pour Mobile Money ET si valide
if ($this->isMobileMoneyPayType($ticket->pay_type, $ticket->pay_sub_type)) {
    $phone = $this->normalizePhone($ticket->phone ?? '');
    if (!empty($phone) && strlen($phone) >= 9) {
        $payload['Telephone'] = (string) $phone;
    }
}
```

### 3. Validation finale avant envoi (Couche 3)

```php
// Vérifier qu'AUCUNE valeur null ou vide n'existe dans le payload
foreach ($payload as $key => $value) {
    if ($value === null || $value === '') {
        Log::error('MaxiCash payload contains null/empty value', [
            'field' => $key,
            'ticket_reference' => $ticket->reference,
        ]);
        return [
            'success' => false,
            'message' => "Paramètre invalide: $key ne peut pas être vide",
        ];
    }
}
```

### 4. Normalisation sécurisée du téléphone

```php
private function normalizePhone(string $phone): string
{
    // Supprimer tous les espaces, tirets, parenthèses
    $cleaned = preg_replace('/[\s\-\(\)]+/', '', $phone);
    
    // Retourner une chaîne vide si invalide (jamais null)
    return $cleaned ?: '';
}
```

## 🛡️ Garanties

Avec ces protections, **AUCUNE valeur null ne peut atteindre MaxiCash**:

1. ✅ **Validation Request**: Bloque les requêtes invalides dès l'entrée
2. ✅ **Validation Service**: Double vérification de tous les paramètres
3. ✅ **Cast explicite**: Toutes les valeurs sont converties en string
4. ✅ **Validation conditionnelle**: Les champs optionnels ne sont ajoutés que s'ils sont valides
5. ✅ **Validation finale**: Boucle de vérification avant envoi
6. ✅ **Logs détaillés**: Traçabilité complète en cas de problème

## 📊 Flux de validation

```
Requête HTTP
    ↓
[1] StoreTicketRequest::rules()
    ├─ Validation format
    ├─ Validation longueur
    └─ Validation existence
    ↓
[2] TicketController::store()
    ├─ Création du ticket
    └─ Construction des URLs
    ↓
[3] MaxiCashService::initiatePaymentForTicket()
    ├─ Validation ticket
    ├─ Validation identifiants
    ├─ Validation URLs
    ├─ Conversion valeurs
    ├─ Construction payload
    ├─ Ajout conditionnel optionnels
    └─ Validation finale (foreach)
    ↓
[4] Envoi HTTP vers MaxiCash
    └─ Payload 100% valide, aucune valeur null
```

## 🧪 Tests

### Test 1: Requête valide
```bash
php test-ticket-payment.php
```
✅ Devrait fonctionner sans erreur

### Test 2: Requête avec champ manquant
```bash
curl -X POST http://192.168.241.9:8000/api/events/1/register \
  -H "Content-Type: application/json" \
  -d '{"event_price_id": 1, "email": "test@example.com"}'
```
❌ Devrait retourner: "Le nom complet est obligatoire"

### Test 3: Email invalide
```bash
curl -X POST http://192.168.241.9:8000/api/events/1/register \
  -H "Content-Type: application/json" \
  -d '{"event_price_id": 1, "full_name": "Test", "email": "invalid", "phone": "+243999999999", "pay_type": "credit_card"}'
```
❌ Devrait retourner: "L'email doit être valide"

### Test 4: Type de paiement invalide
```bash
curl -X POST http://192.168.241.9:8000/api/events/1/register \
  -H "Content-Type: application/json" \
  -d '{"event_price_id": 1, "full_name": "Test User", "email": "test@example.com", "phone": "+243999999999", "pay_type": "bitcoin"}'
```
❌ Devrait retourner: "Le mode de paiement sélectionné n'est pas valide"

## 📝 Messages d'erreur personnalisés

Tous les messages d'erreur sont en français et explicites:

- "Le tarif est obligatoire"
- "Le nom doit contenir au moins 3 caractères"
- "L'email doit être valide"
- "Le numéro de téléphone doit contenir au moins 9 chiffres"
- "Le mode de paiement sélectionné n'est pas valide"
- "SuccessURL invalide ou manquante"
- "Montant invalide après conversion"
- "Paramètre invalide: X ne peut pas être vide"

## 🎉 Résultat

**Aucune valeur null ne peut plus atteindre MaxiCash**, éliminant complètement l'erreur "Object reference not set to an instance of an object" causée par des paramètres manquants ou invalides.

## 🔍 Debugging

Si une erreur persiste, vérifiez les logs:

```bash
tail -f storage/logs/laravel.log
```

Cherchez:
- `MaxiCash payload contains null/empty value` → Un champ est vide
- `MaxiCash PayEntryWeb request` → Payload envoyé (avec clés)
- `MaxiCash PayEntryWeb failed` → Erreur de l'API MaxiCash

## ⚠️ Note importante

Cette protection élimine les erreurs causées par **des valeurs null côté client**. Si MaxiCash retourne toujours une erreur, cela peut être dû à:

1. **Identifiants invalides**: MerchantID ou MerchantPassword incorrects
2. **URLs inaccessibles**: MaxiCash ne peut pas accéder à vos URLs de callback
3. **Problème côté MaxiCash**: Leur serveur a un problème interne

Dans ce cas, utilisez ngrok ou déployez sur un serveur accessible publiquement.
