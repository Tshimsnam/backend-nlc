# Dashboard Admin - Pagination et Filtres Avancés

## 🎯 Fonctionnalités Ajoutées

Le dashboard administrateur dispose maintenant d'un système complet de pagination et de filtres pour gérer efficacement les tickets.

## 📊 Pagination

### Backend (DashboardController.php)

La méthode `pendingTickets()` supporte maintenant:

- **Pagination automatique** avec Laravel
- **Paramètres personnalisables**:
  - `page` - Numéro de la page (défaut: 1)
  - `per_page` - Nombre d'éléments par page (défaut: 20)

**Exemple de requête:**
```http
GET /api/admin/dashboard/pending-tickets?page=2&per_page=50
Authorization: Bearer {token}
```

**Réponse:**
```json
{
  "current_page": 2,
  "data": [...],
  "first_page_url": "...",
  "from": 21,
  "last_page": 5,
  "last_page_url": "...",
  "next_page_url": "...",
  "path": "...",
  "per_page": 20,
  "prev_page_url": "...",
  "to": 40,
  "total": 100
}
```

### Frontend (AdminDashboard.tsx)

- **Navigation par pages** avec boutons Précédent/Suivant
- **Affichage des numéros de page** (max 5 pages visibles)
- **Indicateur de résultats** ("Affichage de X à Y sur Z résultats")
- **Boutons désactivés** quand on est à la première/dernière page

## 🔍 Filtres Disponibles

### 1. Recherche Textuelle

**Paramètre:** `search`

Recherche dans:
- Référence du ticket
- Nom complet du participant
- Email
- Numéro de téléphone

**Exemple:**
```http
GET /api/admin/dashboard/pending-tickets?search=john
```

### 2. Filtre par Statut de Paiement

**Paramètre:** `status`

Valeurs possibles:
- `all` - Tous les statuts (défaut)
- `pending_cash` - En attente de paiement
- `completed` - Paiement validé
- `failed` - Paiement échoué
- `pending` - En attente (paiement en ligne)

**Exemple:**
```http
GET /api/admin/dashboard/pending-tickets?status=pending_cash
```

### 3. Filtre par Mode de Paiement

**Paramètre:** `pay_type`

Valeurs possibles:
- `all` - Tous les modes (défaut)
- `cash` - Paiement en caisse
- `maxicash` - MaxiCash
- `mpesa` - M-Pesa
- `orange_money` - Orange Money

**Exemple:**
```http
GET /api/admin/dashboard/pending-tickets?pay_type=cash
```

### 4. Filtre par Événement

**Paramètre:** `event_id`

Filtre les tickets d'un événement spécifique.

**Exemple:**
```http
GET /api/admin/dashboard/pending-tickets?event_id=5
```

### 5. Filtre par Date

**Paramètres:** `date_from` et `date_to`

Filtre les tickets créés dans une période donnée.

**Exemple:**
```http
GET /api/admin/dashboard/pending-tickets?date_from=2024-01-01&date_to=2024-12-31
```

### 6. Tri des Résultats

**Paramètres:** `sort_by` et `sort_order`

- `sort_by` - Colonne de tri (défaut: `created_at`)
  - `created_at` - Date de création
  - `reference` - Référence
  - `full_name` - Nom
  - `amount` - Montant
  - `payment_status` - Statut

- `sort_order` - Ordre de tri (défaut: `desc`)
  - `asc` - Croissant
  - `desc` - Décroissant

**Exemple:**
```http
GET /api/admin/dashboard/pending-tickets?sort_by=amount&sort_order=desc
```

## 🎨 Interface Utilisateur

### Section Filtres

L'interface propose:

1. **Barre de recherche** avec icône de loupe
2. **Boutons de filtre par statut** (Tous, En attente, Validés, Échoués)
3. **Boutons de filtre par mode de paiement** (Tous, Caisse, MaxiCash, M-Pesa, Orange Money)
4. **Menu déroulant pour sélectionner un événement**
5. **Bouton "Réinitialiser les filtres"** pour tout effacer

### Tableau des Tickets

Colonnes affichées:
- Référence (format monospace)
- Participant (nom)
- Contact (email + téléphone)
- Événement
- Mode de paiement (badge)
- Montant
- Statut (badge coloré)
- Actions (bouton Valider si en attente)

### Pagination

En bas du tableau:
- Texte: "Affichage de X à Y sur Z résultats"
- Bouton "Précédent" (désactivé si page 1)
- Numéros de page (5 max, centrés sur la page actuelle)
- Bouton "Suivant" (désactivé si dernière page)

## 🔄 Combinaison de Filtres

Tous les filtres peuvent être combinés:

```http
GET /api/admin/dashboard/pending-tickets?
  search=john&
  status=pending_cash&
  pay_type=cash&
  event_id=5&
  page=2&
  per_page=50&
  sort_by=created_at&
  sort_order=desc
```

## 💡 Comportement

### Réinitialisation Automatique

Quand un filtre est modifié, la pagination revient automatiquement à la page 1.

### Rechargement Automatique

Les données sont rechargées automatiquement quand:
- On change de page
- On modifie un filtre
- On effectue une recherche
- On valide un ticket

### Debouncing

La recherche textuelle utilise un debouncing pour éviter trop de requêtes:
- Attente de 300ms après la dernière frappe
- Puis envoi de la requête

## 📱 Responsive

L'interface s'adapte aux différentes tailles d'écran:
- **Desktop**: Tous les filtres sur une ligne
- **Tablet**: Filtres sur 2 lignes
- **Mobile**: Filtres empilés verticalement

## 🔐 Sécurité

- Toutes les routes nécessitent une authentification (`auth:sanctum`)
- Les routes admin nécessitent le middleware `admin.only`
- Validation des paramètres côté backend
- Protection contre les injections SQL (Eloquent)

## 📊 Performance

### Optimisations Backend

- Utilisation de `with()` pour eager loading des relations
- Index sur les colonnes de recherche
- Pagination pour limiter les résultats
- Cache des statistiques (optionnel)

### Optimisations Frontend

- Debouncing de la recherche
- Mémorisation des filtres dans l'état
- Rechargement uniquement des données nécessaires
- Affichage conditionnel de la pagination

## 🧪 Tests

### Tester la Pagination

```bash
# Page 1 (20 résultats)
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/admin/dashboard/pending-tickets"

# Page 2 (20 résultats)
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/admin/dashboard/pending-tickets?page=2"

# 50 résultats par page
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/admin/dashboard/pending-tickets?per_page=50"
```

### Tester les Filtres

```bash
# Recherche
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/admin/dashboard/pending-tickets?search=john"

# Statut
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/admin/dashboard/pending-tickets?status=pending_cash"

# Mode de paiement
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/admin/dashboard/pending-tickets?pay_type=cash"

# Combinaison
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/admin/dashboard/pending-tickets?search=john&status=pending_cash&pay_type=cash"
```

## 📝 Code Modifié

### Backend

**Fichier:** `app/Http/Controllers/Admin/DashboardController.php`

**Méthode:** `pendingTickets(Request $request)`

**Changements:**
- Ajout du paramètre `Request $request`
- Ajout des filtres (search, status, pay_type, event_id, dates)
- Ajout du tri personnalisable
- Pagination avec paramètres personnalisables

### Frontend

**Fichier:** `AdminDashboard.tsx`

**Changements:**
- Ajout des états pour la pagination et les filtres
- Modification de `fetchPendingTickets()` pour envoyer les paramètres
- Ajout de l'interface `PaginationMeta`
- Ajout des composants de filtres avancés
- Ajout du composant de pagination
- Ajout de la fonction `handleResetFilters()`
- Ajout de la fonction `handlePageChange()`

## 🎯 Cas d'Usage

### Scénario 1: Rechercher un Ticket Spécifique

1. Taper la référence ou le nom dans la barre de recherche
2. Les résultats s'affichent automatiquement
3. Cliquer sur "Valider" si nécessaire

### Scénario 2: Voir Tous les Tickets en Attente

1. Cliquer sur le bouton "En attente" dans les filtres de statut
2. Seuls les tickets `pending_cash` s'affichent
3. Valider les tickets un par un

### Scénario 3: Voir les Tickets d'un Événement

1. Sélectionner l'événement dans le menu déroulant
2. Les tickets de cet événement s'affichent
3. Naviguer entre les pages si nécessaire

### Scénario 4: Voir les Paiements en Caisse

1. Cliquer sur "Caisse" dans les filtres de mode de paiement
2. Seuls les tickets payés en caisse s'affichent
3. Combiner avec le filtre "En attente" pour voir ceux à valider

## 🔮 Améliorations Futures

### Possibles Ajouts

1. **Export CSV/Excel** des résultats filtrés
2. **Graphiques** de statistiques basés sur les filtres
3. **Sauvegarde des filtres** dans localStorage
4. **Filtres avancés** (montant min/max, catégorie)
5. **Actions en masse** (valider plusieurs tickets)
6. **Notifications** en temps réel
7. **Historique des modifications**
8. **Commentaires** sur les tickets

## ✅ Checklist de Déploiement

- [x] Modifier le contrôleur backend
- [x] Ajouter les filtres et la pagination
- [x] Mettre à jour le frontend
- [x] Ajouter les composants UI
- [x] Tester la pagination
- [x] Tester les filtres
- [x] Tester les combinaisons
- [ ] Déployer en production
- [ ] Former les administrateurs
- [ ] Surveiller les performances

## 📚 Documentation Associée

- `ADMIN_DASHBOARD_SETUP.md` - Configuration initiale du dashboard
- `VALIDATION_TICKET_TOUS_UTILISATEURS.md` - Système de validation des tickets
- `API_DOCUMENTATION.md` - Documentation complète de l'API

---

**Date de création:** 2024
**Dernière mise à jour:** 2024
**Auteur:** Équipe de développement
