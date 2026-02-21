# 📊 Fonctionnalité: Page de Détails des Agents

## 🎯 Vue d'Ensemble

Nouvelle fonctionnalité permettant de voir les statistiques détaillées de chaque agent, avec une séparation claire entre les billets physiques et les billets en ligne validés.

**Date**: 21 Février 2026  
**Status**: ✅ Implémenté

---

## 🚀 Fonctionnalités

### 1. Bouton "Voir Détails" dans le Tableau des Agents
- Ajouté dans la colonne "Actions"
- Icône de graphique
- Lien vers la page de détails de l'agent

### 2. Page de Détails Complète
Une page dédiée affichant:
- Informations de l'agent
- Statistiques globales
- Statistiques par type de billet (physique vs en ligne)
- Graphique d'évolution (30 derniers jours)
- Validations par événement
- Dernières validations effectuées

---

## 📊 Statistiques Affichées

### Statistiques Globales (3 cartes)

#### 1. Total Validations
- Nombre total de billets validés par l'agent
- Icône: Check circle (bleu)

#### 2. Revenus Générés
- Total des revenus générés par les validations
- Icône: Dollar (vert)
- Format: XXX,XXX $

#### 3. Performance
- Revenu moyen par validation
- Icône: Graphique (purple)
- Calcul: Total revenus / Total validations

### Statistiques par Type (2 grandes cartes)

#### Carte Purple - Billets Physiques
```
╔═══════════════════════════════════════╗
║  🔲 QR Physique                       ║
║  ┌─────────────────────────────────┐ ║
║  │ Total validés:        XXX       │ ║
║  │ Revenus générés:  XXX,XXX $     │ ║
║  │ XX.X% du total des validations  │ ║
║  └─────────────────────────────────┘ ║
╚═══════════════════════════════════════╝
```

#### Carte Blue - Billets En Ligne
```
╔═══════════════════════════════════════╗
║  💻 Site Web                          ║
║  ┌─────────────────────────────────┐ ║
║  │ Total validés:        XXX       │ ║
║  │ Revenus générés:  XXX,XXX $     │ ║
║  │ XX.X% du total des validations  │ ║
║  └─────────────────────────────────┘ ║
╚═══════════════════════════════════════╝
```

---

## 📈 Graphique d'Évolution

### Caractéristiques
- Type: Graphique en ligne (Chart.js)
- Période: 30 derniers jours
- 3 courbes:
  - **Purple**: Billets physiques
  - **Blue**: Billets en ligne
  - **Green** (pointillés): Total

### Données Affichées
- Axe X: Dates (format JJ/MM)
- Axe Y: Nombre de validations
- Tooltip: Détails au survol

---

## 📋 Tableaux de Données

### 1. Validations par Événement

| Colonne | Description |
|---------|-------------|
| Événement | Titre de l'événement |
| Total | Nombre total de validations |
| Physiques | Badge purple avec nombre |
| En Ligne | Badge blue avec nombre |
| Revenus | Montant en vert |

### 2. Dernières Validations (20 dernières)

| Colonne | Description |
|---------|-------------|
| Référence | Code du billet (font mono) |
| Type | Badge purple (physique) ou blue (en ligne) |
| Participant | Nom du participant |
| Événement | Titre de l'événement |
| Montant | Montant + devise |
| Date | Date et heure de validation |

---

## 🔧 Implémentation Technique

### 1. Migration
**Fichier**: `database/migrations/2026_02_21_180743_add_validated_by_to_tickets_table.php`

Ajoute le champ `validated_by` à la table `tickets`:
```php
$table->foreignId('validated_by')
    ->nullable()
    ->after('payment_status')
    ->constrained('users')
    ->onDelete('set null');
```

### 2. Modèle Ticket
**Fichier**: `app/Models/Ticket.php`

Ajouts:
- Champ `validated_by` dans `$fillable`
- Relation `validator()` vers User

```php
public function validator(): BelongsTo
{
    return $this->belongsTo(User::class, 'validated_by');
}
```

### 3. Contrôleur
**Fichier**: `app/Http/Controllers/Admin/DashboardController.php`

#### Méthode `agentDetails($id)`
Récupère et calcule:
- Statistiques globales de l'agent
- Statistiques par type (physique/en ligne)
- Évolution des validations (30 jours)
- Validations par événement
- Dernières validations

#### Méthode `validateTicketWeb($reference)` (mise à jour)
Enregistre maintenant l'ID de l'agent qui valide:
```php
$ticket->update([
    'payment_status' => 'completed',
    'validated_by' => $user->id
]);
```

### 4. Route
**Fichier**: `routes/web.php`

```php
Route::get('/admin/agents/{id}/details', [DashboardController::class, 'agentDetails'])
    ->name('admin.agents.details');
```

### 5. Vue
**Fichier**: `resources/views/admin/agent-details.blade.php`

Page complète avec:
- Header avec bouton retour
- Informations de l'agent
- 3 cartes de statistiques globales
- 2 cartes de statistiques par type
- Graphique Chart.js
- 2 tableaux de données

### 6. Dashboard (mise à jour)
**Fichier**: `resources/views/admin/dashboard.blade.php`

Modifications:
- Ajout colonne "Actions" dans le tableau des agents
- Bouton "Voir Détails" avec icône
- Colspan mis à jour (6 → 7)

---

## 🎨 Design

### Couleurs
- **Purple** (#8B5CF6) - Billets physiques
- **Blue** (#3B82F6) - Billets en ligne
- **Green** (#10B981) - Revenus et total
- **Gray** - Textes et bordures

### Icônes
- 🔲 QR Code - Billets physiques
- 💻 Ordinateur - Billets en ligne
- ✓ Check - Validations
- 💰 Dollar - Revenus
- 📊 Graphique - Performance

### Layout
- Design responsive
- Cartes avec ombres et bordures
- Dégradés pour les cartes de type
- Graphique pleine largeur
- Tableaux avec hover

---

## 📊 Requêtes SQL

### Statistiques Globales
```sql
-- Total validations
SELECT COUNT(*) FROM tickets WHERE validated_by = ?

-- Billets physiques
SELECT COUNT(*) FROM tickets 
WHERE validated_by = ? AND physical_qr_id IS NOT NULL

-- Billets en ligne
SELECT COUNT(*) FROM tickets 
WHERE validated_by = ? AND physical_qr_id IS NULL

-- Revenus
SELECT SUM(amount) FROM tickets 
WHERE validated_by = ? AND payment_status = 'completed'
```

### Évolution (30 jours)
```sql
SELECT 
    DATE(updated_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical,
    SUM(CASE WHEN physical_qr_id IS NULL THEN 1 ELSE 0 END) as online
FROM tickets
WHERE validated_by = ? AND updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(updated_at)
ORDER BY date
```

### Validations par Événement
```sql
SELECT 
    events.id,
    events.title,
    COUNT(*) as total,
    SUM(CASE WHEN tickets.physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical,
    SUM(CASE WHEN tickets.physical_qr_id IS NULL THEN 1 ELSE 0 END) as online,
    SUM(CASE WHEN tickets.payment_status = 'completed' THEN tickets.amount ELSE 0 END) as revenue
FROM tickets
JOIN events ON tickets.event_id = events.id
WHERE tickets.validated_by = ?
GROUP BY events.id, events.title
ORDER BY total DESC
```

---

## 🚀 Utilisation

### Accéder à la Page de Détails

1. **Depuis le Dashboard**:
   - Aller dans l'onglet "Agents Mobile"
   - Cliquer sur "Voir Détails" pour un agent

2. **URL Directe**:
   ```
   /admin/agents/{id}/details
   ```

### Navigation
- **Bouton Retour**: Retour à la liste des agents
- **Déconnexion**: En haut à droite

---

## 📝 Données Affichées

### Informations Agent
- Avatar avec initiales
- Nom complet
- Email
- Rôle (badge)
- Statut de vérification
- Date d'inscription

### Métriques Calculées
- Total validations
- Revenus totaux
- Revenu moyen par validation
- Pourcentage physique/en ligne
- Évolution sur 30 jours

---

## 🔐 Sécurité

### Restrictions
- Impossible d'afficher les détails d'un Parent ou Administrateur
- Redirection avec message d'erreur si tentative

### Vérifications
```php
$hasRestrictedRole = $agent->roles()
    ->whereIn('name', ['Parent', 'Administrateur'])
    ->exists();
    
if ($hasRestrictedRole) {
    return redirect()->route('admin.dashboard.view', ['tab' => 'agents'])
        ->with('error', 'Impossible d\'afficher les détails de cet utilisateur.');
}
```

---

## 🧪 Tests

### Test Manuel

1. **Créer un Agent**:
   ```bash
   # Depuis le dashboard, onglet Agents
   # Cliquer sur "Créer un Agent"
   ```

2. **Valider des Billets**:
   ```bash
   # Valider quelques billets physiques et en ligne
   # Le champ validated_by sera automatiquement rempli
   ```

3. **Voir les Détails**:
   ```bash
   # Cliquer sur "Voir Détails" pour l'agent
   # Vérifier que les statistiques s'affichent correctement
   ```

### Vérification Base de Données
```sql
-- Vérifier le champ validated_by
SELECT id, reference, payment_status, validated_by 
FROM tickets 
WHERE validated_by IS NOT NULL;

-- Statistiques d'un agent
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical,
    SUM(CASE WHEN physical_qr_id IS NULL THEN 1 ELSE 0 END) as online
FROM tickets
WHERE validated_by = 1;
```

---

## 📦 Fichiers Modifiés/Créés

### Créés (3 fichiers)
1. `database/migrations/2026_02_21_180743_add_validated_by_to_tickets_table.php`
2. `resources/views/admin/agent-details.blade.php`
3. `AGENT_DETAILS_FEATURE.md` (ce fichier)

### Modifiés (4 fichiers)
1. `app/Models/Ticket.php` - Ajout champ et relation
2. `app/Http/Controllers/Admin/DashboardController.php` - Nouvelle méthode + mise à jour validation
3. `routes/web.php` - Nouvelle route
4. `resources/views/admin/dashboard.blade.php` - Bouton "Voir Détails"

---

## 🎯 Prochaines Étapes

### Installation
```bash
# 1. Exécuter la migration
php artisan migrate

# 2. Vérifier que la colonne existe
php artisan tinker
Schema::hasColumn('tickets', 'validated_by');

# 3. Accéder au dashboard
# http://localhost:8000/admin
```

### Utilisation
1. Aller dans l'onglet "Agents Mobile"
2. Cliquer sur "Voir Détails" pour un agent
3. Explorer les statistiques et graphiques

---

## 💡 Améliorations Futures Possibles

1. **Filtres**:
   - Filtrer par période
   - Filtrer par événement
   - Filtrer par type de billet

2. **Exports**:
   - Export CSV des validations
   - Export PDF du rapport
   - Graphiques téléchargeables

3. **Comparaisons**:
   - Comparer plusieurs agents
   - Classement des agents
   - Objectifs et performances

4. **Notifications**:
   - Alertes de performance
   - Rapports automatiques
   - Badges de réussite

---

## 📞 Support

### En Cas de Problème

1. **La colonne validated_by n'existe pas**:
   ```bash
   php artisan migrate
   ```

2. **Erreur 404 sur la page de détails**:
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

3. **Le graphique ne s'affiche pas**:
   - Vérifier que Chart.js est chargé
   - Vérifier la console du navigateur

4. **Aucune donnée affichée**:
   - Valider quelques billets d'abord
   - Vérifier que `validated_by` est rempli

---

## ✅ Checklist de Vérification

- [x] Migration créée et exécutée
- [x] Modèle Ticket mis à jour
- [x] Contrôleur avec nouvelle méthode
- [x] Route ajoutée
- [x] Vue créée
- [x] Dashboard mis à jour
- [x] Validation enregistre l'agent
- [x] Design responsive
- [x] Graphique fonctionnel
- [x] Statistiques séparées physique/en ligne

---

**Status**: ✅ Fonctionnalité Complète et Opérationnelle  
**Date**: 21 Février 2026  
**Version**: 1.0.0
