# Mise à jour des champs Event

## Nouveaux champs ajoutés

D'après l'affiche "Le Grand Salon de l'Autisme", les champs suivants ont été ajoutés au modèle Event:

### Champs de contact
- `contact_phone` (string, nullable) - Numéro de téléphone de contact (ex: +243 844 338 747)
- `contact_email` (string, nullable) - Email de contact (ex: info@nlcrdc.org)

### Détails du lieu
- `venue_details` (string, nullable) - Nom détaillé du lieu (ex: Fleuve Congo Hôtel Kinshasa)

### Organisation
- `organizer` (string, nullable) - Nom de l'organisateur (ex: Never Limit Children)
- `registration_deadline` (date, nullable) - Date limite d'inscription

### Sponsors/Partenaires
- `sponsors` (json, nullable) - Liste des sponsors et partenaires

## Fichiers modifiés

### 1. Migration principale
- `database/migrations/2025_02_04_120000_create_events_table.php`
  - Ajout des nouveaux champs dans la structure de base

### 2. Migration d'ajout
- `database/migrations/2026_02_20_000000_add_event_details_fields_to_events_table.php` (NOUVEAU)
  - Migration pour ajouter les champs aux tables existantes
  - Utilise `Schema::hasColumn()` pour éviter les doublons

### 3. Modèle Event
- `app/Models/Event.php`
  - Ajout des champs dans `$fillable`
  - Ajout des casts pour `sponsors` (array) et `registration_deadline` (date)

### 4. Validation des requêtes
- `app/Http/Requests/StoreEventRequest.php`
  - Ajout des règles de validation pour les nouveaux champs

### 5. Contrôleur Admin
- `app/Http/Controllers/Admin/DashboardController.php`
  - Méthode `updateEvent()` mise à jour pour gérer les nouveaux champs

### 6. Seeder
- `database/seeders/EventSeeder.php`
  - Mise à jour avec les données de l'affiche réelle:
    - Titre: "Le Grand Salon de l'Autisme"
    - Dates: 15-16 Avril 2026
    - Horaires: 08h00-16h00
    - Lieu: Fleuve Congo Hôtel Kinshasa
    - Contact: +243 844 338 747 / info@nlcrdc.org
    - Liste des sponsors

## Commandes à exécuter

```bash
# Exécuter la nouvelle migration
php artisan migrate

# Ou réinitialiser et re-seeder (ATTENTION: efface les données)
php artisan migrate:fresh --seed
```

## Structure JSON des sponsors

```json
{
  "sponsors": [
    "AGEPE",
    "SOFIBANQUE",
    "TIJE",
    "Fondation Denise Nyakeru Tshisekedi",
    "Vodacom",
    "Ecobank",
    "Calugi EL",
    "Socomerg sarl",
    "CANAL+",
    "UNITED"
  ]
}
```

## Exemple d'utilisation API

### Créer un événement avec les nouveaux champs

```json
POST /api/events
{
  "title": "Le Grand Salon de l'Autisme",
  "date": "2026-04-15",
  "end_date": "2026-04-16",
  "time": "08h00",
  "end_time": "16h00",
  "location": "Kinshasa",
  "venue_details": "Fleuve Congo Hôtel Kinshasa",
  "contact_phone": "+243 844 338 747",
  "contact_email": "info@nlcrdc.org",
  "organizer": "Never Limit Children",
  "registration_deadline": "2026-04-10",
  "sponsors": [
    "AGEPE",
    "SOFIBANQUE",
    "Vodacom"
  ]
}
```

## Notes importantes

1. Tous les nouveaux champs sont **nullable** pour ne pas casser les événements existants
2. Le champ `sponsors` est stocké en JSON pour permettre une liste flexible
3. Le champ `registration_deadline` est casté en date pour faciliter les comparaisons
4. La migration utilise `Schema::hasColumn()` pour éviter les erreurs si les colonnes existent déjà
