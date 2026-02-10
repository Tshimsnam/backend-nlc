# 🚀 Démarrage Rapide - Backend avec LocalTunnel

## ✅ Configuration déjà faite

Votre `.env` est déjà configuré avec les bonnes URLs LocalTunnel:
- ✅ Frontend: `https://nlc-maxicash-rdc.loca.lt`
- ✅ URLs MaxiCash pointent vers le frontend LocalTunnel
- ⚠️  Webhook à mettre à jour après démarrage

## 📋 Étapes à suivre

### 1. Installer LocalTunnel (si pas déjà fait)

```bash
npm install -g localtunnel
```

### 2. Démarrer tout automatiquement

**Option A: Script automatique (recommandé)**
```bash
# Double-cliquer sur:
start-all-localtunnel.bat
```

**Option B: Manuellement (4 terminaux)**

**Terminal 1 - Backend Laravel:**
```bash
php artisan serve --host=192.168.241.9 --port=8000
```

**Terminal 2 - LocalTunnel Backend:**
```bash
lt --port 8000 --subdomain nlc-maxicash-api-rdc
```

**Terminal 3 - Frontend (dans le dossier frontend):**
```bash
npm run dev
```

**Terminal 4 - LocalTunnel Frontend:**
```bash
lt --port 8080 --subdomain nlc-maxicash-rdc
```

### 3. Autoriser LocalTunnel (IMPORTANT!)

Ouvrir dans votre navigateur:
1. https://nlc-maxicash-rdc.loca.lt → Cliquer "Click to Continue"
2. https://nlc-maxicash-api-rdc.loca.lt → Cliquer "Click to Continue"

### 4. Mettre à jour le webhook

Modifier `.env`:
```env
MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
```

Puis redémarrer Laravel (Ctrl+C dans le terminal Laravel, puis relancer):
```bash
php artisan serve --host=192.168.241.9 --port=8000
```

### 5. Vérifier la configuration

```bash
php test-localtunnel-setup.php
```

Vous devriez voir tous les ✅

### 6. Tester un paiement

```bash
php test-ticket-payment.php
```

Puis cliquer sur l'URL de redirection MaxiCash.

## 🎉 Résultat attendu

L'erreur **"Object reference not set to an instance of an object"** devrait avoir **disparu**!

MaxiCash peut maintenant:
- ✅ Accéder à vos URLs de callback (frontend)
- ✅ Envoyer des webhooks (backend)
- ✅ Afficher la page de paiement correctement

## 📊 URLs importantes

| Service | Local | Public (LocalTunnel) |
|---------|-------|---------------------|
| Frontend | http://localhost:8080 | https://nlc-maxicash-rdc.loca.lt |
| Backend | http://192.168.241.9:8000 | https://nlc-maxicash-api-rdc.loca.lt |
| API Test | http://192.168.241.9:8000/api/test | https://nlc-maxicash-api-rdc.loca.lt/api/test |
| Webhook | - | https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash |

## 🔍 Debugging

### LocalTunnel ne démarre pas
```bash
# Vérifier l'installation
lt --version

# Réinstaller
npm install -g localtunnel
```

### Backend ne répond pas
```bash
# Vérifier que Laravel tourne
curl http://192.168.241.9:8000/api/test
```

### "Click to Continue" à chaque fois
C'est normal la première fois. Après avoir cliqué, l'URL reste autorisée.

### Webhook ne fonctionne pas
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier la config
grep MAXICASH_NOTIFY_URL .env
```

## 📚 Documentation complète

- `BACKEND_LOCALTUNNEL_SETUP.md` - Guide détaillé
- `COMMANDES_BACKEND_LOCALTUNNEL.txt` - Toutes les commandes
- `SOLUTION_FINALE_MAXICASH.md` - Explication du problème

## 🆘 Besoin d'aide?

1. Lancer le test: `php test-localtunnel-setup.php`
2. Vérifier les logs: `tail -f storage/logs/laravel.log`
3. Vérifier `.env`: `grep MAXICASH .env`

## ⚠️ Important

- LocalTunnel est **gratuit** mais peut être lent
- Les URLs restent les mêmes avec `--subdomain`
- Pour la production, utilisez un vrai domaine
- Ne commitez jamais les URLs LocalTunnel dans Git

## 🎯 Prochaines étapes

Une fois que tout fonctionne:
1. Tester plusieurs paiements
2. Vérifier que les webhooks arrivent
3. Vérifier que les tickets sont créés
4. Préparer le déploiement en production
