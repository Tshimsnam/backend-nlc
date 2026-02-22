# Dashboard Admin Blade - Onglets Tickets et Agents

## 🎯 Fonctionnalités Ajoutées

Le dashboard admin Blade dispose maintenant de deux nouveaux onglets complets:

1. **Onglet Tickets** - Gestion complète de tous les tickets
2. **Onglet Agents Mobile** - Gestion des utilisateurs/agents

---

## 📋 Onglet Tickets

### Vue d'Ensemble

Affiche tous les tickets du système avec filtres avancés et pagination.

### Filtres Disponibles

**1. Recherche Textuelle**
- Champ: `tickets_search`
- Recherche dans: référence, nom, email, téléphone

**2. Filtre par Statut**
- Tous (défaut)
- En attente (`pending_cash`)
- Validés (`completed`)
- Échoués (`failed`)

**3. Filtre par Mode de Paiement**
- Tous (défaut)
- Caisse (`cash`)
- MaxiCash (`maxicash`)
- M-Pesa (`mpesa`)

### Colonnes du Tableau

1. **Référence** - Code unique du ticket (format monospace)
2. **Participant** - Nom complet
3. **Contact** - Email + téléphone
4. **Événement** - Titre de l'événement
5. **Mode** - Mode de paiement (badge)
6. **Montant** - Montant + devise
7. **Statut** - Statut du paiement (badge coloré)
8. **Date** - Date de création (format: dd/mm/yyyy HH:mm)
9. **Actions** - Bouton "Valider" si en attente

### Pagination

- **20 tickets par page**
- Navigation complète (Précédent, numéros, Suivant)
- Conservation des filtres lors de la navigation
- Paramètre URL: `tickets_page`

### Exemples d'URL

```
# Onglet tickets
/admin/dashboard?tab=tickets

# Avec recherche
/admin/dashboard?tab=tickets&tickets_search=john

# Avec statut
/admin/dashboard?tab=tickets&tickets_status=pending_cash

# Avec mode de paiement
/admin/dashboard?tab=tickets&tickets_pay_type=cash

# Combinaison + pagination
/admin/dashboard?tab=tickets&tickets_search=john&tickets_status=pending_cash&tickets_page=2
```

---

## 👥 Onglet Agents Mobile

### Vue d'Ensemble

Affiche tous les utilisateurs du système (agents, admins, etc.).

### Filtres Disponibles

**Recherche Textuelle**
- Champ: `agents_search`
- Recherche dans: nom, email

### Colonnes du Tableau

1. **ID** - Identifiant unique (#123)
2. **Nom** - Nom complet avec avatar (initiales)
3. **Email** - Adresse email
4. **Rôle** - Rôle principal (badge bleu)
5. **Date d'inscription** - Date de création (format: dd/mm/yyyy HH:mm)
6. **Statut** - Vérifié / Non vérifié (badge)

### Avatar

Chaque agent a un avatar avec ses initiales:
- Fond bleu clair
- Initiales en bleu foncé
- Forme circulaire

### Pagination

- **20 agents par page**
- Navigation complète
- Conservation des filtres
- Paramètre URL: `agents_page`

### Exemples d'URL

```
# Onglet agents
/admin/dashboard?tab=agents

# Avec recherche
/admin/dashboard?tab=agents&agents_search=john

# Avec pagination
/admin/dashboard?tab=agents&agents_page=2
```

---

## 🔧 Backend (DashboardController.php)

### Méthode `view(Request $request)`

**Nouvelles requêtes ajoutées:**

#### Tickets (Onglet Tickets)

```php
$allTicketsQuery = Ticket::with(['event', 'price']);

// Filtre par recherche
if ($request->has('tickets_search') && $request->tickets_search) {
    $search = $request->tickets_search;
    $allTicketsQuery->where(function ($q) use ($search) {
        $q->where('reference', 'like', "%{$search}%")
            ->orWhere('full_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%");
    });
}

// Filtre par statut
if ($request->has('tickets_status') && $request->tickets_status !== 'all') {
    $allTicketsQuery->where('payment_status', $request->tickets_status);
}

// Filtre par mode de paiement
if ($request->has('tickets_pay_type') && $request->tickets_pay_type !== 'all') {
    $allTicketsQuery->where('pay_type', $request->tickets_pay_type);
}

$allTickets = $allTicketsQuery->orderBy('created_at', 'desc')
    ->paginate(20, ['*'], 'tickets_page')
    ->appends($request->query());
```

#### Agents (Onglet Agents)

```php
$agentsQuery = User::with('roles');

// Filtre par recherche
if ($request->has('agents_search') && $request->agents_search) {
    $search = $request->agents_search;
    $agentsQuery->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
    });
}

$agents = $agentsQuery->orderBy('created_at', 'desc')
    ->paginate(20, ['*'], 'agents_page')
    ->appends($request->query());
```

**Variables passées à la vue:**

```php
return view('admin.dashboard', compact('user', 'stats', 'recentTickets', 'allTickets', 'agents'));
```

---

## 🎨 Frontend (Blade)

### Gestion des Onglets

**Alpine.js:**

```blade
<body x-data="{ 
    sidebarOpen: true, 
    currentTab: '{{ request('tab') ?? 'dashboard' }}', 
    validateModal: false, 
    selectedTicket: null 
}" x-init="
    if (window.location.search.includes('tab=tickets')) currentTab = 'tickets';
    if (window.location.search.includes('tab=agents')) currentTab = 'agents';
">
```

**Affichage conditionnel:**

```blade
<div x-show="currentTab === 'tickets'" style="display: none;">
    <!-- Contenu de l'onglet Tickets -->
</div>

<div x-show="currentTab === 'agents'" style="display: none;">
    <!-- Contenu de l'onglet Agents -->
</div>
```

### Structure des Formulaires

**Onglet Tickets:**

```blade
<form method="GET" action="{{ route('admin.dashboard.view') }}">
    <input type="hidden" name="tab" value="tickets" />
    <input type="text" name="tickets_search" />
    <button type="submit" name="tickets_status" value="all">Tous</button>
    <button type="submit" name="tickets_pay_type" value="cash">Caisse</button>
</form>
```

**Onglet Agents:**

```blade
<form method="GET" action="{{ route('admin.dashboard.view') }}">
    <input type="hidden" name="tab" value="agents" />
    <input type="text" name="agents_search" />
</form>
```

### Pagination Séparée

Chaque onglet a sa propre pagination:

- **Dashboard**: `page` (défaut Laravel)
- **Tickets**: `tickets_page`
- **Agents**: `agents_page`

Cela permet de naviguer indépendamment dans chaque onglet.

---

## 🔄 Flux d'Utilisation

### Scénario 1: Consulter Tous les Tickets

1. Utilisateur clique sur "Tickets" dans la sidebar
2. L'onglet Tickets s'affiche
3. URL devient: `/admin/dashboard?tab=tickets`
4. Tous les tickets sont affichés (20 par page)

### Scénario 2: Rechercher un Ticket Spécifique

1. Dans l'onglet Tickets
2. Tape "john" dans la barre de recherche
3. Clique sur "Rechercher"
4. URL: `/admin/dashboard?tab=tickets&tickets_search=john`
5. Seuls les tickets contenant "john" s'affichent

### Scénario 3: Filtrer par Statut et Mode

1. Dans l'onglet Tickets
2. Clique sur "En attente"
3. Clique sur "Caisse"
4. URL: `/admin/dashboard?tab=tickets&tickets_status=pending_cash&tickets_pay_type=cash`
5. Seuls les tickets en attente payés en caisse s'affichent

### Scénario 4: Valider un Ticket

1. Dans l'onglet Tickets
2. Trouve un ticket en attente
3. Clique sur "Valider"
4. Modal de confirmation s'ouvre
5. Confirme la validation
6. Le ticket passe à "Validé"

### Scénario 5: Consulter les Agents

1. Utilisateur clique sur "Agents Mobile" dans la sidebar
2. L'onglet Agents s'affiche
3. URL devient: `/admin/dashboard?tab=agents`
4. Tous les agents sont affichés (20 par page)

### Scénario 6: Rechercher un Agent

1. Dans l'onglet Agents
2. Tape "john" dans la barre de recherche
3. Clique sur "Rechercher"
4. URL: `/admin/dashboard?tab=agents&agents_search=john`
5. Seuls les agents contenant "john" s'affichent

---

## 📊 Statistiques

### Onglet Tickets

**Affichage en haut:**
```
Tous les tickets                    X ticket(s) au total
```

**Affichage en bas:**
```
Affichage de X à Y sur Z résultats
```

### Onglet Agents

**Affichage en haut:**
```
Agents Mobile                       X agent(s) au total
```

**Affichage en bas:**
```
Affichage de X à Y sur Z résultats
```

---

## 🎨 Design

### Badges de Statut (Tickets)

- **Validé**: Vert (`bg-green-100 text-green-800`)
- **En attente**: Orange (`bg-orange-100 text-orange-800`)
- **Échoué**: Rouge (`bg-red-100 text-red-800`)

### Badges de Mode de Paiement

- **Tous les modes**: Gris (`bg-gray-100 text-gray-800`)

### Badges de Rôle (Agents)

- **Rôle assigné**: Bleu (`bg-blue-100 text-blue-800`)
- **Aucun rôle**: Gris (`bg-gray-100 text-gray-800`)

### Badges de Statut (Agents)

- **Vérifié**: Vert (`bg-green-100 text-green-800`)
- **Non vérifié**: Orange (`bg-orange-100 text-orange-800`)

---

## 🔧 Configuration

### Nombre d'Éléments par Page

**Fichier:** `app/Http/Controllers/Admin/DashboardController.php`

**Tickets:**
```php
->paginate(20, ['*'], 'tickets_page') // Changer 20
```

**Agents:**
```php
->paginate(20, ['*'], 'agents_page') // Changer 20
```

### Ajouter un Filtre (Tickets)

**Backend:**
```php
if ($request->has('tickets_event_id') && $request->tickets_event_id !== 'all') {
    $allTicketsQuery->where('event_id', $request->tickets_event_id);
}
```

**Frontend:**
```blade
<select name="tickets_event_id">
    <option value="all">Tous les événements</option>
    @foreach($events as $event)
        <option value="{{ $event->id }}">{{ $event->title }}</option>
    @endforeach
</select>
```

### Ajouter un Filtre (Agents)

**Backend:**
```php
if ($request->has('agents_role') && $request->agents_role !== 'all') {
    $agentsQuery->whereHas('roles', function($q) use ($request) {
        $q->where('name', $request->agents_role);
    });
}
```

**Frontend:**
```blade
<button type="submit" name="agents_role" value="Administrateur">Admins</button>
<button type="submit" name="agents_role" value="Educateur">Éducateurs</button>
```

---

## 🧪 Tests

### Tester l'Onglet Tickets

1. Cliquer sur "Tickets" dans la sidebar
2. Vérifier que tous les tickets s'affichent
3. Tester la recherche
4. Tester les filtres par statut
5. Tester les filtres par mode de paiement
6. Tester la pagination
7. Tester la validation d'un ticket

### Tester l'Onglet Agents

1. Cliquer sur "Agents Mobile" dans la sidebar
2. Vérifier que tous les agents s'affichent
3. Vérifier les avatars avec initiales
4. Tester la recherche
5. Tester la pagination
6. Vérifier les rôles affichés
7. Vérifier les statuts (vérifié/non vérifié)

---

## 🐛 Dépannage

### L'Onglet ne S'Affiche Pas

**Vérifier:**
1. Alpine.js est chargé
2. La variable `currentTab` est initialisée
3. Le paramètre `tab` est dans l'URL

**Solution:**
```blade
<body x-data="{ currentTab: '{{ request('tab') ?? 'dashboard' }}' }">
```

### Les Filtres ne Fonctionnent Pas

**Vérifier:**
1. Le formulaire a `method="GET"`
2. L'input caché `<input type="hidden" name="tab" value="tickets" />` est présent
3. Les noms des inputs correspondent au backend

### La Pagination ne Conserve pas les Filtres

**Vérifier:**
```php
->paginate(20, ['*'], 'tickets_page')->appends($request->query());
```

Le `.appends($request->query())` est essentiel.

### Les Avatars ne S'Affichent Pas

**Vérifier:**
```blade
{{ strtoupper(substr($agent->name, 0, 2)) }}
```

Assurez-vous que `$agent->name` existe.

---

## 📝 Fichiers Modifiés

### Backend
- `app/Http/Controllers/Admin/DashboardController.php`
  - Méthode `view()` étendue
  - Ajout des requêtes pour tickets et agents
  - Filtres et pagination

### Frontend
- `resources/views/admin/dashboard.blade.php`
  - Onglet Tickets complet
  - Onglet Agents complet
  - Gestion des onglets via Alpine.js
  - Formulaires de filtres
  - Tableaux avec pagination

---

## ✅ Checklist de Déploiement

- [x] Modifier le contrôleur backend
- [x] Ajouter l'onglet Tickets
- [x] Ajouter l'onglet Agents
- [x] Ajouter les filtres
- [x] Ajouter la pagination
- [x] Gérer les onglets via URL
- [ ] Tester tous les filtres
- [ ] Tester la pagination
- [ ] Tester la validation de tickets
- [ ] Déployer en production
- [ ] Former les administrateurs

---

**Date de création:** 2024
**Dernière mise à jour:** 2024
