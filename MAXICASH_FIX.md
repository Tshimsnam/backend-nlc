# MaxiCash "Object reference not set" - Solution

## Problème
L'erreur "Object reference not set to an instance of an object" se produit lorsque MaxiCash reçoit des paramètres null ou manquants dans la requête API.

## Corrections appliquées

### 1. Validation des URLs obligatoires
- Ajout de validation pour `SuccessURL` et `FailureURL` (obligatoires)
- Vérification que les URLs ne sont pas vides avant l'envoi

### 2. Validation des identifiants
- Vérification que `MerchantID` et `MerchantPassword` sont configurés
- Message d'erreur clair si les identifiants sont manquants

### 3. Gestion conditionnelle des paramètres optionnels
- `NotifyURL` : envoyé uniquement s'il est défini
- `Email` : envoyé uniquement s'il est disponible
- `Telephone` : envoyé uniquement pour Mobile Money

### 4. Amélioration des logs
- Logs détaillés avant chaque requête API
- Logs d'erreur avec les clés du payload pour debug

## Comment tester

### Test 1: Script de test direct
```bash
php test-maxicash.php
```

Ce script teste directement l'API MaxiCash avec vos identifiants.

### Test 2: Via l'API Laravel
```bash
# Démarrer le serveur
php artisan serve --host=192.168.241.9 --port=8000

# Tester avec curl
curl -X POST http://192.168.241.9:8000/api/events/{event_id}/tickets \
  -H "Content-Type: application/json" \
  -d '{
    "event_price_id": 1,
    "full_name": "Test User",
    "email": "test@example.com",
    "phone": "+243999999999",
    "pay_type": "credit_card"
  }'
```

## Checklist de vérification

- [ ] `MAXICASH_MERCHANT_ID` est défini dans `.env`
- [ ] `MAXICASH_MERCHANT_PASSWORD` est défini dans `.env`
- [ ] `MAXICASH_SUCCESS_URL` est une URL complète (http://...)
- [ ] `MAXICASH_FAILURE_URL` est une URL complète (http://...)
- [ ] `MAXICASH_NOTIFY_URL` est accessible publiquement (pour webhooks)
- [ ] Le serveur Laravel est accessible depuis l'extérieur (pour notify_url)

## Erreurs courantes

### "Object reference not set"
**Causes possibles:**
1. Un paramètre obligatoire est null ou vide
2. Les URLs ne sont pas au format complet (manque http://)
3. MerchantID ou MerchantPassword invalides

**Solution:**
- Vérifier les logs Laravel: `tail -f storage/logs/laravel.log`
- Chercher "MaxiCash PayEntryWeb request" pour voir le payload exact
- Vérifier que toutes les URLs commencent par http:// ou https://

### "Invalid credentials"
**Causes:**
- MerchantID ou MerchantPassword incorrects
- Utilisation des identifiants de production sur l'API sandbox (ou inverse)

**Solution:**
- Vérifier que `MAXICASH_SANDBOX=true` correspond à vos identifiants
- Tester avec le script `test-maxicash.php`

### Webhook non reçu
**Causes:**
- L'URL de notification n'est pas accessible publiquement
- Le serveur local (192.168.x.x) n'est pas accessible depuis MaxiCash

**Solution:**
- Utiliser ngrok ou un service similaire pour exposer votre API locale
- Ou déployer sur un serveur accessible publiquement

## URLs de test recommandées

Pour le développement local, utilisez ngrok:
```bash
ngrok http 8000
```

Puis mettez à jour `.env`:
```env
MAXICASH_NOTIFY_URL=https://your-ngrok-url.ngrok.io/api/webhooks/maxicash
```

## Documentation MaxiCash

- Sandbox API: https://webapi-test.maxicashapp.com
- Production API: https://webapi.maxicashapp.com
- Documentation: https://developer.maxicashme.com

## Support

Si l'erreur persiste:
1. Exécutez `php test-maxicash.php` et partagez la sortie
2. Vérifiez les logs: `storage/logs/laravel.log`
3. Contactez le support MaxiCash avec votre MerchantID
