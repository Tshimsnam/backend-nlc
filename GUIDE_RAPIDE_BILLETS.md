# Guide Rapide - Système de Billets Physiques vs En Ligne

## 🚀 Démarrage Rapide

### 1. Vérifier l'Installation

```bash
php verifier-systeme.php
```

Ce script vérifie:
- ✅ Colonnes de la base de données
- ✅ Événements configurés
- ✅ Statistiques des billets

### 2. Si des Migrations sont Manquantes

```bash
php artisan migrate
```

### 3. Si Aucun Événement n'Existe

```bash
php artisan db:seed --class=EventSeeder
```

Cela créera l'événement "Le Grand Salon de l'Autiste" avec toutes les données.

---

## 📊 Accéder au Dashboard

1. Connectez-vous au dashboard admin: `/admin/login`
2. Vous verrez immédiatement les statistiques séparées:
   - **Carte Purple**: Billets Physiques (QR Code)
   - **Carte Blue**: Billets En Ligne (Site Web)

---

## 🎯 Fonctionnalités Principales

### 1. Voir les Statistiques Séparées

Le dashboard affiche automatiquement:
- Total de billets créés (physiques et en ligne)
- Nombre de billets validés
- Revenus générés
- Taux de validation

### 2. Différencier les Billets

Dans les tableaux de billets, vous verrez:
- **Badge Purple** avec icône QR = Billet Physique
- **Badge Blue** avec icône ordinateur = Billet En Ligne

### 3. Modifier un Événement

1. Allez dans l'onglet "Événements"
2. Cliquez sur "Modifier" pour un événement
3. Le formulaire contient 3 sections:
   - **Gris**: Informations de base (dates, lieu, description)
   - **Vert**: Informations de contact (organisateur, téléphone, email)
   - **Bleu**: Gestion des tarifs

### 4. Générer des QR Codes Physiques

1. Allez dans l'onglet "QR Billet Physique"
2. Sélectionnez un événement
3. Choisissez le nombre de QR codes (1-100)
4. Cliquez sur "Générer les QR Codes"
5. Téléchargez et donnez au designer pour impression

---

## 🔍 Identifier un Billet

### Billet Physique
- A un `physical_qr_id` (NOT NULL)
- Badge purple dans le dashboard
- Icône QR code
- Affiche les 8 premiers caractères du QR ID

### Billet En Ligne
- N'a PAS de `physical_qr_id` (NULL)
- Badge blue dans le dashboard
- Icône ordinateur
- Texte "Généré sur le site"

---

## 📱 Frontend React

Les pages suivantes affichent les nouveaux champs:

### EventDetailPage.tsx
- Date de fin
- Horaires complets
- Lieu détaillé
- Contact cliquable (téléphone et email)
- Section sponsors avec grille responsive
- Alerte date limite d'inscription

### EventInscriptionPage-v2.tsx
- Tous les champs dans le billet généré
- Date limite d'inscription
- Contact de l'organisateur

---

## 🎨 Codes Couleur

| Type | Couleur | Badge | Icône |
|------|---------|-------|-------|
| Billet Physique | Purple (#8B5CF6) | "Physique" | QR Code |
| Billet En Ligne | Blue (#3B82F6) | "En ligne" | Ordinateur |
| Validé | Green (#10B981) | "Validé" | Check |
| En Attente | Orange (#F59E0B) | "En attente" | Horloge |
| Échoué | Red (#EF4444) | "Échoué" | X |

---

## 📝 Champs Événement

### Obligatoires
- Titre
- Date de début
- Ville/Localité

### Optionnels
- Description courte
- Description complète
- Date de fin
- Heure de début / fin
- Lieu détaillé
- Nombre max de participants
- Date limite d'inscription
- Organisateur
- Téléphone de contact
- Email de contact
- Sponsors (array JSON)

---

## 🔧 Dépannage

### Les statistiques ne s'affichent pas
```bash
# Vérifier que les migrations sont à jour
php artisan migrate:status

# Vérifier les données
php verifier-systeme.php
```

### Les nouveaux champs ne s'affichent pas
```bash
# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Erreur lors de la modification d'événement
- Vérifiez que tous les champs obligatoires sont remplis
- Vérifiez le format des dates (YYYY-MM-DD)
- Vérifiez le format de l'email

---

## 📊 Exemple de Données

L'événement de test "Le Grand Salon de l'Autiste" contient:
- Dates: 15-16 Avril 2026
- Horaires: 08h00 - 16h00
- Lieu: Fleuve Congo Hôtel Kinshasa
- Contact: +243 844 338 747
- Email: info@nlcrdc.org
- Organisateur: Never Limit Children
- 10 sponsors
- 5 tarifs différents

---

## 🎯 Prochaines Actions

1. **Tester le système**
   ```bash
   php verifier-systeme.php
   ```

2. **Accéder au dashboard**
   - URL: `/admin/login`
   - Voir les statistiques séparées

3. **Créer des billets de test**
   - Billets physiques: Scanner un QR code généré
   - Billets en ligne: S'inscrire via le site

4. **Vérifier les statistiques**
   - Les cartes purple et blue doivent afficher les bons chiffres
   - Les tableaux doivent différencier les types

---

## 📞 Support

Pour toute question ou problème:
1. Consultez `ETAT_SYSTEME_BILLETS.md` pour l'état complet
2. Exécutez `php verifier-systeme.php` pour diagnostiquer
3. Vérifiez les logs Laravel: `storage/logs/laravel.log`

---

**Dernière mise à jour**: 21 Février 2026
