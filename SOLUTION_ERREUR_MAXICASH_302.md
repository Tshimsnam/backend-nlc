# Solution - Erreur MaxiCash "Object reference not set to an instance of an object"

## 🔍 Problème

Lorsque vous accédez à l'URL MaxiCash:
```
https://api-testbed.maxicashapp.com/payentryweb?logid=97762
```

Vous obtenez:
- **Erreur**: "Object reference not set to an instance of an object"
- **Code HTTP**: 302 Found (redirection vers payfailure.aspx)

## 🎯 Cause

MaxiCash ne peut pas accéder à vos URLs de callback car elles sont **locales**:
```
http://192.168.58.9:8080/paiement/success  ❌ (non accessible depuis Internet)
http://192.168.58.9:8080/paiement/failure  ❌
http://192.168.58.9:8080/paiement/cancel   ❌
http://192.168.58.9:8000/api/webhooks/maxicash  ❌
```

MaxiCash a besoin d'URLs **publiques** (accessibles depuis Internet) pour:
1. Afficher la page de paiement correctement
2. Rediriger l'utilisateur après le paiement
3. Envoyer les notifications webhook

## ✅ Solution: Exposer votre application avec LocalTunnel

### Option 1: LocalTunnel (Gratuit, Rapide)

#### Étape 1: Installer LocalTunnel

```bash
npm install -g localtunnel
```

#### Étape 2: Démarrer le backend Laravel

```bash
php artisan serve --host=192.168.58.9 --port=8000
```

#### Étape 3: Exposer le backend avec LocalTunnel

Dans un nouveau terminal:
```bash
lt --port 8000 --subdomain nlc-maxicash-api-rdc
```

Vous obtiendrez:
```
your url is: https://nlc-maxicash-api-rdc.loca.lt
```

#### Étape 4: Démarrer le frontend

```bash
cd ../frontend-nlc
npm run dev
```

#### Étape 5: Exposer le frontend avec LocalTunnel

Dans un nouveau terminal:
```bash
lt --port 8080 --subdomain nlc-maxicash-rdc
```

Vous obtiendrez:
```
your url is: https://nlc-maxicash-rdc.loca.lt
```

#### Étape 6: Autoriser LocalTunnel

Ouvrez dans votre navigateur et cliquez sur "Continue":
- https://nlc-maxicash-rdc.loca.lt
- https://nlc-maxicash-api-rdc.loca.lt

#### Étape 7: Mettre à jour le .env

```env
# URLs publiques LocalTunnel
MAXICASH_SUCCESS_URL=https://nlc-maxicash-rdc.loca.lt/paiement/success
MAXICASH_FAILURE_URL=https://nlc-maxicash-rdc.loca.lt/paiement/failure
MAXICASH_CANCEL_URL=https://nlc-maxicash-rdc.loca.lt/paiement/cancel
MAXICASH_NOTIFY_URL=https://nlc-maxicash-api-rdc.loca.lt/api/webhooks/maxicash
```

#### Étape 8: Redémarrer Laravel

```bash
# Arrêter avec Ctrl+C, puis:
php artisan config:clear
php artisan serve --host=192.168.58.9 --port=8000
```

#### Étape 9: Tester l'inscription

Maintenant, testez depuis votre frontend:
```
http://192.168.58.9:8080/evenements/1
```

Ou via l'URL publique:
```
https://nlc-maxicash-rdc.loca.lt/evenements/1
```

### Option 2: Cloudflare Tunnel (Plus stable)

Consultez `BACKEND_CLOUDFLARE_SETUP.md` pour les instructions détaillées.

## 🧪 Vérification

### 1. Vérifier que LocalTunnel fonctionne

```bash
# Tester le backend
curl https://nlc-maxicash-api-rdc.loca.lt/api/test

# Tester le frontend
curl https://nlc-maxicash-rdc.loca.lt
```

### 2. Tester l'inscription

```bash
php test-api-inscription.php
```

Vérifiez que les URLs dans la réponse sont publiques:
```json
{
  "success": true,
  "redirect_url": "https://api-testbed.maxicashapp.com/payentryweb?logid=97763"
}
```

### 3. Accéder à la page MaxiCash

Ouvrez l'URL de redirection dans votre navigateur. Vous devriez voir:
- ✅ La page de paiement MaxiCash
- ✅ Le montant correct (15.00 USD)
- ✅ Les options de paiement (Mobile Money, Carte, PayPal)
- ❌ Plus d'erreur "Object reference not set to an instance of an object"

## 📋 Script automatique

Utilisez le script batch fourni:

```bash
start-all-localtunnel.bat
```

Ce script démarre automatiquement:
1. Backend Laravel (port 8000)
2. LocalTunnel backend
3. Frontend (port 8080)
4. LocalTunnel frontend

## ⚠️ Important

### LocalTunnel
- ✅ Gratuit
- ✅ Facile à utiliser
- ⚠️ Peut être lent
- ⚠️ Nécessite autorisation à chaque démarrage
- ⚠️ URLs peuvent changer sans `--subdomain`

### Cloudflare Tunnel
- ✅ Plus stable
- ✅ Plus rapide
- ✅ Pas besoin d'autorisation
- ⚠️ Configuration plus complexe

## 🔍 Débogage

### Erreur persiste après LocalTunnel?

1. **Vérifiez que LocalTunnel est autorisé**:
   - Ouvrez https://nlc-maxicash-rdc.loca.lt
   - Cliquez sur "Continue"

2. **Vérifiez les URLs dans .env**:
   ```bash
   grep MAXICASH .env
   ```

3. **Effacez le cache Laravel**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Redémarrez Laravel**:
   ```bash
   # Ctrl+C puis:
   php artisan serve --host=192.168.58.9 --port=8000
   ```

5. **Vérifiez les logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### LocalTunnel ne démarre pas?

```bash
# Réinstaller LocalTunnel
npm uninstall -g localtunnel
npm install -g localtunnel

# Vérifier l'installation
lt --version
```

## 📚 Documentation

- `README_MAXICASH.md` - Configuration complète LocalTunnel
- `BACKEND_LOCALTUNNEL_SETUP.md` - Guide détaillé LocalTunnel
- `BACKEND_CLOUDFLARE_SETUP.md` - Alternative avec Cloudflare
- `SOLUTION_FINALE_MAXICASH.md` - Explication du problème

## 🎯 Résultat attendu

Après configuration LocalTunnel:

1. ✅ Page MaxiCash s'affiche sans erreur
2. ✅ Options de paiement visibles
3. ✅ Montant correct affiché
4. ✅ Après paiement: redirection vers votre site
5. ✅ Webhook reçu par le backend

## 📞 Support

Si le problème persiste:
1. Vérifiez que LocalTunnel tourne: `lt --version`
2. Vérifiez les URLs: `grep MAXICASH .env`
3. Testez les URLs publiques: `curl https://nlc-maxicash-api-rdc.loca.lt/api/test`
4. Consultez les logs: `tail -f storage/logs/laravel.log`

---

**Note**: Pour la production, utilisez un vrai domaine au lieu de LocalTunnel.
