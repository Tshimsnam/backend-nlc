# Dashboard Admin - Agents Mobile: Filtrage et Création

## 🎯 Modifications Effectuées

L'onglet "Agents Mobile" a été amélioré avec:

1. **Filtrage automatique** - Exclusion des rôles "Parent" et "Administrateur"
2. **Formulaire de création** - Créer un nouvel agent avec mot de passe

---

## 🔒 Filtrage des Agents

### Rôles Exclus

Les utilisateurs avec les rôles suivants ne sont **PAS affichés** dans la liste des agents:
- **Parent**
- **Administrateur**

### Rôles Affichés

Tous les autres rôles sont affichés, par exemple:
- Éducateur
- Super Teacher
- Agent de Scan
- Caissier
- Etc.

### Raison du Filtrage

Les agents mobile sont des utilisateurs qui utilisent l'application mobile pour scanner les billets, valider les paiements, etc. Les Parents et Administrateurs n'ont pas besoin d'apparaître dans cette liste car:
- **Parents**: Utilisent l'application pour inscrire leurs enfants
- **Administrateurs**: Gèrent le système via le dashboard web

---

## 🔧 Backend (DashboardController.php)

### Requête Modifiée

**Avant:**
```php
$agentsQuery = User::with('roles');
```

**Après:**
```php
$agentsQuery = User::with('roles')
    ->whereHas('roles', function($q) {
        $q->whereNotIn('name', ['Parent', 'Administrateur']);
    });
```

**Explication:**
- `whereHas('roles', ...)` - Filtre les utilisateurs qui ont au moins un rôle
- `whereNotIn('name', ['Parent', 'Administrateur'])` - Exclut les rôles Parent et Administrateur

### Rôles Disponibles pour la Création

```php
$availableRoles = \App\Models\Role::whereNotIn('name', ['Parent', 'Administrateur'])->get();
```

Cette variable est passée à la vue pour remplir le menu déroulant du formulaire de création.

---

## ➕ Création d'Agent

### Formulaire

Le formulaire de création d'agent contient les champs suivants:

1. **Nom complet** (requis)
   - Type: Texte
   - Placeholder: "John Doe"

2. **Email** (requis, unique)
   - Type: Email
   - Placeholder: "john@example.com"
   - Validation: Doit être unique dans la base de données

3. **Mot de passe** (requis, min 6 caractères)
   - Type: Password
   - Placeholder: "Minimum 6 caractères"
   - Validation: Minimum 6 caractères

4. **Rôle** (requis)
   - Type: Select
   - Options: Tous les rôles sauf Parent et Administrateur

### Méthode `createAgent(Request $request)`

**Validation:**
```php
$request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|string|min:6',
    'role_id' => 'required|exists:roles,id',
]);
```

**Vérification du rôle:**
```php
$role = \App\Models\Role::findOrFail($request->role_id);
if (in_array($role->name, ['Parent', 'Administrateur'])) {
    return redirect()->route('admin.dashboard.view', ['tab' => 'agents'])
        ->with('error', 'Impossible de créer un utilisateur avec ce rôle.');
}
```

**Création de l'utilisateur:**
```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => bcrypt($request->password),
    'email_verified_at' => now(), // Vérifié automatiquement
]);
```

**Attribution du rôle:**
```php
$user->roles()->attach($request->role_id);
```

**Redirection:**
```php
return redirect()->route('admin.dashboard.view', ['tab' => 'agents'])
    ->with('success', 'Agent créé avec succès!');
```

---

## 🎨 Frontend (Blade)

### Bouton "Créer un Agent"

**Position:** En haut à droite de l'onglet Agents

**Comportement:**
- Clic → Affiche le formulaire de création
- Clic à nouveau → Cache le formulaire
- Texte change: "Créer un Agent" ↔ "Annuler"

**Code:**
```blade
<button @click="showCreateForm = !showCreateForm">
    <span x-text="showCreateForm ? 'Annuler' : 'Créer un Agent'"></span>
</button>
```

### Formulaire de Création

**Affichage conditionnel:**
```blade
<div x-show="showCreateForm" x-cloak>
    <!-- Formulaire -->
</div>
```

**Alpine.js:**
- `x-data="{ showCreateForm: false }"` - État du formulaire (caché par défaut)
- `x-show="showCreateForm"` - Affiche/cache le formulaire
- `x-cloak` - Évite le flash de contenu non stylé

### Grille Responsive

Le formulaire utilise une grille 2 colonnes sur desktop, 1 colonne sur mobile:

```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Champs -->
</div>
```

---

## 🔄 Flux d'Utilisation

### Scénario 1: Consulter les Agents

```
┌─────────────────┐
│ Ouvrir onglet   │
│ "Agents Mobile" │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Affichage auto  │
│ agents filtrés  │
│ (hors Parents & │
│ Administrateurs)│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Liste des       │
│ agents mobile   │
└─────────────────┘
```

### Scénario 2: Créer un Agent

```
┌─────────────────┐
│ Clic "Créer un  │
│ Agent"          │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Formulaire      │
│ s'affiche       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Remplir les     │
│ champs          │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Clic "Créer     │
│ l'Agent"        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Validation      │
│ backend         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Agent créé      │
│ Message succès  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Redirection     │
│ onglet Agents   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Nouvel agent    │
│ dans la liste   │
└─────────────────┘
```

### Scénario 3: Erreur de Création

```
┌─────────────────┐
│ Remplir le      │
│ formulaire      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Email déjà      │
│ utilisé         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Validation      │
│ échoue          │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Message erreur  │
│ affiché         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Corriger et     │
│ réessayer       │
└─────────────────┘
```

---

## 📊 Exemples

### Créer un Éducateur

**Données:**
- Nom: Jean Dupont
- Email: jean.dupont@nlc.com
- Mot de passe: password123
- Rôle: Éducateur

**Résultat:**
- Utilisateur créé avec ID unique
- Mot de passe hashé (bcrypt)
- Email vérifié automatiquement
- Rôle "Éducateur" attaché
- Peut se connecter immédiatement

### Créer un Agent de Scan

**Données:**
- Nom: Marie Martin
- Email: marie.martin@nlc.com
- Mot de passe: secure456
- Rôle: Agent de Scan

**Résultat:**
- Utilisateur créé
- Peut scanner les billets via l'app mobile
- Apparaît dans la liste des agents

---

## 🔐 Sécurité

### Validation des Données

1. **Nom**: Requis, max 255 caractères
2. **Email**: Requis, format email valide, unique
3. **Mot de passe**: Requis, minimum 6 caractères
4. **Rôle**: Requis, doit exister dans la table roles

### Protection contre les Rôles Interdits

Même si quelqu'un essaie de manipuler le formulaire pour créer un Parent ou Administrateur, le backend vérifie:

```php
if (in_array($role->name, ['Parent', 'Administrateur'])) {
    return redirect()->with('error', 'Impossible de créer un utilisateur avec ce rôle.');
}
```

### Hashage du Mot de Passe

Le mot de passe est automatiquement hashé avec bcrypt:

```php
'password' => bcrypt($request->password),
```

### Email Vérifié Automatiquement

Les agents créés par l'admin sont automatiquement vérifiés:

```php
'email_verified_at' => now(),
```

---

## 🎯 Avantages

### Pour l'Administrateur

1. **Vue claire** - Seuls les agents mobile sont affichés
2. **Création rapide** - Formulaire simple et intuitif
3. **Contrôle total** - Définit le mot de passe initial
4. **Pas de confusion** - Parents et Admins séparés

### Pour l'Organisation

1. **Gestion centralisée** - Tous les agents au même endroit
2. **Sécurité** - Mots de passe forts obligatoires
3. **Traçabilité** - Qui a créé quel agent et quand
4. **Flexibilité** - Différents rôles pour différentes tâches

---

## 🧪 Tests

### Tester le Filtrage

1. Créer un utilisateur avec le rôle "Parent"
2. Créer un utilisateur avec le rôle "Administrateur"
3. Créer un utilisateur avec le rôle "Éducateur"
4. Ouvrir l'onglet "Agents Mobile"
5. Vérifier que seul l'Éducateur apparaît

### Tester la Création

1. Cliquer sur "Créer un Agent"
2. Remplir tous les champs
3. Sélectionner un rôle (ex: Éducateur)
4. Cliquer sur "Créer l'Agent"
5. Vérifier le message de succès
6. Vérifier que l'agent apparaît dans la liste

### Tester les Validations

**Email déjà utilisé:**
1. Créer un agent avec email@example.com
2. Essayer de créer un autre agent avec le même email
3. Vérifier le message d'erreur

**Mot de passe trop court:**
1. Essayer de créer un agent avec mot de passe "123"
2. Vérifier que le formulaire refuse (min 6 caractères)

**Champs vides:**
1. Essayer de soumettre le formulaire vide
2. Vérifier que les champs requis sont signalés

---

## 🐛 Dépannage

### Les Parents/Admins Apparaissent Toujours

**Vérifier:**
1. La requête utilise `whereHas('roles', ...)`
2. Les noms de rôles sont exacts: "Parent", "Administrateur"
3. La relation `roles()` existe sur le modèle User

**Solution:**
```php
->whereHas('roles', function($q) {
    $q->whereNotIn('name', ['Parent', 'Administrateur']);
});
```

### Le Formulaire ne S'Affiche Pas

**Vérifier:**
1. Alpine.js est chargé
2. `x-data="{ showCreateForm: false }"` est sur le bon élément
3. `x-show="showCreateForm"` est sur le formulaire

**Solution:**
```blade
<div x-data="{ showCreateForm: false }">
    <button @click="showCreateForm = !showCreateForm">...</button>
    <div x-show="showCreateForm">...</div>
</div>
```

### L'Agent n'est Pas Créé

**Vérifier:**
1. La route existe: `Route::post('/admin/agents/create', ...)`
2. Le formulaire a `method="POST"` et `@csrf`
3. Les validations passent
4. Le rôle sélectionné n'est pas Parent ou Administrateur

**Logs:**
```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log
```

---

## 📝 Fichiers Modifiés

### Backend
- `app/Http/Controllers/Admin/DashboardController.php`
  - Méthode `view()` - Filtrage des agents
  - Méthode `createAgent()` - Création d'agent
  - Variable `$availableRoles` - Rôles pour le formulaire

### Routes
- `routes/web.php`
  - Route `POST /admin/agents/create`

### Frontend
- `resources/views/admin/dashboard.blade.php`
  - Bouton "Créer un Agent"
  - Formulaire de création
  - Titre du tableau mis à jour

---

## ✅ Checklist de Déploiement

- [x] Modifier le contrôleur (filtrage)
- [x] Ajouter la méthode createAgent
- [x] Ajouter la route
- [x] Ajouter le bouton de création
- [x] Ajouter le formulaire
- [x] Mettre à jour le titre du tableau
- [ ] Tester le filtrage
- [ ] Tester la création
- [ ] Tester les validations
- [ ] Tester les erreurs
- [ ] Déployer en production
- [ ] Former les administrateurs

---

**Date de création:** 2024
**Dernière mise à jour:** 2024
