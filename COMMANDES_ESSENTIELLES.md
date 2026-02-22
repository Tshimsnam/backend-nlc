# Commandes Essentielles - Système de Billets

## 🚀 Installation et Configuration

### 1. Vérifier l'État du Système
```bash
php verifier-systeme.php
```
✅ Vérifie les colonnes, événements et statistiques

### 2. Exécuter les Migrations
```bash
php artisan migrate
```
✅ Crée/met à jour les tables de la base de données

### 3. Créer les Données de Test
```bash
php artisan db:seed --class=EventSeeder
```
✅ Crée l'événement "Le Grand Salon de l'Autisme" avec toutes les données

### 4. Tester les Statistiques
```bash
php test-statistiques.php
```
✅ Affiche les statistiques comme dans le dashboard

---

## 🔧 Maintenance

### Vider le Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Vider Tout le Cache
```bash
php artisan optimize:clear
```

### Reconstruire le Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🗄️ Base de Données

### Voir l'État des Migrations
```bash
php artisan migrate:status
```

### Rollback de la Dernière Migration
```bash
php artisan migrate:rollback
```

### Rollback de Toutes les Migrations
```bash
php artisan migrate:reset
```

### Réinitialiser et Re-migrer
```bash
php artisan migrate:fresh
```

### Réinitialiser et Seeder
```bash
php artisan migrate:fresh --seed
```

---

## 🧪 Tests et Vérifications

### Test Complet du Système
```bash
# 1. Vérifier l'installation
php verifier-systeme.php

# 2. Tester les statistiques
php test-statistiques.php

# 3. Vérifier les routes
php artisan route:list | grep admin

# 4. Vérifier les migrations
php artisan migrate:status
```

### Vérifier les Modèles
```bash
php artisan tinker
```
Puis dans Tinker:
```php
// Vérifier les événements
App\Models\Event::count();
App\Models\Event::first();

// Vérifier les billets
App\Models\Ticket::count();
App\Models\Ticket::whereNotNull('physical_qr_id')->count();
App\Models\Ticket::whereNull('physical_qr_id')->count();

// Quitter
exit
```

---

## 🌐 Serveur de Développement

### Démarrer le Serveur
```bash
php artisan serve
```
Accès: http://localhost:8000

### Démarrer sur un Port Spécifique
```bash
php artisan serve --port=8080
```

### Démarrer avec une IP Spécifique
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 📊 Statistiques et Données

### Compter les Billets
```bash
php artisan tinker
```
```php
// Total billets
App\Models\Ticket::count();

// Billets physiques
App\Models\Ticket::whereNotNull('physical_qr_id')->count();

// Billets en ligne
App\Models\Ticket::whereNull('physical_qr_id')->count();

// Billets validés
App\Models\Ticket::where('payment_status', 'completed')->count();

// Revenus total
App\Models\Ticket::where('payment_status', 'completed')->sum('amount');
```

### Voir les Événements
```bash
php artisan tinker
```
```php
// Tous les événements
App\Models\Event::all();

// Premier événement avec détails
$event = App\Models\Event::first();
echo $event->title;
echo $event->contact_phone;
echo $event->organizer;
print_r($event->sponsors);
```

---

## 🔐 Utilisateurs et Authentification

### Créer un Utilisateur Admin
```bash
php artisan tinker
```
```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now()
]);

// Attacher le rôle admin (si vous avez un système de rôles)
$adminRole = App\Models\Role::where('name', 'Administrateur')->first();
$user->roles()->attach($adminRole->id);
```

### Réinitialiser un Mot de Passe
```bash
php artisan tinker
```
```php
$user = App\Models\User::where('email', 'admin@example.com')->first();
$user->password = bcrypt('nouveau_mot_de_passe');
$user->save();
```

---

## 📝 Logs et Débogage

### Voir les Logs en Temps Réel
```bash
tail -f storage/logs/laravel.log
```

### Vider les Logs
```bash
# Windows
type nul > storage/logs/laravel.log

# Linux/Mac
> storage/logs/laravel.log
```

### Activer le Mode Debug
Dans `.env`:
```
APP_DEBUG=true
```

---

## 🎨 Assets et Frontend

### Compiler les Assets (si vous utilisez Mix/Vite)
```bash
npm run dev
```

### Compiler pour Production
```bash
npm run build
```

### Watcher (développement)
```bash
npm run watch
```

---

## 🔄 Git et Versioning

### Sauvegarder les Changements
```bash
git add .
git commit -m "Ajout du système de billets physiques vs en ligne"
git push
```

### Voir l'État
```bash
git status
```

### Voir les Différences
```bash
git diff
```

---

## 📦 Composer et Dépendances

### Installer les Dépendances
```bash
composer install
```

### Mettre à Jour les Dépendances
```bash
composer update
```

### Vérifier les Dépendances Obsolètes
```bash
composer outdated
```

---

## 🚨 Dépannage Rapide

### Problème: Erreur 500
```bash
# 1. Vérifier les logs
tail -f storage/logs/laravel.log

# 2. Vider le cache
php artisan optimize:clear

# 3. Vérifier les permissions
chmod -R 775 storage bootstrap/cache
```

### Problème: Les migrations ne fonctionnent pas
```bash
# 1. Vérifier la connexion DB
php artisan tinker
DB::connection()->getPdo();

# 2. Voir l'état des migrations
php artisan migrate:status

# 3. Forcer la migration
php artisan migrate --force
```

### Problème: Les statistiques sont incorrectes
```bash
# 1. Vérifier les données
php test-statistiques.php

# 2. Vérifier dans Tinker
php artisan tinker
App\Models\Ticket::whereNotNull('physical_qr_id')->count();
App\Models\Ticket::whereNull('physical_qr_id')->count();

# 3. Vider le cache
php artisan cache:clear
```

---

## 📋 Checklist de Déploiement

```bash
# 1. Mettre à jour le code
git pull

# 2. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 3. Exécuter les migrations
php artisan migrate --force

# 4. Vider et reconstruire le cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Vérifier le système
php verifier-systeme.php

# 6. Tester les statistiques
php test-statistiques.php

# 7. Redémarrer le serveur (si nécessaire)
# Dépend de votre configuration (Apache, Nginx, etc.)
```

---

## 🎯 Commandes Personnalisées

### Créer une Commande Artisan
```bash
php artisan make:command NomDeLaCommande
```

### Lister Toutes les Commandes
```bash
php artisan list
```

### Aide sur une Commande
```bash
php artisan help migrate
```

---

## 📊 Statistiques Rapides (One-Liners)

### Total de Billets
```bash
php artisan tinker --execute="echo App\Models\Ticket::count();"
```

### Billets Physiques
```bash
php artisan tinker --execute="echo App\Models\Ticket::whereNotNull('physical_qr_id')->count();"
```

### Billets En Ligne
```bash
php artisan tinker --execute="echo App\Models\Ticket::whereNull('physical_qr_id')->count();"
```

### Revenus Total
```bash
php artisan tinker --execute="echo App\Models\Ticket::where('payment_status', 'completed')->sum('amount');"
```

---

## 🔗 URLs Importantes

### Dashboard Admin
```
http://localhost:8000/admin/login
http://localhost:8000/admin/dashboard
```

### API (si disponible)
```
http://localhost:8000/api/events
http://localhost:8000/api/tickets
```

---

## 📝 Notes

- Toujours exécuter `php verifier-systeme.php` après une mise à jour
- Vider le cache après modification de configuration
- Tester les statistiques avec `php test-statistiques.php`
- Consulter les logs en cas d'erreur
- Faire des backups réguliers de la base de données

---

**Astuce**: Créez un alias pour les commandes fréquentes:
```bash
# Dans votre .bashrc ou .zshrc
alias pa="php artisan"
alias pat="php artisan tinker"
alias pam="php artisan migrate"
alias pac="php artisan cache:clear"
```

Ensuite:
```bash
pa migrate
pat
pac
```
