# Configuration Backend Laravel avec Cloudflare Tunnel

## 🎯 Pourquoi Cloudflare Tunnel?

✅ **PAS de mot de passe** - MaxiCash peut accéder directement
✅ **Plus rapide** que LocalTunnel
✅ **Plus stable** et fiable
✅ **Gratuit** et illimité

## 📋 Configuration actuelle

### Frontend (déjà configuré)
```bash
# Terminal 1: Frontend
npm run dev

# Terminal 2: Cloudflare Tunnel Frontend
cloudflared tunnel --url http://localhost:8080
# URL: https://prot-momentum-numerous-sms.trycloudflare.com
```

### Backend (configuration Laravel)
Votre `.env` est maintenant configuré avec:

```env
# Frontend Cloudflare Tunnel
FRONTEND_WEBSITE_URL=https://prot-momentum-numerous-sms.trycloudflare.com
FRONTEND_NLC=https://prot-momentum-numerous-sms.trycloudflare.com

# MaxiCash URLs (pointent vers le frontend Cloudflare)
MAXICASH_SUCCESS_URL=https://prot-momentum-numerous-sms.trycloudflare.com/paiement/success
MAXICASH_FAILURE_URL=https://prot-momentum-numerous-sms.trycloudflare.com/paiement/failure
MAXICASH_CANCEL_URL=https://prot-momentum-numerous-sms.trycloudflare.com/paiement/cancel

# Webhook (local pour l'instant)
MAXICASH_NOTIFY_URL=http://192.168.241.9:8000/api/webhooks/maxicash
```

## 🚀 Démarrage

### Option 1: Sans webhook (recommandé pour tester)

**Terminal 1 - Backend Laravel:**
```bash
php artisan serve --host=192.168.241.9 --port=8000
```

**Terminal 2 - Frontend (dans le dossier frontend):**
```bash
npm run dev
```

**Terminal 3 - Cloudflare Tunnel Frontend:**
```bash
cloudflared tunnel --url http://localhost:8080
```

➡️ Copier l'URL affichée (ex: `https://xyz.trycloudflare.com`)

**Terminal 4 - Mettre à jour .env si l'URL a changé:**
Si l'URL Cloudflare est différente de `https://prot-momentum-numerous-sms.trycloudflare.com`, mettez à jour `.env` et redémarrez Laravel.

### Option 2: Avec webhook (pour production)

Si vous voulez que MaxiCash puisse envoyer des webhooks:

**Terminal 4 - Cloudflare Tunnel Backend:**
```bash
cloudflared tunnel --url http://192.168.241.9:8000
```

➡️ Copier l'URL affichée (ex: `https://abc-def.trycloudflare.com`)

**Mettre à jour .env:**
```env
MAXICASH_NOTIFY_URL=https://abc-def.trycloudflare.com/api/webhooks/maxicash
```

Puis redémarrer Laravel (Ctrl+C puis relancer).

## ✅ Vérification

### 1. Vérifier le frontend
Ouvrir dans le navigateur:
```
https://prot-momentum-numerous-sms.trycloudflare.com
```

Vous devriez voir votre application **directement, sans mot de passe**! ✅

### 2. Vérifier le backend local
```bash
curl http://192.168.241.9:8000/api/test
# Devrait retourner: {"message":"API fonctionne!","timestamp":"..."}
```

### 3. Tester un paiement complet
```bash
php test-ticket-payment.php
```

Cliquer sur l'URL de redirection MaxiCash. L'erreur "Object reference not set" devrait avoir **disparu**! 🎉

## 📊 Architecture

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
│   Gateway    │    │  (Navigateur)│    │  (optionnel) │
└──────────────┘    └──────────────┘    └──────────────┘
        │                     │                     │
        │                     │                     │
        ▼                     ▼                     ▼
┌─────────────────────────────────────────────────────────────┐
│           Cloudflare Tunnel (Tunnel public)                  │
│  https://prot-momentum-numerous-sms.trycloudflare.com       │
│  (PAS de mot de passe requis!)                              │
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

1. **Utilisateur** → Remplit le formulaire sur `https://prot-momentum-numerous-sms.trycloudflare.com`
2. **Frontend** → Envoie la requête au backend Laravel local
3. **Backend** → Crée le ticket et appelle MaxiCash avec les URLs Cloudflare
4. **MaxiCash** → Retourne un LogID
5. **Backend** → Redirige vers MaxiCash avec le LogID
6. **Utilisateur** → Remplit les infos de paiement sur MaxiCash
7. **MaxiCash** → Redirige vers `https://prot-momentum-numerous-sms.trycloudflare.com/paiement/success?reference=XXX`
8. **Frontend** → Affiche le ticket avec QR code

## 🔍 Debugging

### Cloudflare Tunnel ne démarre pas
```bash
# Vérifier l'installation
cloudflared --version

# Réinstaller
npm install -g cloudflared
```

### "Connection refused"
```bash
# Vérifier que le serveur local tourne
curl http://localhost:8080  # Frontend
curl http://192.168.241.9:8000/api/test  # Backend
```

### L'URL Cloudflare change à chaque redémarrage
C'est normal avec les tunnels temporaires. Pour une URL fixe, utilisez un tunnel nommé:
```bash
cloudflared tunnel create mon-tunnel
cloudflared tunnel route dns mon-tunnel mon-app.example.com
```

### Vérifier les logs Laravel
```bash
tail -f storage/logs/laravel.log
```

## ⚠️ Important

- Cloudflare Tunnel est **gratuit** et **sans mot de passe**
- Les URLs changent à chaque redémarrage (sauf avec tunnel nommé)
- **Pas besoin d'autorisation** - MaxiCash peut accéder directement
- Pour la production, créez un tunnel nommé avec un domaine fixe

## 🎉 Avantages vs LocalTunnel

| Fonctionnalité | Cloudflare Tunnel | LocalTunnel |
|----------------|-------------------|-------------|
| Mot de passe | ❌ Non | ✅ Oui (problème!) |
| Vitesse | ⚡ Rapide | 🐌 Lent |
| Stabilité | ✅ Stable | ⚠️  Variable |
| Gratuit | ✅ Oui | ✅ Oui |
| MaxiCash compatible | ✅ Oui | ❌ Non (mot de passe) |

## 🚀 Prêt à tester!

Une fois tout démarré:
```bash
php test-ticket-payment.php
```

Puis cliquez sur l'URL de redirection MaxiCash. L'erreur devrait avoir disparu! 🎉

## 📚 Documentation

- Cloudflare Tunnel: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps
- MaxiCash API: https://developer.maxicashme.com
