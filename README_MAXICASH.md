# 🎯 Configuration MaxiCash - Backend Laravel

## 📋 Résumé du problème résolu

**Erreur**: "Object reference not set to an instance of an object" sur la page de paiement MaxiCash

**Cause**: MaxiCash ne peut pas accéder aux URLs de callback locales (`http://192.168.241.9:8080`)

**Solution**: Utiliser LocalTunnel pour exposer votre application sur Internet

## ✅ Ce qui a été fait

### 1. Protection contre les valeurs NULL (3 couches)
- ✅ Validation Request (`StoreTicketRequest.php`)
- ✅ Validation Service (`MaxiCashService.php`)
- ✅ Validation finale avant envoi (boucle foreach)

### 2. Configuration LocalTunnel
- ✅ `.env` configuré avec URLs LocalTunnel
- ✅ Scripts de démarrage automatique créés
- ✅ Tests de vérification créés

### 3. Documentation complète
- ✅ Guides détaillés
- ✅ Scripts batch pour Windows
- ✅ Commandes de test

## 🚀 Démarrage rapide

### Étape 1: Installer LocalTunnel
```bash
npm install -g localtunnel
```

### Étape 2: Démarrer tout
```bash
# Option A: Script automatique
start-all-localtunnel.bat

# Option B: Manuellement
# Terminal 1: php artisan serve --host=192.168.241.9 --port=8000
# Terminal 2: lt --port 8000 --subdomain nlc-maxicash-api-rdc
# Terminal 3: npm run dev (dans le dossier frontend)
# Terminal 4: lt --port 8080 --subdomain nlc-maxicash-rdc
```

### Étape 3: Autoriser LocalTunnel
Ouvrir dans le navigateur et cliquer "Continue":
- https://nlc-maxicash-rdc.loca.lt
- https://nlc-maxicash-api-rdc.loca.lt

### Étape 4: Mettre à jour le webhook
Dans `.env`:
```env
MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
```

Redémarrer Laravel (Ctrl+C puis relancer)

### Étape 5: Tester
```bash
php test-localtunnel-setup.php  # Vérifier la config
php test-ticket-payment.php      # Tester un paiement
```

## 📁 Fichiers créés

### Documentation
- `DEMARRAGE_RAPIDE.md` - Guide de démarrage
- `BACKEND_LOCALTUNNEL_SETUP.md` - Configuration détaillée
- `SOLUTION_FINALE_MAXICASH.md` - Explication du problème
- `MAXICASH_PROTECTION_NULL.md` - Protections contre NULL
- `COMMANDES_BACKEND_LOCALTUNNEL.txt` - Toutes les commandes

### Scripts
- `start-backend-localtunnel.bat` - Démarrer backend + tunnel
- `start-all-localtunnel.bat` - Démarrer tout (frontend + backend)

### Tests
- `test-localtunnel-setup.php` - Vérifier la configuration
- `test-ticket-payment.php` - Tester un paiement complet
- `test-null-protection.php` - Tester les validations
- `test-maxicash-payload.php` - Tester le payload MaxiCash
- `test-maxicash-public-urls.php` - Tester avec URLs publiques

### Code
- `app/Http/Requests/StoreTicketRequest.php` - Validation Request
- `app/Services/Payments/MaxiCashService.php` - Service amélioré
- `app/Http/Controllers/API/TicketController.php` - Contrôleur mis à jour

## 🎯 URLs importantes

| Service | Local | Public (LocalTunnel) |
|---------|-------|---------------------|
| Frontend | http://localhost:8080 | https://nlc-maxicash-rdc.loca.lt |
| Backend | http://192.168.241.9:8000 | https://nlc-maxicash-api-rdc.loca.lt |
| API Test | /api/test | https://nlc-maxicash-api-rdc.loca.lt/api/test |
| Webhook | - | https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash |

## ✅ Checklist de vérification

- [ ] LocalTunnel installé (`npm install -g localtunnel`)
- [ ] Backend Laravel démarré (port 8000)
- [ ] LocalTunnel backend démarré (`lt --port 8000 --subdomain nlc-maxicash-api-rdc`)
- [ ] Frontend démarré (port 8080)
- [ ] LocalTunnel frontend démarré (`lt --port 8080 --subdomain nlc-maxicash-rdc`)
- [ ] URLs LocalTunnel autorisées (cliquer "Continue")
- [ ] `.env` mis à jour avec webhook LocalTunnel
- [ ] Laravel redémarré après modification `.env`
- [ ] Test de configuration réussi (`php test-localtunnel-setup.php`)
- [ ] Test de paiement réussi (`php test-ticket-payment.php`)

## 🎉 Résultat attendu

Après configuration:
1. ✅ Création de ticket fonctionne
2. ✅ Redirection vers MaxiCash fonctionne
3. ✅ Page de paiement MaxiCash s'affiche **sans erreur**
4. ✅ Après paiement: redirection vers page de succès
5. ✅ Webhook reçu par le backend
6. ✅ Ticket affiché avec QR code

## 🔍 Debugging

### Vérifier la configuration
```bash
php test-localtunnel-setup.php
```

### Vérifier les logs
```bash
tail -f storage/logs/laravel.log
```

### Vérifier les URLs
```bash
grep MAXICASH .env
```

### Tester l'API
```bash
curl http://192.168.241.9:8000/api/test
curl https://nlc-maxicash-api-rdc.loca.lt/api/test
```

## 📞 Support

Si problème:
1. Lire `DEMARRAGE_RAPIDE.md`
2. Lancer `php test-localtunnel-setup.php`
3. Vérifier les logs Laravel
4. Vérifier que LocalTunnel tourne

## ⚠️ Important

- LocalTunnel est **gratuit** mais peut être lent
- Les URLs restent les mêmes avec `--subdomain`
- Pour la production, utilisez un vrai domaine
- Ne commitez jamais les identifiants MaxiCash

## 🚀 Prochaines étapes

1. Tester plusieurs paiements
2. Vérifier les webhooks
3. Tester tous les modes de paiement (carte, mobile money)
4. Préparer le déploiement en production

## 📚 Documentation MaxiCash

- API: https://developer.maxicashme.com
- Sandbox: https://webapi-test.maxicashapp.com
- Production: https://webapi.maxicashapp.com
