# Dashboard Admin - Billets Validés et Impression

## 🎯 Modifications Effectuées

L'onglet "Tickets" a été modifié pour:

1. **Afficher uniquement les billets validés par défaut**
2. **Ajouter un bouton d'impression de la liste**

---

## 📋 Affichage par Défaut

### Avant
- Affichait tous les tickets (tous statuts confondus)
- Filtre par défaut: "Tous"

### Après
- Affiche uniquement les billets validés (`payment_status = 'completed'`)
- Filtre par défaut: "Validés"
- Possibilité de voir les autres statuts en cliquant sur les filtres

---

## 🔧 Backend (DashboardController.php)

### Modification de la Requête

**Avant:**
```php
$allTicketsQuery = Ticket::with(['event', 'price']);

if ($request->has('tickets_status') && $request->tickets_status !== 'all') {
    $allTicketsQuery->where('payment_status', $request->tickets_status);
}
```

**Après:**
```php
$allTicketsQuery = Ticket::with(['event', 'price']);

// Filtre par défaut: tickets validés uniquement
$ticketsStatus = $request->get('tickets_status', 'completed');
if ($ticketsStatus !== 'all') {
    $allTicketsQuery->where('payment_status', $ticketsStatus);
}
```

**Explication:**
- `$request->get('tickets_status', 'completed')` - Si aucun filtre n'est spécifié, utilise 'completed' par défaut
- L'utilisateur peut toujours voir tous les tickets en cliquant sur "Tous"

---

## 🎨 Frontend (Blade)

### Ordre des Boutons de Filtre

**Avant:**
```blade
<button name="tickets_status" value="all">Tous</button>
<button name="tickets_status" value="pending_cash">En attente</button>
<button name="tickets_status" value="completed">Validés</button>
<button name="tickets_status" value="failed">Échoués</button>
```

**Après:**
```blade
<button name="tickets_status" value="completed">Validés</button>
<button name="tickets_status" value="all">Tous</button>
<button name="tickets_status" value="pending_cash">En attente</button>
<button name="tickets_status" value="failed">Échoués</button>
```

**Raison:** Le bouton "Validés" est maintenant en premier car c'est le filtre par défaut.

### Titre du Tableau

**Avant:**
```blade
<h3>Tous les tickets</h3>
<span>{{ $allTickets->total() }} ticket(s) au total</span>
```

**Après:**
```blade
<h3>Billets Validés</h3>
<span>{{ $allTickets->total() }} billet(s) validé(s)</span>
```

### Bouton d'Impression

**Ajout:**
```blade
<button onclick="printTicketsList()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
    <svg>...</svg>
    Imprimer la liste
</button>
```

**Position:** En haut à droite du tableau, à côté du titre

---

## 🖨️ Fonctionnalité d'Impression

### Comment ça Marche

1. **Utilisateur clique sur "Imprimer la liste"**
2. **La fonction JavaScript `printTicketsList()` est appelée**
3. **`window.print()` ouvre la boîte de dialogue d'impression**
4. **Les styles CSS `@media print` sont appliqués**
5. **Seul le tableau des tickets est visible à l'impression**

### Styles d'Impression

```css
@media print {
    /* Cacher tout sauf le tableau */
    body * {
        visibility: hidden;
    }
    #tickets-table-container, #tickets-table-container * {
        visibility: visible;
    }
    
    /* Positionner le tableau */
    #tickets-table-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    /* Cacher les éléments non nécessaires */
    button, .pagination, .border-t {
        display: none !important;
    }
    
    /* Styles du tableau pour l'impression */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f3f4f6;
        font-weight: bold;
    }
}
```

### Ce qui est Imprimé

**Visible:**
- Titre du tableau ("Billets Validés")
- Nombre total de billets
- Tableau complet avec toutes les colonnes
- Toutes les lignes de la page actuelle

**Caché:**
- Sidebar
- Header
- Filtres
- Boutons d'action
- Pagination
- Bouton "Imprimer"

---

## 📊 Cas d'Usage

### Scénario 1: Imprimer la Liste des Billets Validés

1. Administrateur ouvre l'onglet "Tickets"
2. Par défaut, seuls les billets validés s'affichent
3. Clique sur "Imprimer la liste"
4. La boîte de dialogue d'impression s'ouvre
5. Sélectionne l'imprimante ou "Enregistrer en PDF"
6. Imprime ou enregistre la liste

### Scénario 2: Imprimer une Liste Filtrée

1. Administrateur ouvre l'onglet "Tickets"
2. Tape "john" dans la recherche
3. Clique sur "MaxiCash" pour filtrer par mode de paiement
4. Clique sur "Rechercher"
5. Seuls les billets validés de John payés via MaxiCash s'affichent
6. Clique sur "Imprimer la liste"
7. Imprime la liste filtrée

### Scénario 3: Voir Tous les Tickets

1. Administrateur ouvre l'onglet "Tickets"
2. Clique sur "Tous" dans les filtres de statut
3. Tous les tickets (validés, en attente, échoués) s'affichent
4. Peut imprimer cette liste complète si nécessaire

---

## 🎯 Avantages

### Pour l'Administrateur

1. **Gain de temps** - Pas besoin de filtrer manuellement à chaque fois
2. **Vue claire** - Focus sur les billets validés (les plus importants)
3. **Impression facile** - Un seul clic pour imprimer
4. **Flexibilité** - Peut toujours voir les autres statuts si nécessaire

### Pour l'Organisation

1. **Traçabilité** - Liste imprimée des participants confirmés
2. **Contrôle** - Vérification rapide des paiements validés
3. **Archivage** - Possibilité d'enregistrer en PDF pour les archives
4. **Reporting** - Liste prête pour les rapports

---

## 🔄 Flux d'Utilisation

### Flux Normal

```
┌─────────────────┐
│ Ouvrir onglet   │
│ "Tickets"       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Affichage auto  │
│ billets validés │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Clic "Imprimer  │
│ la liste"       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Boîte dialogue  │
│ d'impression    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Imprimer ou     │
│ Enregistrer PDF │
└─────────────────┘
```

### Flux avec Filtres

```
┌─────────────────┐
│ Ouvrir onglet   │
│ "Tickets"       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Appliquer       │
│ filtres         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Résultats       │
│ filtrés         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Clic "Imprimer  │
│ la liste"       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Impression de   │
│ la liste filtrée│
└─────────────────┘
```

---

## 📝 Exemples d'URL

### Billets Validés (Défaut)
```
/admin/dashboard?tab=tickets
/admin/dashboard?tab=tickets&tickets_status=completed
```

### Tous les Billets
```
/admin/dashboard?tab=tickets&tickets_status=all
```

### Billets en Attente
```
/admin/dashboard?tab=tickets&tickets_status=pending_cash
```

### Billets Validés + Recherche
```
/admin/dashboard?tab=tickets&tickets_search=john
```

### Billets Validés + Mode de Paiement
```
/admin/dashboard?tab=tickets&tickets_pay_type=maxicash
```

---

## 🎨 Personnalisation

### Changer le Filtre par Défaut

**Fichier:** `app/Http/Controllers/Admin/DashboardController.php`

**Ligne:**
```php
$ticketsStatus = $request->get('tickets_status', 'completed');
```

**Options:**
- `'completed'` - Billets validés (actuel)
- `'pending_cash'` - Billets en attente
- `'all'` - Tous les billets
- `'failed'` - Billets échoués

### Personnaliser les Styles d'Impression

**Fichier:** `resources/views/admin/dashboard.blade.php`

**Section:** `@media print { ... }`

**Exemples:**

**Ajouter un en-tête:**
```css
@media print {
    #tickets-table-container::before {
        content: "Liste des Billets Validés - NLC";
        display: block;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
    }
}
```

**Ajouter la date:**
```css
@media print {
    #tickets-table-container::after {
        content: "Imprimé le " attr(data-date);
        display: block;
        margin-top: 20px;
        text-align: right;
        font-size: 12px;
    }
}
```

**Changer les couleurs:**
```css
@media print {
    th {
        background-color: #e5e7eb !important;
        color: #000 !important;
    }
}
```

---

## 🧪 Tests

### Tester l'Affichage par Défaut

1. Ouvrir `/admin/dashboard?tab=tickets`
2. Vérifier que seuls les billets validés s'affichent
3. Vérifier que le bouton "Validés" est en bleu (actif)
4. Vérifier le titre "Billets Validés"

### Tester les Filtres

1. Cliquer sur "Tous"
2. Vérifier que tous les tickets s'affichent
3. Cliquer sur "En attente"
4. Vérifier que seuls les tickets en attente s'affichent
5. Revenir sur "Validés"
6. Vérifier que seuls les billets validés s'affichent

### Tester l'Impression

1. Ouvrir l'onglet "Tickets"
2. Cliquer sur "Imprimer la liste"
3. Vérifier que la boîte de dialogue d'impression s'ouvre
4. Vérifier l'aperçu avant impression:
   - Seul le tableau est visible
   - Pas de sidebar, header, filtres
   - Pas de boutons ni pagination
   - Tableau bien formaté avec bordures
5. Annuler ou imprimer

### Tester l'Impression avec Filtres

1. Appliquer des filtres (recherche + mode de paiement)
2. Cliquer sur "Imprimer la liste"
3. Vérifier que seuls les résultats filtrés sont dans l'aperçu
4. Vérifier le nombre de billets dans le titre

---

## 🐛 Dépannage

### Le Bouton d'Impression ne Fonctionne Pas

**Vérifier:**
1. La fonction JavaScript `printTicketsList()` est définie
2. Le bouton a l'attribut `onclick="printTicketsList()"`
3. Pas d'erreurs dans la console du navigateur

**Solution:**
```javascript
function printTicketsList() {
    window.print();
}
```

### L'Impression Affiche Toute la Page

**Vérifier:**
1. Les styles `@media print` sont présents
2. L'ID `tickets-table-container` est sur le bon élément
3. Les styles ne sont pas écrasés par d'autres CSS

**Solution:**
```css
@media print {
    body * {
        visibility: hidden !important;
    }
    #tickets-table-container, #tickets-table-container * {
        visibility: visible !important;
    }
}
```

### Les Billets Validés ne S'Affichent Pas par Défaut

**Vérifier:**
1. Le contrôleur utilise `$request->get('tickets_status', 'completed')`
2. La vue initialise `statusFilter: '{{ request('tickets_status') ?? 'completed' }}'`
3. Le bouton "Validés" a `value="completed"`

---

## 📋 Checklist de Déploiement

- [x] Modifier le contrôleur (filtre par défaut)
- [x] Modifier la vue (ordre des boutons)
- [x] Ajouter le bouton d'impression
- [x] Ajouter les styles d'impression
- [x] Ajouter la fonction JavaScript
- [ ] Tester l'affichage par défaut
- [ ] Tester tous les filtres
- [ ] Tester l'impression
- [ ] Tester l'impression avec filtres
- [ ] Déployer en production
- [ ] Former les administrateurs

---

**Date de création:** 2024
**Dernière mise à jour:** 2024
