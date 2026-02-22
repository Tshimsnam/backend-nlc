# Mise à jour du formulaire d'édition d'événement

## ✅ Nouveaux champs ajoutés

Le formulaire d'édition d'événement dans le dashboard admin a été enrichi avec tous les nouveaux champs.

---

## 📝 Sections du formulaire

### 1. Informations de base (Section grise)

**Champs existants mis à jour:**
- ✅ Titre * (obligatoire)
- ✅ Description courte (pour les listes)
- ✅ **Description complète** (NOUVEAU - pour la page de détail)

**Dates et horaires:**
- ✅ Date de début * (obligatoire)
- ✅ **Date de fin** (NOUVEAU)
- ✅ **Heure de début** (NOUVEAU - ex: 08h00)
- ✅ **Heure de fin** (NOUVEAU - ex: 16h00)

**Lieu:**
- ✅ Ville/Localité * (obligatoire - ex: Kinshasa)
- ✅ **Lieu détaillé** (NOUVEAU - ex: Fleuve Congo Hôtel Kinshasa)

**Capacité:**
- ✅ Nombre maximum de participants
- ✅ **Date limite d'inscription** (NOUVEAU)

### 2. Informations de contact (Section verte - NOUVEAU)

- ✅ **Organisateur** (ex: Never Limit Children)
- ✅ **Téléphone de contact** (ex: +243 844 338 747)
- ✅ **Email de contact** (ex: info@nlcrdc.org)

### 3. Gestion des Prix (Section bleue)

- ✅ Catégorie
- ✅ Montant
- ✅ Devise (USD, CDF, EUR)
- ✅ Label
- ✅ Description

---

## 🎨 Organisation visuelle

```
┌─────────────────────────────────────────────────────────────┐
│  📝 Informations de base (Gris)                             │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                             │
│  • Titre *                                                  │
│  • Description courte                                       │
│  • Description complète (NOUVEAU)                           │
│                                                             │
│  • Date de début *    • Date de fin (NOUVEAU)              │
│  • Heure de début     • Heure de fin (NOUVEAU)             │
│                                                             │
│  • Ville/Localité *   • Lieu détaillé (NOUVEAU)            │
│                                                             │
│  • Max participants   • Date limite (NOUVEAU)              │
│                                                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  📞 Informations de contact (Vert) - NOUVEAU                │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                             │
│  • Organisateur  • Téléphone  • Email                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  💰 Gestion des Prix (Bleu)                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                             │
│  [+ Ajouter un tarif]                                       │
│                                                             │
│  Tarif #1                                    [Supprimer]    │
│  • Catégorie  • Montant  • Devise                          │
│  • Label      • Description                                 │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📋 Détails des nouveaux champs

### Description complète
```html
<textarea name="full_description" rows="4">
  Description détaillée pour la page de l'événement
</textarea>
```
- Utilisé sur la page de détail de l'événement
- Permet un texte plus long et détaillé

### Date de fin
```html
<input type="date" name="end_date" />
```
- Pour les événements sur plusieurs jours
- Exemple: 15 avril → 16 avril 2026

### Horaires
```html
<input type="text" name="time" placeholder="Ex: 08h00" />
<input type="text" name="end_time" placeholder="Ex: 16h00" />
```
- Format libre (08h00, 8:00 AM, etc.)
- Affiché sur la page de détail

### Lieu détaillé
```html
<input type="text" name="venue_details" 
       placeholder="Ex: Fleuve Congo Hôtel Kinshasa" />
```
- Nom complet du lieu/salle
- Complète le champ "location" (ville)

### Date limite d'inscription
```html
<input type="date" name="registration_deadline" />
```
- Affichée comme alerte sur la page de détail
- Aide à créer l'urgence

### Organisateur
```html
<input type="text" name="organizer" 
       placeholder="Ex: Never Limit Children" />
```
- Nom de l'organisation
- Affiché sur la page de détail

### Contact
```html
<input type="text" name="contact_phone" 
       placeholder="Ex: +243 844 338 747" />
<input type="email" name="contact_email" 
       placeholder="Ex: info@nlcrdc.org" />
```
- Affichés sur la page de détail
- Liens cliquables (tel: et mailto:)

---

## 🔄 Flux d'utilisation

### 1. Ouvrir le formulaire d'édition

```
Dashboard → Onglet "Événements" → Cliquer sur "Modifier"
```

### 2. Remplir les nouveaux champs

**Informations de base:**
- Ajouter la description complète
- Définir la date de fin si événement sur plusieurs jours
- Ajouter les horaires (début et fin)
- Préciser le lieu détaillé
- Définir la date limite d'inscription

**Informations de contact:**
- Indiquer l'organisateur
- Ajouter le téléphone de contact
- Ajouter l'email de contact

### 3. Enregistrer

Cliquer sur "Enregistrer les modifications"

---

## 💾 Données envoyées

Le formulaire envoie maintenant:

```php
[
    'title' => 'Le Grand Salon de l\'Autisme',
    'description' => 'Description courte...',
    'full_description' => 'Description complète détaillée...',
    'date' => '2026-04-15',
    'end_date' => '2026-04-16',
    'time' => '08h00',
    'end_time' => '16h00',
    'location' => 'Kinshasa',
    'venue_details' => 'Fleuve Congo Hôtel Kinshasa',
    'max_participants' => 200,
    'registration_deadline' => '2026-04-10',
    'organizer' => 'Never Limit Children',
    'contact_phone' => '+243 844 338 747',
    'contact_email' => 'info@nlcrdc.org',
    'prices' => [
        [
            'id' => 1,
            'category' => 'doctor',
            'amount' => 50,
            'currency' => 'USD',
            'label' => 'Médecin',
            'description' => null
        ],
        // ... autres tarifs
    ]
]
```

---

## ✅ Validation

Le contrôleur `DashboardController::updateEvent()` a déjà été mis à jour pour accepter ces champs:

```php
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'full_description' => 'nullable|string',  // NOUVEAU
    'date' => 'required|date',
    'end_date' => 'nullable|date',            // NOUVEAU
    'time' => 'nullable|string|max:50',       // NOUVEAU
    'end_time' => 'nullable|string|max:50',   // NOUVEAU
    'location' => 'required|string|max:255',
    'venue_details' => 'nullable|string|max:255',  // NOUVEAU
    'max_participants' => 'nullable|integer|min:1',
    'registration_deadline' => 'nullable|date',    // NOUVEAU
    'contact_phone' => 'nullable|string|max:50',   // NOUVEAU
    'contact_email' => 'nullable|email|max:255',   // NOUVEAU
    'organizer' => 'nullable|string|max:255',      // NOUVEAU
    // ... validation des prix
]);
```

---

## 🎯 Exemple de remplissage

### Événement: Le Grand Salon de l'Autiste

**Informations de base:**
- Titre: `Le Grand Salon de l'Autiste`
- Description courte: `Conférence et ateliers sur le trouble du spectre autistique`
- Description complète: `Une conférence complète sur le trouble du spectre autistique et son impact sur la scolarité. Deux jours d'ateliers pratiques et de conférences plénières.`
- Date de début: `2026-04-15`
- Date de fin: `2026-04-16`
- Heure de début: `08h00`
- Heure de fin: `16h00`
- Ville/Localité: `Kinshasa`
- Lieu détaillé: `Fleuve Congo Hôtel Kinshasa`
- Max participants: `200`
- Date limite: `2026-04-10`

**Informations de contact:**
- Organisateur: `Never Limit Children`
- Téléphone: `+243 844 338 747`
- Email: `info@nlcrdc.org`

**Tarifs:**
- Médecin: 50 USD (événement complet)
- Étudiants: 15 USD/jour ou 20 USD (2 jours)
- Parents: 15 USD/jour
- Enseignants: 20 USD/jour

---

## 📱 Affichage sur le site

Une fois enregistré, l'événement affichera:

**Page de détail:**
- Description complète
- Dates: 15-16 Avril 2026
- Horaires: 08h00 - 16h00
- Lieu: Fleuve Congo Hôtel Kinshasa (Kinshasa)
- Organisé par: Never Limit Children
- Contact: +243 844 338 747 / info@nlcrdc.org
- Date limite d'inscription: 10 avril 2026

**Page d'inscription:**
- Toutes les informations ci-dessus
- Alerte de date limite
- Contact organisateur dans le billet

---

## 🔧 Personnalisation

### Ajouter d'autres champs

Pour ajouter un nouveau champ, suivez ce modèle:

```html
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Nom du champ
    </label>
    <input 
        type="text" 
        name="nom_du_champ" 
        x-model="selectedEvent.nom_du_champ"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    />
</div>
```

### Modifier les couleurs des sections

```html
<!-- Section grise -->
<div class="bg-gray-50 p-4 rounded-lg">

<!-- Section verte -->
<div class="bg-green-50 p-4 rounded-lg">

<!-- Section bleue -->
<div class="bg-blue-50 p-4 rounded-lg">

<!-- Nouvelle section orange -->
<div class="bg-orange-50 p-4 rounded-lg">
```

---

## 📚 Fichiers modifiés

- `resources/views/admin/dashboard.blade.php` - Formulaire d'édition enrichi
- `app/Http/Controllers/Admin/DashboardController.php` - Validation mise à jour (déjà fait)

---

## ✅ Checklist

- [x] Champs de base enrichis
- [x] Section contact ajoutée
- [x] Validation mise à jour
- [x] Placeholders informatifs
- [x] Organisation visuelle claire
- [x] Responsive design
- [ ] Tester la modification d'un événement
- [ ] Vérifier l'affichage sur le site

---

**Date**: 20 Février 2026
**Version**: 2.0
**Statut**: ✅ Implémenté
