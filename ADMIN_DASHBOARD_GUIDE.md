# Guide d'utilisation du Dashboard Admin

## Accès au Dashboard

Le dashboard admin est accessible à l'adresse `/admin` de votre application frontend.

### Prérequis
- Avoir un compte utilisateur avec le rôle "admin"
- Être connecté avec un token d'authentification valide

## Fonctionnalités

### 1. Dashboard (Vue d'ensemble)

Le dashboard principal affiche :

#### Statistiques en temps réel
- **Total Tickets** : Nombre total de tickets créés
- **Tickets Validés** : Nombre de paiements confirmés
- **Tickets En Attente** : Nombre de tickets à valider
- **Revenus Totaux** : Montant total encaissé

#### Tickets Récents
- Liste des 10 derniers tickets créés
- Informations : référence, participant, événement, montant, statut
- Action rapide : bouton "Valider" pour les tickets en attente

### 2. Gestion des Tickets

Onglet dédié à la gestion complète des tickets en attente de validation.

#### Fonctionnalités
- **Recherche** : Par référence, nom ou email du participant
- **Filtres** :
  - Tous les tickets
  - En attente uniquement
  - Validés uniquement
- **Actions** :
  - Valider un ticket en attente (bouton vert)
  - Voir toutes les informations du participant

#### Informations affichées
- Référence du ticket
- Nom du participant
- Email et téléphone
- Événement associé
- Montant et devise
- Statut du paiement

### 3. Statistiques des Événements

Vue d'ensemble de la performance de vos événements.

#### Informations par événement
- Titre de l'événement
- Date et lieu
- Nombre de tickets vendus
- Revenus générés

### 4. Gestion des Utilisateurs

Liste de tous les utilisateurs de la plateforme.

#### Informations affichées
- Nom de l'utilisateur
- Email
- Rôle (admin, validateur, etc.)
- Date d'inscription

## Navigation

### Sidebar
- **Dashboard** : Vue d'ensemble
- **Tickets** : Gestion des tickets
- **Événements** : Statistiques des événements
- **Utilisateurs** : Liste des utilisateurs
- **Déconnexion** : Se déconnecter du dashboard

### Responsive
- Le sidebar peut être réduit en cliquant sur l'icône menu
- Version mobile adaptée

## API Endpoints utilisés

### Backend Laravel (Routes Web)
```
GET  /admin/dashboard              - Statistiques générales
GET  /admin/tickets/pending        - Liste des tickets en attente
POST /admin/tickets/{ref}/validate - Valider un ticket
GET  /admin/events/stats           - Statistiques des événements
GET  /admin/users                  - Liste des utilisateurs
```

### Middleware
Toutes les routes admin sont protégées par :
- `auth:sanctum` : Authentification requise
- `admin.only` : Rôle admin requis

### Note importante
Les routes admin sont dans `routes/web.php` et non dans `routes/api.php`. Elles sont accessibles directement via `/admin/*` sans le préfixe `/api`.

## Validation de Tickets

### Processus
1. Aller dans l'onglet "Tickets"
2. Rechercher le ticket par référence ou nom
3. Vérifier les informations du participant
4. Cliquer sur "Valider" pour confirmer le paiement
5. Le statut passe de "En attente" à "Validé"

### Statuts des tickets
- 🟠 **En attente** (pending_cash) : Paiement non confirmé
- 🟢 **Validé** (completed) : Paiement confirmé
- 🔴 **Échoué** (failed) : Paiement échoué

## Sécurité

### Authentification
- Token JWT stocké dans localStorage
- Redirection automatique vers /login si non authentifié
- Vérification du rôle admin côté backend

### Protection des routes
- Middleware `admin.only` sur toutes les routes admin
- Vérification du token à chaque requête API

## Configuration Frontend

### Variables d'environnement
```env
VITE_API_URL=http://localhost:8000/api
```

### Route React Router
Ajouter dans votre fichier de routes :
```tsx
import AdminDashboard from "@/pages/AdminDashboard";

// Dans vos routes
<Route path="/admin" element={<AdminDashboard />} />
```

## Dépannage

### Erreur 401 (Non autorisé)
- Vérifier que vous êtes connecté
- Vérifier que votre token est valide
- Vérifier que vous avez le rôle admin

### Données ne s'affichent pas
- Vérifier la connexion à l'API backend
- Ouvrir la console du navigateur pour voir les erreurs
- Vérifier que l'URL de l'API est correcte

### Bouton "Valider" ne fonctionne pas
- Vérifier que le ticket est bien en statut "pending_cash"
- Vérifier les permissions admin
- Consulter les logs backend

## Améliorations futures

- Graphiques d'évolution des ventes
- Export des données en CSV/PDF
- Notifications en temps réel
- Gestion des rôles utilisateurs
- Historique des validations
- Statistiques avancées par période
