# Dashboard Admin - Référence Rapide 🚀

## 🔗 Accès

**URL:** `/admin`

**Identifiants par défaut:**
- Email: `admin@nlc.com`
- Mot de passe: `Admin@123`

## 📊 Onglets Disponibles

### 1️⃣ Dashboard
Vue d'ensemble avec 4 statistiques principales et tickets récents

### 2️⃣ Tickets
Gestion complète des tickets avec recherche et filtres

### 3️⃣ Événements
Statistiques de performance par événement

### 4️⃣ Utilisateurs
Liste des utilisateurs et leurs rôles

## ⚡ Actions Rapides

### Valider un Ticket
1. Aller dans "Tickets"
2. Rechercher par référence/nom/email
3. Cliquer sur "Valider"
4. ✅ Ticket validé !

### Rechercher un Ticket
- Par référence: `REF123456`
- Par nom: `Jean Dupont`
- Par email: `jean@example.com`

### Filtrer les Tickets
- **Tous** : Voir tous les tickets
- **En attente** : Tickets à valider
- **Validés** : Tickets confirmés

## 🎨 Codes Couleur

| Couleur | Signification |
|---------|---------------|
| 🟢 Vert | Validé (completed) |
| 🟠 Orange | En attente (pending_cash) |
| 🔴 Rouge | Échoué (failed) |

## 🔑 Raccourcis Clavier

- `Ctrl + F` : Rechercher dans la page
- `Esc` : Fermer les modales
- `Tab` : Navigation entre les champs

## 📱 Responsive

- **Desktop** : Sidebar complète
- **Mobile** : Sidebar rétractable (icône menu)

## 🔒 Sécurité

- Token JWT stocké localement
- Déconnexion automatique si token expiré
- Accès réservé aux admins uniquement

## 🆘 Dépannage Express

| Problème | Solution |
|----------|----------|
| Page blanche | Vérifier la console (F12) |
| 401 Error | Se reconnecter |
| 403 Error | Vérifier le rôle admin |
| Données vides | Vérifier l'API backend |

## 📞 Support

Consulter la documentation complète :
- `ADMIN_DASHBOARD_GUIDE.md` - Guide utilisateur
- `ADMIN_DASHBOARD_SETUP.md` - Guide technique

---

**Astuce:** Gardez cette page en favori pour un accès rapide ! 🌟
