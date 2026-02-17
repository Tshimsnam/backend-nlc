# Fix - Méthode GET pour /login ✅

## 🐛 Problème

Erreur rencontrée lors de l'accès à `http://192.168.171.9:8000/login` :
```
Method Not Allowed
The GET method is not supported for route login. Supported methods: POST.
```

## 🔍 Cause

La route `/login` n'acceptait que la méthode POST. Quand on accède à l'URL dans le navigateur (requête GET), Laravel retournait une erreur.

## ✅ Solution Appliquée

Ajout d'une route GET pour `/login` qui redirige vers la page de login du frontend.

### Code Ajouté

**Fichier:** `routes/web.php`

```php
// Routes d'authentification
Route::get('/login', function () {
    // Rediriger vers la page de login du frontend
    $frontendUrl = env('FRONTEND_WEBSITE_URL', 'http://localhost:8080');
    return redirect($frontendUrl . '/login');
})->name('login.form');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
```

## 📍 Routes Login Disponibles

### GET /login
- **Nom:** `login.form`
- **Méthode:** GET
- **Action:** Redirige vers la page de login du frontend
- **URL de redirection:** `{FRONTEND_WEBSITE_URL}/login`

**Exemple:**
```
Accès: http://192.168.171.9:8000/login
Redirige vers: http://localhost:8080/login
```

### POST /login
- **Nom:** `login`
- **Méthode:** POST
- **Action:** Traite la connexion et retourne un token
- **Contrôleur:** `AuthController@login`

**Exemple:**
```bash
curl -X POST http://192.168.171.9:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nlc.com",
    "password": "Admin@123"
  }'
```

## 🎯 Comportement

### Navigateur (GET)
1. Utilisateur accède à `http://192.168.171.9:8000/login`
2. Laravel redirige vers `http://localhost:8080/login`
3. L'utilisateur voit la page de login du frontend

### API (POST)
1. Frontend envoie POST à `http://192.168.171.9:8000/login`
2. Laravel traite la connexion
3. Retourne un token JWT

## 🔧 Configuration

### Variable d'Environnement

Dans `.env` :
```env
FRONTEND_WEBSITE_URL=http://localhost:8080
```

Pour l'accès depuis le réseau local :
```env
FRONTEND_WEBSITE_URL=http://192.168.171.9:3000
```

## 🧪 Tests

### Test GET (Navigateur)
```bash
# Ouvrir dans le navigateur
http://192.168.171.9:8000/login

# Ou avec curl
curl -X GET http://192.168.171.9:8000/login
# Devrait retourner une redirection 302
```

### Test POST (API)
```bash
curl -X POST http://192.168.171.9:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nlc.com",
    "password": "Admin@123"
  }'
```

### Vérifier les Routes
```bash
php artisan route:list --path=login
```

**Résultat attendu:**
```
GET|HEAD   login ............... login.form
POST       login ............... login › AuthController@login
```

## 📱 Utilisation

### Depuis le Frontend

Le frontend peut maintenant :

1. **Rediriger vers la page de login**
```typescript
// Si l'utilisateur n'est pas authentifié
window.location.href = `${API_URL}/login`;
// Sera redirigé vers la page de login du frontend
```

2. **Envoyer une requête de connexion**
```typescript
const response = await axios.post(`${API_URL}/login`, {
  email: 'admin@nlc.com',
  password: 'Admin@123'
});
```

### Depuis le Backend

Laravel peut maintenant rediriger vers la page de login :
```php
// Dans un middleware ou contrôleur
return redirect()->route('login.form');
```

## 🔒 Sécurité

### Routes Publiques
- `GET /login` : Public (redirection)
- `POST /login` : Public (authentification)

### Routes Protégées
- `POST /logout` : Protégée par `auth:sanctum`
- `GET /admin/*` : Protégée par `auth:sanctum` + `admin.only`

## 📋 Checklist

- [x] Route GET `/login` ajoutée
- [x] Redirection vers frontend configurée
- [x] Route POST `/login` maintenue
- [x] Tests effectués
- [x] Documentation mise à jour

## 🎉 Résultat

✅ L'erreur "Method Not Allowed" est corrigée
✅ Accès GET à `/login` fonctionne (redirection)
✅ Accès POST à `/login` fonctionne (authentification)
✅ Expérience utilisateur améliorée

## 📚 Documentation Liée

- `ROUTES_AUTH_WEB.md` - Routes d'authentification complètes
- `FIX_LOGIN_ROUTE.md` - Correction route login manquante
- `ADMIN_COMPLET_FINAL.md` - Vue d'ensemble complète

---

**Date:** Février 2026

**Statut:** ✅ CORRIGÉ
