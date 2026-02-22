# Système de Billets Physiques vs En Ligne

## 📋 Vue d'Ensemble

Ce système permet de différencier et de suivre séparément les billets physiques (avec QR code pré-imprimé) et les billets en ligne (générés sur le site web).

---

## 🎯 Fonctionnalités Principales

### 1. Différenciation Visuelle
- **Billets Physiques**: Badge purple avec icône QR code
- **Billets En Ligne**: Badge blue avec icône ordinateur

### 2. Statistiques Séparées
- Total de billets créés par type
- Nombre de billets validés par type
- Revenus générés par type
- Taux de validation par type

### 3. Gestion des Événements Enrichie
- Date de fin
- Horaires complets (début et fin)
- Lieu détaillé
- Contact (téléphone et email)
- Organisateur
- Date limite d'inscription
- Liste des sponsors

---

## 🚀 Installation et Configuration

### Étape 1: Vérifier le Système
```bash
php verifier-systeme.php
```

### Étape 2: Exécuter les Migrations (si nécessaire)
```bash
php artisan migrate
```

### Étape 3: Créer les Données de Test (si nécessaire)
```bash
php artisan db:seed --class=EventSeeder
```

### Étape 4: Tester les Statistiques
```bash
php test-statistiques.php
```

---

## 📁 Fichiers Modifiés

### Backend (Laravel)

#### Modèles
- `app/Models/Event.php` - Nouveaux champs ajoutés

#### Contrôleurs
- `app/Http/Controllers/Admin/DashboardController.php` - Statistiques séparées

#### Migrations
- `database/migrations/2025_02_04_120000_create_events_table.php` - Table events
- `database/migrations/2026_02_20_000000_add_event_details_fields_to_events_table.php` - Nouveaux champs

#### Seeders
- `database/seeders/EventSeeder.php` - Données de test complètes

#### Vues
- `resources/views/admin/dashboard.blade.php` - Dashboard avec statistiques et formulaires

### Frontend (React)
- `EventInscriptionPage-v2.tsx` - Page d'inscription mise à jour
- `EventDetailPage.tsx` - Page de détails mise à jour

---

## 📊 Structure des Données

### Table `events`

#### Champs Existants
- `id`, `title`, `slug`, `description`, `full_description`
- `date`, `time`, `location`, `type`, `status`
- `image`, `agenda`, `price`, `capacity`, `registered`

#### Nouveaux Champs
- `end_date` (nullable) - Date de fin de l'événement
- `end_time` (nullable) - Heure de fin
- `venue_details` (nullable) - Lieu détaillé
- `contact_phone` (nullable) - Téléphone de contact
- `contact_email` (nullable) - Email de contact
- `organizer` (nullable) - Nom de l'organisateur
- `registration_deadline` (nullable) - Date limite d'inscription
- `sponsors` (nullable, JSON) - Liste des sponsors

### Table `tickets`

#### Champ Clé pour la Différenciation
- `physical_qr_id` (nullable) - ID du QR code physique
  - **NULL** = Billet en ligne
  - **NOT NULL** = Billet physique

---

## 🎨 Design et Couleurs

### Billets Physiques (Purple)
- Couleur principale: `#8B5CF6` (Purple)
- Fond carte: Dégradé `from-purple-50 to-purple-100`
- Badge: `bg-purple-100 text-purple-800`
- Icône: QR Code

### Billets En Ligne (Blue)
- Couleur principale: `#3B82F6` (Blue)
- Fond carte: Dégradé `from-blue-50 to-blue-100`
- Badge: `bg-blue-100 text-blue-800`
- Icône: Ordinateur

---

## 📖 Documentation Disponible

### Guides Principaux
1. **GUIDE_RAPIDE_BILLETS.md** - Guide de démarrage rapide
2. **ETAT_SYSTEME_BILLETS.md** - État complet du système
3. **APERCU_VISUEL_DASHBOARD.md** - Aperçu visuel du dashboard

### Scripts de Test
1. **verifier-systeme.php** - Vérification de l'installation
2. **test-statistiques.php** - Test des statistiques

---

## 🔍 Utilisation

### Accéder au Dashboard
1. Connectez-vous: `/admin/login`
2. Le dashboard affiche automatiquement les statistiques séparées

### Voir les Statistiques
- **Cartes Purple**: Billets physiques
- **Cartes Blue**: Billets en ligne
- Chaque carte affiche: Total, Validés, Revenus, Taux de validation

### Modifier un Événement
1. Onglet "Événements"
2. Cliquez sur "Modifier"
3. Remplissez les 3 sections:
   - Informations de base (gris)
   - Informations de contact (vert)
   - Gestion des prix (bleu)

### Générer des QR Codes Physiques
1. Onglet "QR Billet Physique"
2. Sélectionnez un événement
3. Choisissez la quantité (1-100)
4. Générez et téléchargez

---

## 🧪 Tests

### Test 1: Vérification du Système
```bash
php verifier-systeme.php
```
Vérifie:
- ✅ Colonnes de la base de données
- ✅ Événements configurés
- ✅ Statistiques calculables

### Test 2: Statistiques
```bash
php test-statistiques.php
```
Affiche:
- Statistiques globales
- Statistiques par type
- Détails des derniers billets
- Résumé avec pourcentages

### Test 3: Dashboard Web
1. Accédez à `/admin/login`
2. Vérifiez les cartes de statistiques
3. Vérifiez les tableaux de billets
4. Testez le formulaire d'édition d'événement

---

## 🔧 Dépannage

### Problème: Les colonnes n'existent pas
**Solution**:
```bash
php artisan migrate
```

### Problème: Aucun événement
**Solution**:
```bash
php artisan db:seed --class=EventSeeder
```

### Problème: Les statistiques ne s'affichent pas
**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Problème: Erreur lors de la modification
**Vérifiez**:
- Tous les champs obligatoires sont remplis
- Format des dates: YYYY-MM-DD
- Format de l'email valide

---

## 📊 Exemple de Données

### Événement de Test
```
Titre: Le Grand Salon de l'Autiste
Dates: 15-16 Avril 2026
Horaires: 08h00 - 16h00
Lieu: Kinshasa
Lieu détaillé: Fleuve Congo Hôtel Kinshasa
Contact: +243 844 338 747
Email: info@nlcrdc.org
Organisateur: Never Limit Children
Date limite: 10 Avril 2026
Sponsors: 10 sponsors (AGEPE, SOFIBANQUE, etc.)
Tarifs: 5 catégories différentes
```

---

## 🎯 Workflow Complet

### Pour les Billets Physiques
1. Admin génère des QR codes dans le dashboard
2. QR codes sont donnés au designer pour impression
3. Billets physiques sont distribués
4. Participant scanne le QR code dans l'app mobile
5. Agent remplit les informations du participant
6. Billet est créé avec `physical_qr_id`
7. Statistiques "Billets Physiques" sont mises à jour

### Pour les Billets En Ligne
1. Participant s'inscrit sur le site web
2. Participant remplit le formulaire
3. Participant effectue le paiement
4. Billet est créé sans `physical_qr_id`
5. Statistiques "Billets En Ligne" sont mises à jour

---

## 📈 Métriques Suivies

### Par Type de Billet
- Nombre total créé
- Nombre validé (paiement confirmé)
- Revenus générés
- Taux de validation (%)

### Globales
- Total de tous les billets
- Total des revenus
- Répartition physique/en ligne (%)
- Taux de validation global (%)

---

## 🔐 Sécurité

- Tous les nouveaux champs sont optionnels (nullable)
- Validation des données dans le contrôleur
- Protection CSRF sur tous les formulaires
- Authentification requise pour le dashboard admin

---

## 🚀 Prochaines Améliorations Possibles

1. **Filtres Avancés**
   - Filtrer par type de billet (physique/en ligne)
   - Filtrer par période
   - Filtrer par événement

2. **Exports**
   - Export CSV des statistiques
   - Export PDF de la liste des billets
   - Graphiques de visualisation

3. **Notifications**
   - Alertes pour billets en attente
   - Notifications de nouveaux billets
   - Rappels de date limite

4. **Rapports**
   - Rapport mensuel automatique
   - Comparaison période à période
   - Prévisions de ventes

---

## 📞 Support

### Documentation
- Consultez les fichiers `.md` dans le dossier racine
- Exécutez les scripts de test pour diagnostiquer

### Logs
- Laravel: `storage/logs/laravel.log`
- Serveur: Vérifiez les logs Apache/Nginx

### Commandes Utiles
```bash
# Vérifier le système
php verifier-systeme.php

# Tester les statistiques
php test-statistiques.php

# Vider le cache
php artisan cache:clear

# Voir les migrations
php artisan migrate:status

# Voir les routes
php artisan route:list
```

---

## ✅ Checklist de Déploiement

- [ ] Migrations exécutées
- [ ] Seeder exécuté (si nécessaire)
- [ ] Cache vidé
- [ ] Tests effectués
- [ ] Dashboard accessible
- [ ] Statistiques affichées correctement
- [ ] Formulaire d'édition fonctionnel
- [ ] QR codes générables
- [ ] Frontend React mis à jour

---

## 📝 Notes Importantes

- Tous les nouveaux champs Event sont **optionnels**
- L'identification physique/en ligne se base sur `physical_qr_id`
- Les sponsors sont stockés en **JSON array**
- Le formatage des revenus utilise l'**espace** comme séparateur
- Les couleurs sont cohérentes: **Purple** pour physique, **Blue** pour en ligne

---

**Version**: 1.0.0  
**Date**: 21 Février 2026  
**Status**: ✅ Production Ready
