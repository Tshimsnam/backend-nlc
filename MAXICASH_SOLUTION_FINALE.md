# Solution MaxiCash - "Object reference not set"

## 🎯 Problème identifié

L'erreur "Object reference not set to an instance of an object" apparaît **après** avoir rempli les informations de carte sur MaxiCash, lors de la redirection vers vos URLs de callback.

### Cause principale

MaxiCash essaie de rediriger vers vos URLs de callback (`SuccessURL`, `FailureURL`, `CancelURL`) mais:

1. **Vos URLs pointent vers un réseau local** (`http://192.168.241.9:8080`)
2. MaxiCash ne peut pas accéder à ces URLs depuis Internet
3. MaxiCash essaie d'envoyer des paramètres à ces URLs mais échoue
4. Résultat: erreur "Object reference not set"

## ✅ Solutions appliquées

### 1. Ajout de la référence du ticket dans les URLs de callback

**Fichier modifié**: `app/Http/Controllers/API/TicketController.php`

Maintenant, les URLs de callback incluent automatiquement la référence du ticket:
```
http://192.168.241.9:8080/paiement/success?reference=FLWTUBLS5L
http://192.168.241.9:8080/paiement/failure?reference=FLWTUBLS5L
http://192.168.241.9:8080/paiement/cancel?reference=FLWTUBLS5L
```

### 2. Amélioration de la page de succès frontend

**Fichier modifié**: `PaymentSuccessPage.tsx`

La page gère maintenant plusieurs formats de paramètres:
- `?reference=XXX` (ajouté par notre backend)
- `?Reference=XXX` (envoyé par MaxiCash)
- `?logid=XXX` (LogID MaxiCash)

## 🚀 Solution pour la production

### Option 1: Utiliser ngrok (développement local)

```bash
# Installer ngrok: https://ngrok.com/download
ngrok http 8080
```

Puis mettez à jour `.env`:
```env
MAXICASH_SUCCESS_URL=https://your-ngrok-url.ngrok.io/paiement/success
MAXICASH_FAILURE_URL=https://your-ngrok-url.ngrok.io/paiement/failure
MAXICASH_CANCEL_URL=https://your-ngrok-url.ngrok.io/paiement/cancel
MAXICASH_NOTIFY_URL=https://your-ngrok-url.ngrok.io/api/webhooks/maxicash
```

### Option 2: Déployer sur un serveur accessible publiquement

Déployez votre application sur:
- Heroku
- DigitalOcean
- AWS
- Vercel (frontend) + Railway/Render (backend)

Puis configurez les URLs dans `.env`:
```env
MAXICASH_SUCCESS_URL=https://votre-domaine.com/paiement/success
MAXICASH_FAILURE_URL=https://votre-domaine.com/paiement/failure
MAXICASH_CANCEL_URL=https://votre-domaine.com/paiement/cancel
MAXICASH_NOTIFY_URL=https://votre-api.com/api/webhooks/maxicash
```

## 🧪 Test en local (workaround)

Pour tester en local **sans ngrok**, vous pouvez:

1. **Simuler le succès manuellement**:
   - Créez un ticket via l'API
   - Notez la référence (ex: `FLWTUBLS5L`)
   - Allez directement sur: `http://192.168.241.9:8080/paiement/success?reference=FLWTUBLS5L`

2. **Marquer le paiement comme complété manuellement**:
   ```sql
   UPDATE tickets SET payment_status = 'completed' WHERE reference = 'FLWTUBLS5L';
   ```

## 📋 Checklist de vérification

- [x] Backend Laravel fonctionne (port 8000)
- [x] Frontend React fonctionne (port 8080)
- [x] API MaxiCash répond correctement (test-maxicash.php ✅)
- [x] Création de ticket fonctionne (test-ticket-payment.php ✅)
- [x] Référence ajoutée aux URLs de callback
- [ ] URLs accessibles publiquement (ngrok ou déploiement)
- [ ] Webhook MaxiCash configuré et accessible

## 🔍 Debugging

### Vérifier les URLs générées

Regardez les logs Laravel après création d'un ticket:
```bash
tail -f storage/logs/laravel.log
```

Cherchez: `MaxiCash PayEntryWeb request`

### Tester la redirection MaxiCash

```bash
php test-maxicash-redirect.php 97138
```

### Vérifier qu'un ticket existe

```bash
curl http://192.168.241.9:8000/api/tickets/FLWTUBLS5L
```

## 📝 Notes importantes

1. **En sandbox MaxiCash**: Les paiements ne sont pas réellement traités
2. **Les webhooks**: Nécessitent une URL publique accessible depuis Internet
3. **Les URLs de callback**: Doivent être accessibles depuis le navigateur de l'utilisateur (peuvent être locales)
4. **La référence du ticket**: Est maintenant automatiquement ajoutée aux URLs de callback

## 🎉 Résultat

Votre intégration MaxiCash est **fonctionnelle** pour:
- ✅ Création de tickets
- ✅ Initiation de paiement
- ✅ Redirection vers MaxiCash
- ✅ Affichage de la page de paiement MaxiCash

Pour finaliser complètement:
- 🔄 Configurez des URLs publiques (ngrok ou déploiement)
- 🔄 Testez un paiement complet avec redirection
- 🔄 Vérifiez que le webhook fonctionne

## 🆘 Support

Si l'erreur persiste:
1. Vérifiez que les URLs dans `.env` sont correctes
2. Testez avec ngrok pour avoir des URLs publiques
3. Vérifiez les logs Laravel: `storage/logs/laravel.log`
4. Contactez le support MaxiCash avec votre MerchantID
