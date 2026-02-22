# Dashboard Admin Blade - Filtres et Pagination

## 🎯 Modifications Effectuées

Le dashboard admin Blade (`resources/views/admin/dashboard.blade.php`) a été amélioré avec:

1. **Cartes cachées** - Événements et Utilisateurs
2. **Système de filtres** - Recherche et statut
3. **Pagination complète** - Navigation entre les pages
4. **Compteur de résultats** - Affichage du nombre total

---

## 📊 Cartes de Statistiques

### Cartes Visibles

**Première ligne (4 cartes):**
1. Total Tickets (bleu)
2. Tickets Validés (vert)
3. Tickets En Attente (orange)
4. Scans de Billets (indigo)

**Deuxième ligne (4 cartes):**
1. Scans d'Événements (cyan)
2. Billets Uniques Scannés (teal)
3. Revenus (purple)

### Cartes Cachées

Les cartes suivantes ont été commentées (cachées):
- Total Événements (pink)
- Total Utilisateurs (gray)

Pour les réafficher, décommentez les sections dans le fichier Blade.

---

## 🔍 Système de Filtres

### Barre de Recherche

**Champ:** Texte libre

**Recherche dans:**
- Référence du ticket
- Nom complet du participant
- Email
- Numéro de téléphone

**Exemple:**
```
Recherche: "john" → Trouve tous les tickets avec "john" dans le nom, email, etc.
```

### Filtre par Statut

**Boutons disponibles:**
- **Tous** - Affiche tous les tickets (défaut)
- **En attente** - Tickets avec `payment_status = 'pending_cash'`
- **Validés** - Tickets avec `payment_status = 'completed'`
- **Échoués** - Tickets avec `payment_status = 'failed'`

**Comportement:**
- Le bouton actif est en bleu
- Les autres boutons sont en gris
- Un clic sur un bouton soumet le formulaire

### Boutons d'Action

**Rechercher:**
- Soumet le formulaire avec les filtres sélectionnés
- Icône de loupe

**Réinitialiser:**
- Efface tous les filtres
- Redirige vers la page sans paramètres
- Affiche tous les tickets

---

## 📄 Pagination

### Affichage

**En haut du tableau:**
```
Tickets récents                    X ticket(s) au total
```

**En bas du tableau:**
```
Affichage de X à Y sur Z résultats

[Précédent] [1] [2] [3] ... [10] [Suivant]
```

### Fonctionnalités

**Navigation:**
- Bouton "Précédent" (désactivé si page 1)
- Numéros de page (max 5 visibles + ellipses)
- Bouton "Suivant" (désactivé si dernière page)

**Nombre par page:**
- 15 tickets par page (configurable dans le contrôleur)

**Conservation des filtres:**
- Les filtres sont conservés lors de la navigation
- URL: `?search=john&status=pending_cash&page=2`

---

## 🔧 Backend (DashboardController.php)

### Méthode `view(Request $request)`

**Modifications:**

1. **Ajout du paramètre `Request $request`**
   - Permet de récupérer les paramètres de filtrage

2. **Filtre par recherche**
   ```php
   if ($request->has('search') && $request->search) {
       $search = $request->search;
       $query->where(function ($q) use ($search) {
           $q->where('reference', 'like', "%{$search}%")
               ->orWhere('full_name', 'like', "%{$search}%")
               ->orWhere('email', 'like', "%{$search}%")
               ->orWhere('phone', 'like', "%{$search}%");
       });
   }
   ```

3. **Filtre par statut**
   ```php
   if ($request->has('status') && $request->status !== 'all') {
       $query->where('payment_status', $request->status);
   }
   ```

4. **Pagination**
   ```php
   $recentTickets = $query->orderBy('created_at', 'desc')
       ->paginate(15)
       ->appends($request->query());
   ```

**Note:** `.appends($request->query())` conserve les paramètres de filtrage dans les liens de pagination.

---

## 🎨 Frontend (Blade)

### Section Filtres

**Structure:**
```blade
<div x-data="{ searchTerm: '{{ request('search') ?? '' }}', statusFilter: '{{ request('status') ?? 'all' }}' }">
    <form method="GET" action="{{ route('admin.dashboard.view') }}">
        <!-- Barre de recherche -->
        <input type="text" name="search" />
        
        <!-- Boutons de statut -->
        <button type="submit" name="status" value="all">Tous</button>
        <button type="submit" name="status" value="pending_cash">En attente</button>
        <button type="submit" name="status" value="completed">Validés</button>
        <button type="submit" name="status" value="failed">Échoués</button>
        
        <!-- Boutons d'action -->
        <button type="submit">Rechercher</button>
        <a href="{{ route('admin.dashboard.view') }}">Réinitialiser</a>
    </form>
</div>
```

**Alpine.js:**
- `x-data` - Initialise les variables de filtrage
- `x-model` - Lie les inputs aux variables
- `:class` - Change le style selon le filtre actif

### Section Pagination

**Structure:**
```blade
@if($recentTickets->hasPages())
    <div class="pagination">
        <!-- Affichage du nombre de résultats -->
        <div>Affichage de X à Y sur Z résultats</div>
        
        <!-- Navigation -->
        <div>
            <!-- Bouton Précédent -->
            @if($recentTickets->onFirstPage())
                <span>Précédent</span>
            @else
                <a href="{{ $recentTickets->previousPageUrl() }}">Précédent</a>
            @endif
            
            <!-- Numéros de page -->
            @foreach(range(1, $recentTickets->lastPage()) as $page)
                @if($page == $recentTickets->currentPage())
                    <span>{{ $page }}</span>
                @else
                    <a href="{{ $recentTickets->url($page) }}">{{ $page }}</a>
                @endif
            @endforeach
            
            <!-- Bouton Suivant -->
            @if($recentTickets->hasMorePages())
                <a href="{{ $recentTickets->nextPageUrl() }}">Suivant</a>
            @else
                <span>Suivant</span>
            @endif
        </div>
    </div>
@endif
```

**Logique d'affichage:**
- Affiche toujours la première et dernière page
- Affiche les 2 pages avant et après la page actuelle
- Affiche "..." pour les pages cachées

---

## 🔄 Flux d'Utilisation

### Scénario 1: Rechercher un Ticket

1. Utilisateur tape "john" dans la barre de recherche
2. Clique sur "Rechercher"
3. Le formulaire est soumis avec `?search=john`
4. Le contrôleur filtre les tickets
5. La page se recharge avec les résultats filtrés
6. La barre de recherche conserve "john"

### Scénario 2: Filtrer par Statut

1. Utilisateur clique sur "En attente"
2. Le formulaire est soumis avec `?status=pending_cash`
3. Le contrôleur filtre les tickets
4. La page se recharge avec uniquement les tickets en attente
5. Le bouton "En attente" est en bleu (actif)

### Scénario 3: Combiner Recherche et Statut

1. Utilisateur tape "john" et clique sur "En attente"
2. Le formulaire est soumis avec `?search=john&status=pending_cash`
3. Le contrôleur applique les deux filtres
4. La page affiche les tickets en attente contenant "john"

### Scénario 4: Naviguer entre les Pages

1. Utilisateur a 50 tickets filtrés
2. La page 1 affiche les tickets 1-15
3. Utilisateur clique sur "2"
4. URL devient `?search=john&status=pending_cash&page=2`
5. La page 2 affiche les tickets 16-30
6. Les filtres sont conservés

### Scénario 5: Réinitialiser les Filtres

1. Utilisateur a appliqué plusieurs filtres
2. Clique sur "Réinitialiser"
3. Redirigé vers `{{ route('admin.dashboard.view') }}`
4. Tous les tickets sont affichés
5. Les filtres sont effacés

---

## 📊 Exemples d'URL

### Sans Filtres
```
/admin/dashboard
```

### Avec Recherche
```
/admin/dashboard?search=john
```

### Avec Statut
```
/admin/dashboard?status=pending_cash
```

### Avec Recherche et Statut
```
/admin/dashboard?search=john&status=pending_cash
```

### Avec Pagination
```
/admin/dashboard?search=john&status=pending_cash&page=2
```

---

## 🎯 Configuration

### Nombre de Tickets par Page

**Fichier:** `app/Http/Controllers/Admin/DashboardController.php`

**Ligne:**
```php
->paginate(15) // Changer 15 par le nombre souhaité
```

**Exemples:**
- `paginate(10)` - 10 tickets par page
- `paginate(20)` - 20 tickets par page
- `paginate(50)` - 50 tickets par page

### Champs de Recherche

**Fichier:** `app/Http/Controllers/Admin/DashboardController.php`

**Ajouter un champ:**
```php
$q->where('reference', 'like', "%{$search}%")
    ->orWhere('full_name', 'like', "%{$search}%")
    ->orWhere('email', 'like', "%{$search}%")
    ->orWhere('phone', 'like', "%{$search}%")
    ->orWhere('nouveau_champ', 'like', "%{$search}%"); // Nouveau
```

### Ajouter un Filtre

**Backend:**
```php
// Dans DashboardController.php
if ($request->has('pay_type') && $request->pay_type !== 'all') {
    $query->where('pay_type', $request->pay_type);
}
```

**Frontend:**
```blade
<!-- Dans dashboard.blade.php -->
<button type="submit" name="pay_type" value="cash">Caisse</button>
<button type="submit" name="pay_type" value="maxicash">MaxiCash</button>
```

---

## 🧪 Tests

### Tester la Recherche

1. Ouvrir le dashboard
2. Taper un nom dans la barre de recherche
3. Cliquer sur "Rechercher"
4. Vérifier que les résultats correspondent

### Tester les Filtres

1. Cliquer sur "En attente"
2. Vérifier que seuls les tickets en attente s'affichent
3. Cliquer sur "Validés"
4. Vérifier que seuls les tickets validés s'affichent

### Tester la Pagination

1. Créer plus de 15 tickets
2. Ouvrir le dashboard
3. Vérifier que la pagination apparaît
4. Cliquer sur "2"
5. Vérifier que la page 2 s'affiche

### Tester la Combinaison

1. Taper "john" dans la recherche
2. Cliquer sur "En attente"
3. Cliquer sur "Rechercher"
4. Vérifier que les résultats sont filtrés
5. Cliquer sur "2" (pagination)
6. Vérifier que les filtres sont conservés

---

## 🐛 Dépannage

### Les Filtres ne Fonctionnent Pas

**Vérifier:**
1. Le formulaire a `method="GET"`
2. L'action pointe vers `{{ route('admin.dashboard.view') }}`
3. Les inputs ont les bons attributs `name`
4. Le contrôleur reçoit bien les paramètres

### La Pagination ne Conserve pas les Filtres

**Solution:**
```php
// Ajouter .appends() dans le contrôleur
->paginate(15)->appends($request->query());
```

### Les Boutons de Statut ne Changent pas de Couleur

**Vérifier:**
1. Alpine.js est chargé
2. La variable `statusFilter` est initialisée
3. La condition `:class` est correcte

---

## 📝 Fichiers Modifiés

### Backend
- `app/Http/Controllers/Admin/DashboardController.php`
  - Méthode `view()` modifiée
  - Ajout des filtres et pagination

### Frontend
- `resources/views/admin/dashboard.blade.php`
  - Cartes Événements et Utilisateurs cachées
  - Section filtres ajoutée
  - Pagination ajoutée
  - Compteur de résultats ajouté

---

## ✅ Checklist de Déploiement

- [x] Modifier le contrôleur backend
- [x] Ajouter les filtres dans la vue
- [x] Ajouter la pagination
- [x] Cacher les cartes non nécessaires
- [x] Tester la recherche
- [x] Tester les filtres
- [x] Tester la pagination
- [x] Tester la combinaison
- [ ] Déployer en production
- [ ] Former les administrateurs

---

**Date de création:** 2024
**Dernière mise à jour:** 2024
