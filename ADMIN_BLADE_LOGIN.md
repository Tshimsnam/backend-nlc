# Login Admin avec Blade - Documentation

## 🎯 Système Dual : API + Blade

Le système d'authentification supporte maintenant deux modes :
1. **API Login** (pour l'application mobile) - JSON
2. **Web Login** (pour l'admin dashboard) - Blade + Session

## 📍 Routes Disponibles

### Routes Web (Blade)
```
GET  /login        - Formulaire de connexion admin
POST /login        - Traitement connexion admin (redirige vers dashboard)
GET  /admin        - Dashboard admin (vue Blade)
POST /admin/logout - Déconnexion admin
```

### Routes API (JSON)
```
POST /api/login    - Authentification API (retourne token JSON)
POST /logout       - Déconnexion API
```

### Routes Admin API (JSON)
```
GET  /admin/dashboard                    - Stats JSON
GET  /admin/tickets/pending              - Tickets JSON
POST /admin/tickets/{reference}/validate - Valider ticket
GET  /admin/users                        - Users JSON
GET  /admin/events/stats                 - Stats événements JSON
```

## 🔐 Flux d'Authentification

### 1. Login Web (Admin Dashboard)

**Étape 1:** Accéder au formulaire
```
http://192.168.171.9:8000/login
```

**Étape 2:** Remplir le formulaire
- Email: `admin@nlc.com`
- Password: `Admin@123`

**Étape 3:** Soumission
- Vérification des identifiants
- Vérification du rôle admin
- Création d'un token
- Stockage dans la session
- Redirection vers `/admin`

**Étape 4:** Dashboard
- Affichage de la vue Blade
- Statistiques en temps réel
- Liste des tickets récents

### 2. Login API (Application Mobile)

**Requête:**
```bash
curl -X POST http://192.168.171.9:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@nlc.com",
    "password": "password"
  }'
```

**Réponse:**
```json
{
  "user": {...},
  "token": "1|xxxxxxxxxxxxxx"
}
```

## 📂 Fichiers Créés

### Contrôleurs

**AuthController.php**
- `login()` - Login API (JSON)
- `showLoginForm()` - Affiche formulaire Blade
- `webLogin()` - Traite login web + redirige

**DashboardController.php**
- `view()` - Vue Blade du dashboard
- `index()` - API JSON des stats

### Vues Blade

**resources/views/auth/login.blade.php**
- Formulaire de connexion stylisé
- Validation des erreurs
- Design responsive avec Tailwind CSS

**resources/views/admin/dashboard.blade.php**
- Dashboard complet avec stats
- Tableau des tickets récents
- Bouton de déconnexion

### Routes

**routes/web.php**
- Routes GET/POST pour login
- Route GET pour dashboard Blade
- Routes API JSON pour admin

## 🎨 Interface Blade

### Page de Login
- Design moderne avec Tailwind CSS
- Formulaire centré
- Messages d'erreur
- Lien retour au site

### Dashboard Admin
- Header avec nom utilisateur
- 4 cartes de statistiques
- Tableau des tickets récents
- Bouton de déconnexion

## 🔒 Sécurité

### Vérifications Login Web
1. Email et password requis
2. Vérification des identifiants
3. Vérification du rôle admin
4. Création de token Sanctum
5. Stockage en session

### Protection Dashboard
- Vérification de la session
- Redirection si non connecté
- Token stocké en session

### Déconnexion
- Suppression du token de session
- Suppression des données utilisateur
- Redirection vers login

## 🧪 Tests

### Test Login Web

1. **Accéder au formulaire**
```
http://192.168.171.9:8000/login
```

2. **Se connecter**
- Email: `admin@nlc.com`
- Password: `Admin@123`

3. **Vérifier la redirection**
- Doit rediriger vers `/admin`
- Doit afficher le dashboard

4. **Se déconnecter**
- Cliquer sur "Déconnexion"
- Doit rediriger vers `/login`

### Test Login API

```bash
# Login
curl -X POST http://192.168.171.9:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nlc.com",
    "password": "Admin@123"
  }'

# Utiliser le token pour accéder aux stats
curl -X GET http://192.168.171.9:8000/admin/dashboard \
  -H "Authorization: Bearer {token}"
```

## 📊 Données Affichées

### Dashboard Blade
- Total tickets
- Tickets validés
- Tickets en attente
- Revenus totaux
- 10 derniers tickets avec :
  - Référence
  - Participant (nom + email)
  - Événement
  - Montant
  - Statut (badge coloré)

## 🎯 Différences API vs Blade

| Fonctionnalité | API (JSON) | Blade (HTML) |
|----------------|------------|--------------|
| Login | POST /api/login | POST /login |
| Réponse | JSON token | Redirection |
| Stockage | Client (localStorage) | Session serveur |
| Dashboard | JSON data | Vue HTML |
| Usage | Mobile app | Admin web |
| Authentification | Bearer token | Session |

## 🔧 Configuration

### Variables d'Environnement

```env
# Frontend URL (pour redirection)
FRONTEND_WEBSITE_URL=http://localhost:8080

# Session (déjà configuré par Laravel)
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Session Laravel

Les sessions sont automatiquement gérées par Laravel. Le token et les données utilisateur sont stockés dans :
```
storage/framework/sessions/
```

## 📝 Utilisation

### Pour l'Admin Web

1. Accéder à `http://192.168.171.9:8000/login`
2. Se connecter avec email/password
3. Utiliser le dashboard Blade
4. Se déconnecter quand terminé

### Pour l'Application Mobile

1. Envoyer POST à `/api/login`
2. Récupérer le token
3. Utiliser le token pour les requêtes API
4. Appeler `/logout` pour se déconnecter

## ✅ Avantages

### Login Blade
- ✅ Interface utilisateur complète
- ✅ Pas besoin de frontend séparé
- ✅ Session gérée par Laravel
- ✅ Redirection automatique
- ✅ Messages d'erreur intégrés

### Login API
- ✅ Compatible mobile
- ✅ Token JWT
- ✅ Stateless
- ✅ Flexible

## 🚀 Déploiement

```bash
# Nettoyer le cache
php artisan route:clear
php artisan config:clear
php artisan view:clear

# Vérifier les routes
php artisan route:list --path=login
php artisan route:list --path=admin

# Créer un admin
php artisan db:seed --class=AdminSeeder

# Démarrer le serveur
php artisan serve --host=0.0.0.0 --port=8000
```

## 📚 Fichiers Modifiés

- ✅ `app/Http/Controllers/AuthController.php` - Méthodes login Blade
- ✅ `app/Http/Controllers/Admin/DashboardController.php` - Méthode view()
- ✅ `routes/web.php` - Routes login + dashboard
- ✅ `resources/views/auth/login.blade.php` - Formulaire login
- ✅ `resources/views/admin/dashboard.blade.php` - Dashboard

---

**Date:** Février 2026

**Statut:** ✅ COMPLET
