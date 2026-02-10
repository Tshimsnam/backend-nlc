# Configuration Backend Laravel avec LocalTunnel

## 🎯 Objectif

Exposer votre backend Laravel sur Internet pour que MaxiCash puisse:
1. Accéder aux URLs de callback (frontend via LocalTunnel)
2. Envoyer des webhooks au backend (backend via LocalTunnel)

## 📋 Configuration actuelle

### Frontend (déjà configuré)
```bash
# Terminal 1: Frontend
npm run dev

# Terminal 2: LocalTunnel Frontend
lt --port 8080 --subdomain nlc-maxicash-rdc
# URL: https://nlc-maxicash-rdc.loca.lt
```

### Backend (à configurer)
```bash
# Terminal 3: Backend Laravel
php artisan serve --host=192.168.241.9 --port=8000

# Terminal 4: LocalTunnel Backend (pour webhooks)
lt --port 8000 --subdomain nlc-maxicash-api-rdc
# URL: https://nlc-maxicash-api-rdc.loca.lt
```

## ⚙️ Configuration .env

Votre `.env` est déjà configuré avec:

```env
# Frontend LocalTunnel
FRONTEND_WEBSITE_URL=https://nlc-maxicash-rdc.loca.lt
FRONTEND_NLC=https://nlc-maxicash-rdc.loca.lt

# MaxiCash URLs (pointent vers le frontend LocalTunnel)
MAXICASH_SUCCESS_URL=https://nlc-maxicash-rdc.loca.lt/paiement/success
MAXICASH_FAILURE_URL=https://nlc-maxicash-rdc.loca.lt/paiement/failure
MAXICASH_CANCEL_URL=https://nlc-maxicash-rdc.loca.lt/paiement/cancel

# Webhook (à mettre à jour après avoir démarré LocalTunnel backend)
MAXICASH_NOTIFY_URL=http://192.168.241.9:8000/api/webhooks/maxicash
```

## 🚀 Démarrage complet

### Option 1: Démarrage manuel (4 terminaux)

**Terminal 1 - Frontend Dev Server**
```bash
cd frontend
npm run dev
```

**Terminal 2 - Frontend LocalTunnel**
```bash
lt --port 8080 --subdomain nlc-maxicash-rdc
```
➡️ Ouvrir https://nlc-maxicash-rdc.loca.lt et cliquer "Continue"

**Terminal 3 - Backend Laravel**
```bash
cd backend-nlc
php artisan serve --host=192.168.241.9 --port=8000
```

**Terminal 4 - Backend LocalTunnel (pour webhooks)**
```bash
lt --port 8000 --subdomain nlc-maxicash-api-rdc
```
➡️ Ouvrir https://nlc-maxicash-api-rdc.loca.lt et cliquer "Continue"

**Terminal 5 - Mettre à jour le webhook**
```bash
# Mettre à jour .env avec l'URL LocalTunnel du backend
# MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
```

### Option 2: Script automatique (recommandé)

Utilisez les scripts créés:

**Windows:**
```bash
# Double-cliquer sur:
start-backend-localtunnel.bat
```

**Ou manuellement:**
```bash
start cmd /k "cd /d D:\choupole\Projects\Website\backend-nlc && php artisan serve --host=192.168.241.9 --port=8000"
start cmd /k "lt --port 8000 --subdomain nlc-maxicash-api-rdc"
```

## 📝 Mise à jour du webhook

Une fois le backend LocalTunnel démarré, mettez à jour `.env`:

```env
MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
```

Puis redémarrez Laravel:
```bash
# Ctrl+C dans le terminal Laravel
php artisan serve --host=192.168.241.9 --port=8000
```

## ✅ Vérification

### 1. Vérifier le frontend
```bash
curl https://nlc-maxicash-rdc.loca.lt
# Devrait retourner votre page HTML
```

### 2. Vérifier le backend
```bash
curl https://nlc-maxicash-api-rdc.loca.lt/api/test
# Devrait retourner: {"message":"API fonctionne!","timestamp":"..."}
```

### 3. Vérifier le webhook
```bash
curl -X POST https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash \
  -H "Content-Type: application/json" \
  -d '{"Reference":"TEST","Status":"completed"}'
# Devrait retourner: {"message":"Webhook reçu"}
```

### 4. Tester un paiement complet
```bash
php test-ticket-payment.php
```

## 🔍 Debugging

### LocalTunnel ne démarre pas
```bash
# Vérifier que LocalTunnel est installé
lt --version

# Réinstaller si nécessaire
npm install -g localtunnel
```

### "Connection refused" sur LocalTunnel
```bash
# Vérifier que le serveur local tourne
curl http://localhost:8080  # Frontend
curl http://192.168.241.9:8000/api/test  # Backend
```

### Webhook ne fonctionne pas
```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log

# Vérifier que l'URL est correcte dans .env
grep MAXICASH_NOTIFY_URL .env
```

### "Click to Continue" à chaque fois
C'est normal avec LocalTunnel. Vous devez cliquer "Continue" la première fois que vous accédez à l'URL.

## 📊 Architecture finale

```
┌─────────────────────────────────────────────────────────────┐
│                         Internet                             │
└─────────────────────────────────────────────────────────────┘
                              │
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   MaxiCash   │    │  Utilisateur │    │   Webhook    │
│   Gateway    │    │  (Navigateur)│    │  MaxiCash    │
└──────────────┘    └──────────────┘    └──────────────┘
        │                     │                     │
        │                     │                     │
        ▼                     ▼                     ▼
┌─────────────────────────────────────────────────────────────┐
│              LocalTunnel (Tunnel public)                     │
│  https://nlc-maxicash-rdc.loca.lt (Frontend)               │
│  https://nlc-maxicash-api-rdc.loca.lt (Backend)            │
└─────────────────────────────────────────────────────────────┘
                              │
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   Frontend   │    │   Backend    │    │   Webhook    │
│  localhost   │    │   Laravel    │    │   Handler    │
│   :8080      │    │   :8000      │    │              │
└──────────────┘    └──────────────┘    └──────────────┘
```

## 🎯 Flux de paiement complet

1. **Utilisateur** → Remplit le formulaire sur `https://nlc-maxicash-rdc.loca.lt`
2. **Frontend** → Envoie la requête au backend Laravel local
3. **Backend** → Crée le ticket et appelle MaxiCash avec les URLs LocalTunnel
4. **MaxiCash** → Retourne un LogID
5. **Backend** → Redirige vers MaxiCash avec le LogID
6. **Utilisateur** → Remplit les infos de paiement sur MaxiCash
7. **MaxiCash** → Redirige vers `https://nlc-maxicash-rdc.loca.lt/paiement/success?reference=XXX`
8. **MaxiCash** → Envoie un webhook à `https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash`
9. **Frontend** → Affiche le ticket avec QR code

## ⚠️ Important

- LocalTunnel est **gratuit** mais les URLs peuvent changer si vous redémarrez
- Utilisez `--subdomain` pour avoir une URL fixe (ex: `nlc-maxicash-rdc`)
- La première visite nécessite de cliquer "Continue"
- Pour la production, utilisez un vrai domaine ou Cloudflare Tunnel

## 🚀 Prêt à tester!

Une fois tout démarré:
```bash
php test-ticket-payment.php
```

Puis cliquez sur l'URL de redirection MaxiCash. L'erreur "Object reference not set" devrait avoir disparu! 🎉
