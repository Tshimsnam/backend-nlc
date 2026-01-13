# Guide de Protection Admin - Routes DELETE

## 🔒 Vue d'ensemble

Toutes les routes `DELETE` du système NLC sont maintenant **protégées** et accessibles uniquement aux utilisateurs ayant le rôle `admin`.

## 📋 Routes Protégées

Les routes suivantes nécessitent le rôle `admin` :

| Route | Contrôleur | Description |
|-------|------------|-------------|
| `DELETE /api/children/{child}` | ChildController@destroy | Supprimer un enfant |
| `DELETE /api/programs/{program}` | ProgramController@destroy | Supprimer un programme |
| `DELETE /api/courses/{course}` | CourseController@destroy | Supprimer un cours |
| `DELETE /api/appointments/{appointment}` | AppointmentController@destroy | Supprimer un rendez-vous |
| `DELETE /api/messages/{message}` | MessageController@destroy | Supprimer un message |
| `DELETE /api/reports/{report}` | ReportController@destroy | Supprimer un rapport |
| `DELETE /api/notifications/{notification}` | NotificationController@destroy | Supprimer une notification |
| `DELETE /api/dossiers/{dossier}` | DossierController@destroy | Supprimer un dossier |
| `DELETE /api/settings/{setting}` | SettingController@destroy | Supprimer un paramètre |

## 🛠️ Implémentation Technique

### 1. Middleware `AdminOnly`

**Fichier:** `app/Http/Middleware/AdminOnly.php`

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
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Accès refusé. Seuls les administrateurs peuvent effectuer cette action.'
            ], 403);
        }

        return $next($request);
    }
}
```

### 2. Enregistrement du Middleware

**Fichier:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'admin.only' => \App\Http\Middleware\AdminOnly::class,
    ]);
})
```

### 3. Configuration des Routes

**Fichier:** `routes/api.php`

```php
// Routes DELETE - Réservées aux administrateurs uniquement
Route::middleware(['admin.only'])->group(function () {
    Route::delete('/children/{child}', [ChildController::class, 'destroy'])->name('children.destroy');
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/dossiers/{dossier}', [DossierController::class, 'destroy'])->name('dossiers.destroy');
    Route::delete('/settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy');
});
```

## 📝 Exemples d'Utilisation

### ✅ Cas 1 : Utilisateur Admin (Réussite)

**Requête:**
```bash
curl -X DELETE http://localhost:8000/api/children/uuid-child-123 \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json"
```

**Réponse:** `200 OK`
```json
{
  "message": "Enfant supprimé avec succès"
}
```

---

### ❌ Cas 2 : Utilisateur Parent (Échec)

**Requête:**
```bash
curl -X DELETE http://localhost:8000/api/children/uuid-child-123 \
  -H "Authorization: Bearer {parent_token}" \
  -H "Content-Type: application/json"
```

**Réponse:** `403 Forbidden`
```json
{
  "message": "Accès refusé. Seuls les administrateurs peuvent effectuer cette action."
}
```

---

### ❌ Cas 3 : Utilisateur Éducateur (Échec)

**Requête:**
```bash
curl -X DELETE http://localhost:8000/api/courses/uuid-course-456 \
  -H "Authorization: Bearer {educator_token}" \
  -H "Content-Type: application/json"
```

**Réponse:** `403 Forbidden`
```json
{
  "message": "Accès refusé. Seuls les administrateurs peuvent effectuer cette action."
}
```

---

### ❌ Cas 4 : Non Authentifié (Échec)

**Requête:**
```bash
curl -X DELETE http://localhost:8000/api/programs/uuid-program-789 \
  -H "Content-Type: application/json"
```

**Réponse:** `401 Unauthorized`
```json
{
  "message": "Unauthenticated."
}
```

## 🔍 Comment Vérifier

### 1. Lister toutes les routes DELETE

```bash
php artisan route:list --path=api --method=DELETE
```

### 2. Vérifier le middleware appliqué

```bash
php artisan route:list --path=api --method=DELETE --columns=uri,name,action,middleware
```

### 3. Tester avec Postman ou Insomnia

1. **Créer une requête DELETE**
   - URL: `http://localhost:8000/api/children/{id}`
   - Headers: 
     - `Authorization: Bearer {token}`
     - `Content-Type: application/json`

2. **Tester avec différents rôles:**
   - Admin → ✅ Doit fonctionner
   - Educator → ❌ Doit retourner 403
   - Parent → ❌ Doit retourner 403
   - Specialist → ❌ Doit retourner 403

## 🧪 Tests avec CURL

### Créer un utilisateur admin et obtenir un token

```bash
# 1. Se connecter en tant qu'admin
curl -X POST http://localhost:8000/api/login \
  -H "X-API-SECRET: votre_secret" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@nlc.fr",
    "password": "votre_mot_de_passe"
  }'
```

**Réponse:**
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "email": "admin@nlc.fr",
    "role": "admin"
  }
}
```

### Tester la suppression

```bash
# 2. Utiliser le token pour supprimer
curl -X DELETE http://localhost:8000/api/children/uuid-xxx \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json"
```

## 🎯 Matrice de Permissions

| Rôle | GET | POST | PUT/PATCH | DELETE |
|------|-----|------|-----------|--------|
| **admin** | ✅ | ✅ | ✅ | ✅ |
| **super-teacher** | ✅ | ✅ (programmes) | ✅ | ❌ |
| **educator** | ✅ (limité) | ✅ (limité) | ✅ (limité) | ❌ |
| **specialist** | ✅ (limité) | ✅ (limité) | ✅ (limité) | ❌ |
| **receptionist** | ✅ (limité) | ✅ (limité) | ✅ (limité) | ❌ |
| **parent** | ✅ (très limité) | ❌ | ❌ | ❌ |

## 🔐 Sécurité Renforcée

### Recommandations Additionnelles

1. **Logs des Suppressions**
   - Envisagez d'ajouter un système de logs pour tracer toutes les suppressions
   - Utile pour l'audit et la conformité RGPD

2. **Soft Deletes**
   - Utilisez les soft deletes Laravel pour ne pas supprimer définitivement
   - Permet la récupération des données en cas d'erreur

3. **Confirmation Double**
   - Ajoutez une confirmation côté frontend pour les suppressions critiques
   - Exemple: "Êtes-vous sûr de vouloir supprimer cet enfant ?"

4. **Notifications**
   - Notifiez automatiquement les super-admins lors de suppressions importantes
   - Conservez un historique des actions

## 📊 Exemple d'Implémentation Soft Delete

Si vous souhaitez implémenter les soft deletes (suppression douce) :

### 1. Ajouter le trait dans les modèles

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasUuids, SoftDeletes;
}
```

### 2. Ajouter la colonne dans les migrations

```php
$table->softDeletes(); // Ajoute deleted_at
```

### 3. Restaurer un élément supprimé

```php
$child = Child::withTrashed()->find($id);
$child->restore();
```

### 4. Supprimer définitivement

```php
$child->forceDelete(); // Suppression définitive
```

## 🚨 Codes d'Erreur

| Code | Signification | Raison |
|------|---------------|--------|
| `200` | OK | Suppression réussie |
| `401` | Unauthorized | Token manquant ou invalide |
| `403` | Forbidden | Utilisateur non-admin |
| `404` | Not Found | Ressource inexistante |
| `500` | Server Error | Erreur serveur |

## 📞 Dépannage

### Problème : "Accès refusé" même avec un admin

**Solution 1:** Vérifier le rôle de l'utilisateur
```bash
# Dans tinker
php artisan tinker
>>> $user = User::find(1);
>>> $user->role; // Doit retourner "admin"
```

**Solution 2:** Vérifier que le middleware est bien enregistré
```bash
php artisan route:list --path=api/children --method=DELETE
```

**Solution 3:** Nettoyer le cache
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Problème : Routes DELETE non trouvées

**Solution:** Vérifier que les routes sont bien définies
```bash
php artisan route:list --method=DELETE
```

## 📚 Ressources

- Documentation Laravel Middleware: https://laravel.com/docs/middleware
- Documentation Laravel Authorization: https://laravel.com/docs/authorization
- Documentation Sanctum: https://laravel.com/docs/sanctum

## ✅ Checklist de Vérification

- [x] Middleware `AdminOnly` créé
- [x] Middleware enregistré dans `bootstrap/app.php`
- [x] Routes DELETE protégées dans `routes/api.php`
- [x] Documentation API mise à jour
- [x] Guide de configuration mis à jour
- [ ] Tests unitaires pour le middleware
- [ ] Tests d'intégration pour les routes
- [ ] Logs des suppressions implémentés
- [ ] Soft deletes activés (optionnel)

---

**Développé pour le Neuro Learning Center (NLC)**

Pour toute question sur la protection admin, consultez ce guide ou l'équipe de développement.

