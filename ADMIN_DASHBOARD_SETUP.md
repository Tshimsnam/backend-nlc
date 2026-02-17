# Configuration du Dashboard Admin - Guide d'Installation

## 📦 Prérequis

- React Router DOM installé
- Axios configuré
- Composants UI (Button, Input) disponibles
- Lucide React pour les icônes

## 🚀 Installation Rapide

### 1. Ajouter la Route dans votre Router

Dans votre fichier de routes principal (ex: `App.tsx` ou `routes.tsx`):

```tsx
import AdminDashboard from "@/pages/AdminDashboard";

// Dans votre configuration de routes
<Route path="/admin" element={<AdminDashboard />} />
```

### 2. Configuration des Variables d'Environnement

Créer ou modifier `.env` :

```env
VITE_API_URL=http://localhost:8000/api
```

### 3. Vérifier les Dépendances

```bash
npm install lucide-react axios react-router-dom
```

## 🔐 Configuration Backend

### 1. Vérifier le Middleware Admin

Le fichier `app/Http/Middleware/AdminOnly.php` doit exister :

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$request->user()) {
            return response()->json([
                'message' => 'Non authentifié.'
            ], 401);
        }

        // Vérifier si l'utilisateur a le rôle admin
        $hasAdminRole = $request->user()->roles()
            ->where('name', 'admin')
            ->exists();

        if (!$hasAdminRole) {
            return response()->json([
                'message' => 'Accès refusé. Seuls les administrateurs peuvent accéder à cette ressource.'
            ], 403);
        }

        return $next($request);
    }
}
```

### 2. Enregistrer le Middleware

Dans `bootstrap/app.php` :

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin.only' => \App\Http\Middleware\AdminOnly::class,
    ]);
})
```

### 3. Vérifier les Routes API

Dans `routes/web.php`, les routes admin doivent être protégées :

```php
use App\Http\Controllers\Admin\DashboardController;

// Routes Admin Dashboard
Route::prefix('admin')->middleware(['auth:sanctum', 'admin.only'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/tickets/pending', [DashboardController::class, 'pendingTickets']);
    Route::post('/tickets/{reference}/validate', [DashboardController::class, 'validateTicket']);
    Route::get('/users', [DashboardController::class, 'users']);
    Route::get('/events/stats', [DashboardController::class, 'eventsStats']);
});
```

**Important:** Les routes admin sont dans `routes/web.php` et non dans `routes/api.php`.

## 👤 Créer un Utilisateur Admin

### Option 1: Via Seeder (Recommandé)

Créer `database/seeders/AdminSeeder.php` :

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Créer l'utilisateur admin
        $user = User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@nlc.com',
            'password' => Hash::make('Admin@123'),
        ]);

        // Assigner le rôle admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole);
        }
    }
}
```

Exécuter le seeder :

```bash
php artisan db:seed --class=AdminSeeder
```

### Option 2: Via Tinker

```bash
php artisan tinker
```

```php
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@nlc.com',
    'password' => Hash::make('Admin@123')
]);

$adminRole = Role::where('name', 'admin')->first();
$user->roles()->attach($adminRole);
```

## 🔑 Connexion et Authentification

### 1. Se Connecter

Endpoint: `POST /api/login`

```bash
curl -X POST http://localhost:8000/api/login \
  -H "X-API-SECRET: votre_secret" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nlc.com",
    "password": "Admin@123"
  }'
```

Réponse :

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

### 2. Stocker le Token (Frontend)

Dans votre composant de login :

```tsx
const handleLogin = async (email: string, password: string) => {
  try {
    const response = await axios.post(`${API_URL}/login`, {
      email,
      password
    }, {
      headers: {
        'X-API-SECRET': import.meta.env.VITE_API_SECRET
      }
    });

    // Stocker le token
    localStorage.setItem('auth_token', response.data.token);
    
    // Rediriger vers le dashboard
    navigate('/admin');
  } catch (error) {
    console.error('Erreur de connexion:', error);
  }
};
```

## 🎨 Personnalisation

### Modifier les Couleurs

Dans `AdminDashboard.tsx`, vous pouvez personnaliser les couleurs des cartes de statistiques :

```tsx
// Exemple: Changer la couleur de la carte "Total Tickets"
<div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
  <Ticket className="w-6 h-6 text-blue-600" />
</div>
```

Couleurs disponibles :
- `bg-blue-100` / `text-blue-600` (Bleu)
- `bg-green-100` / `text-green-600` (Vert)
- `bg-orange-100` / `text-orange-600` (Orange)
- `bg-purple-100` / `text-purple-600` (Violet)
- `bg-red-100` / `text-red-600` (Rouge)

### Ajouter des Statistiques Personnalisées

Dans `DashboardController.php` :

```php
$stats = [
    'total_tickets' => Ticket::count(),
    'ma_stat_custom' => MonModele::where('condition', true)->count(),
    // ...
];
```

Dans `AdminDashboard.tsx` :

```tsx
interface DashboardStats {
  total_tickets: number;
  ma_stat_custom: number;
  // ...
}
```

## 🧪 Tests

### Tester l'Accès Admin

```bash
# Avec token admin (doit fonctionner)
curl -X GET http://localhost:8000/admin/dashboard \
  -H "Authorization: Bearer {admin_token}"

# Sans token (doit retourner 401)
curl -X GET http://localhost:8000/admin/dashboard

# Avec token non-admin (doit retourner 403)
curl -X GET http://localhost:8000/admin/dashboard \
  -H "Authorization: Bearer {user_token}"
```

### Tester la Validation de Ticket

```bash
curl -X POST http://localhost:8000/admin/tickets/REF123456/validate \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json"
```

## 🐛 Dépannage

### Erreur: "Cannot find module '@/components/ui/button'"

Vérifier que les composants UI sont bien installés. Si vous utilisez shadcn/ui :

```bash
npx shadcn-ui@latest add button
npx shadcn-ui@latest add input
```

### Erreur: "401 Unauthorized"

1. Vérifier que le token est bien stocké dans localStorage
2. Vérifier que le token est valide
3. Vérifier que l'utilisateur existe et est actif

```tsx
// Vérifier le token
const token = localStorage.getItem('auth_token');
console.log('Token:', token);
```

### Erreur: "403 Forbidden"

1. Vérifier que l'utilisateur a bien le rôle admin
2. Vérifier que le middleware est bien appliqué

```bash
# Dans tinker
php artisan tinker
>>> $user = User::find(1);
>>> $user->roles;
```

### Les Données ne s'Affichent Pas

1. Ouvrir la console du navigateur (F12)
2. Vérifier les erreurs réseau
3. Vérifier que l'API_URL est correcte

```tsx
console.log('API URL:', import.meta.env.VITE_API_URL);
```

## 📱 Responsive Design

Le dashboard est déjà responsive :
- Sidebar rétractable sur mobile
- Grilles adaptatives (grid-cols-1 md:grid-cols-2 lg:grid-cols-4)
- Tables avec scroll horizontal sur petits écrans

## 🔒 Sécurité

### Bonnes Pratiques

1. **Ne jamais exposer le token** dans les logs ou console
2. **Utiliser HTTPS** en production
3. **Implémenter un refresh token** pour les sessions longues
4. **Ajouter un timeout** pour déconnecter automatiquement
5. **Valider les permissions** côté backend ET frontend

### Exemple de Protection de Route

```tsx
import { Navigate } from 'react-router-dom';

const ProtectedRoute = ({ children }) => {
  const token = localStorage.getItem('auth_token');
  
  if (!token) {
    return <Navigate to="/login" replace />;
  }
  
  return children;
};

// Utilisation
<Route 
  path="/admin" 
  element={
    <ProtectedRoute>
      <AdminDashboard />
    </ProtectedRoute>
  } 
/>
```

## 📚 Ressources Supplémentaires

- [Guide d'utilisation du Dashboard](./ADMIN_DASHBOARD_GUIDE.md)
- [Guide de protection Admin](./ADMIN_PROTECTION_GUIDE.md)
- [Documentation API](./API_DOCUMENTATION.md)

## ✅ Checklist d'Installation

- [ ] Route `/admin` ajoutée dans React Router
- [ ] Variables d'environnement configurées
- [ ] Middleware `AdminOnly` créé et enregistré
- [ ] Routes API admin protégées
- [ ] Utilisateur admin créé
- [ ] Test de connexion réussi
- [ ] Test d'accès au dashboard réussi
- [ ] Test de validation de ticket réussi

---

**Développé pour le Neuro Learning Center (NLC)**

Pour toute question, consultez la documentation ou contactez l'équipe de développement.
