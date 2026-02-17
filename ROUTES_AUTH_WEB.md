# Routes d'Authentification - Web

## 🔐 Routes Ajoutées

Les routes d'authentification ont été ajoutées dans `routes/web.php` :

```php
// Routes d'authentification
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
```

## 📍 Endpoints Disponibles

### 1. Login
**Route:** `POST /login`  
**Nom:** `login`  
**Middleware:** Aucun (public)

**Requête:**
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nlc.com",
    "password": "Admin@123"
  }'
```

**Réponse (Succès):**
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Admin Principal",
    "email": "admin@nlc.com"
  }
}
```

**Réponse (Erreur):**
```json
{
  "message": "Identifiants incorrects"
}
```

### 2. Logout
**Route:** `POST /logout`  
**Nom:** `logout`  
**Middleware:** `auth:sanctum`

**Requête:**
```bash
curl -X POST http://localhost:8000/logout \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

**Réponse:**
```json
{
  "message": "Déconnexion réussie"
}
```

## 🎯 Utilisation dans le Frontend

### Configuration
```typescript
// Dans votre fichier .env
VITE_API_URL=http://localhost:8000
```

### Exemple de Login
```typescript
const handleLogin = async (email: string, password: string) => {
  try {
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/login`, {
      email,
      password
    });

    // Stocker le token
    localStorage.setItem('auth_token', response.data.token);
    
    // Rediriger vers le dashboard
    navigate('/admin');
  } catch (error) {
    console.error('Erreur de connexion:', error);
    alert('Identifiants incorrects');
  }
};
```

### Exemple de Logout
```typescript
const handleLogout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    
    await axios.post(`${import.meta.env.VITE_API_URL}/logout`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    // Supprimer le token
    localStorage.removeItem('auth_token');
    
    // Rediriger vers la page de login
    navigate('/login');
  } catch (error) {
    console.error('Erreur de déconnexion:', error);
  }
};
```

## 🔄 Différence avec les Routes API

### Avant (routes/api.php)
```
POST /api/login   - Nécessitait X-API-SECRET header
POST /api/logout  - Dans routes/api.php
```

### Maintenant (routes/web.php)
```
POST /login   - Pas besoin de X-API-SECRET
POST /logout  - Dans routes/web.php
```

## ⚠️ Important

### X-API-SECRET
La route `/login` dans `web.php` **ne nécessite PAS** le header `X-API-SECRET`.

Si vous voulez garder cette protection, vous devez :

1. **Option 1:** Ajouter le middleware dans web.php
```php
use App\Http\Middleware\VerifyApiSecret;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware(VerifyApiSecret::class)
    ->name('login');
```

2. **Option 2:** Garder la route dans api.php
```php
// Dans routes/api.php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(VerifyApiSecret::class);

// Dans routes/web.php
Route::post('/login', [AuthController::class, 'login'])->name('login');
```

## 🧪 Tests

### Test Login
```bash
# Test avec identifiants corrects
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@nlc.com","password":"Admin@123"}'

# Test avec identifiants incorrects
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"email":"wrong@email.com","password":"wrong"}'
```

### Test Logout
```bash
# Obtenir un token d'abord
TOKEN=$(curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@nlc.com","password":"Admin@123"}' \
  | jq -r '.token')

# Tester le logout
curl -X POST http://localhost:8000/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"
```

## 🔒 Sécurité

### CSRF Protection
Les routes web Laravel ont automatiquement la protection CSRF activée. Pour les requêtes AJAX, vous devez :

1. **Option 1:** Exclure les routes de la protection CSRF
```php
// Dans app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    '/login',
    '/logout',
    '/admin/*'
];
```

2. **Option 2:** Inclure le token CSRF dans vos requêtes
```typescript
// Récupérer le token CSRF
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// L'inclure dans la requête
axios.post('/login', data, {
  headers: {
    'X-CSRF-TOKEN': csrfToken
  }
});
```

### Recommandation
Pour une API, il est recommandé d'exclure les routes de la protection CSRF et d'utiliser Sanctum pour l'authentification.

## 📋 Checklist

- [x] Route `/login` ajoutée dans web.php
- [x] Route `/logout` ajoutée dans web.php
- [x] Import AuthController ajouté
- [x] Noms de routes définis
- [ ] CSRF désactivé pour ces routes (si nécessaire)
- [ ] Frontend mis à jour pour utiliser `/login` au lieu de `/api/login`
- [ ] Tests effectués

## 🚀 Prochaines Étapes

1. **Désactiver CSRF pour les routes API**
```php
// Dans app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    '/login',
    '/logout',
    '/admin/*'
];
```

2. **Mettre à jour le frontend**
- Changer `/api/login` en `/login`
- Changer `/api/logout` en `/logout`

3. **Tester**
```bash
php artisan route:clear
php artisan config:clear
```

## 📚 Documentation Liée

- `ADMIN_DASHBOARD_SETUP.md` - Configuration du dashboard
- `ADMIN_ROUTES_UPDATE.md` - Changement des routes admin
- `RESUME_ADMIN_FINAL.md` - Résumé complet

---

**Date:** Février 2026

**Statut:** ✅ Routes ajoutées
