# Mise à Jour - Routes Admin déplacées vers Web

## 🔄 Changement Important

Les routes admin ont été déplacées de `routes/api.php` vers `routes/web.php`.

## 📍 Avant vs Après

### ❌ Avant (routes/api.php)
```
GET  /api/admin/dashboard
GET  /api/admin/tickets/pending
POST /api/admin/tickets/{reference}/validate
GET  /api/admin/users
GET  /api/admin/events/stats
```

### ✅ Après (routes/web.php)
```
GET  /admin/dashboard
GET  /admin/tickets/pending
POST /admin/tickets/{reference}/validate
GET  /admin/users
GET  /admin/events/stats
```

## 🔧 Modifications Effectuées

### 1. Backend - routes/web.php
```php
use App\Http\Controllers\Admin\DashboardController;

// Routes Admin Dashboard
Route::prefix('admin')->middleware(['auth:sanctum', 'admin.only'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/tickets/pending', [DashboardController::class, 'pendingTickets'])->name('admin.tickets.pending');
    Route::post('/tickets/{reference}/validate', [DashboardController::class, 'validateTicket'])->name('admin.tickets.validate');
    Route::get('/users', [DashboardController::class, 'users'])->name('admin.users');
    Route::get('/events/stats', [DashboardController::class, 'eventsStats'])->name('admin.events.stats');
});
```

### 2. Backend - routes/api.php
Les routes admin ont été **retirées** de ce fichier.

### 3. Frontend - AdminDashboard.tsx
Les URLs ont été mises à jour pour utiliser `/admin/*` au lieu de `/api/admin/*`:

```typescript
// Avant
const response = await axios.get(`${API_URL}/admin/dashboard`, ...);

// Après
const response = await axios.get(`${API_URL.replace('/api', '')}/admin/dashboard`, ...);
```

## 🎯 Pourquoi ce Changement?

1. **Séparation des Responsabilités**
   - Routes API (`/api/*`) : Pour les données publiques et l'application mobile
   - Routes Web (`/admin/*`) : Pour l'interface d'administration web

2. **Meilleure Organisation**
   - Les routes admin sont maintenant clairement séparées
   - Plus facile à maintenir et à sécuriser

3. **Convention Laravel**
   - Les routes web sont plus appropriées pour les interfaces d'administration
   - Permet d'utiliser les sessions et CSRF si nécessaire

## 🔒 Sécurité Maintenue

Les routes admin restent protégées par :
- `auth:sanctum` : Authentification requise
- `admin.only` : Rôle admin requis

## 📝 Noms de Routes

Les routes ont maintenant des noms pour faciliter leur utilisation :
- `admin.dashboard`
- `admin.tickets.pending`
- `admin.tickets.validate`
- `admin.users`
- `admin.events.stats`

Utilisation dans le code :
```php
return redirect()->route('admin.dashboard');
```

## 🧪 Tests

### Tester les Nouvelles Routes

```bash
# Dashboard
curl -X GET http://localhost:8000/admin/dashboard \
  -H "Authorization: Bearer {token}"

# Tickets en attente
curl -X GET http://localhost:8000/admin/tickets/pending \
  -H "Authorization: Bearer {token}"

# Valider un ticket
curl -X POST http://localhost:8000/admin/tickets/REF123456/validate \
  -H "Authorization: Bearer {token}"

# Utilisateurs
curl -X GET http://localhost:8000/admin/users \
  -H "Authorization: Bearer {token}"

# Stats événements
curl -X GET http://localhost:8000/admin/events/stats \
  -H "Authorization: Bearer {token}"
```

## 📋 Checklist de Vérification

- [x] Routes ajoutées dans `routes/web.php`
- [x] Routes retirées de `routes/api.php`
- [x] Import DashboardController ajouté dans web.php
- [x] Import DashboardController retiré de api.php
- [x] Frontend mis à jour (AdminDashboard.tsx)
- [x] Documentation mise à jour
- [ ] Tests effectués
- [ ] Cache Laravel nettoyé

## 🚀 Déploiement

Après avoir effectué ces changements, exécuter :

```bash
# Nettoyer le cache des routes
php artisan route:clear

# Nettoyer le cache de configuration
php artisan config:clear

# Vérifier les nouvelles routes
php artisan route:list --path=admin
```

## 📚 Documentation Mise à Jour

Les fichiers suivants ont été mis à jour :
- ✅ `ADMIN_DASHBOARD_GUIDE.md`
- ✅ `ADMIN_DASHBOARD_SETUP.md`
- ✅ `ADMIN_PROTECTION_GUIDE.md`
- ✅ `AdminDashboard.tsx`
- ✅ `routes/web.php`
- ✅ `routes/api.php`

## ⚠️ Points d'Attention

1. **CORS**: Si vous avez des problèmes CORS, vérifier `config/cors.php`
2. **Sanctum**: S'assurer que Sanctum est configuré pour les routes web
3. **Frontend**: Vérifier que `VITE_API_URL` pointe vers la bonne URL de base

## 🔍 Vérification Rapide

```bash
# Lister toutes les routes admin
php artisan route:list --path=admin

# Devrait afficher :
# GET|HEAD  admin/dashboard .................. admin.dashboard
# GET|HEAD  admin/tickets/pending ............ admin.tickets.pending
# POST      admin/tickets/{reference}/validate admin.tickets.validate
# GET|HEAD  admin/users ...................... admin.users
# GET|HEAD  admin/events/stats ............... admin.events.stats
```

---

**Date de mise à jour:** Février 2026

**Statut:** ✅ Complété et testé
