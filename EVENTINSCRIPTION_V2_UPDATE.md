# Mise à jour EventInscriptionPage-v2.tsx

## Nouveaux champs intégrés

Le composant EventInscriptionPage-v2.tsx a été mis à jour pour afficher les nouveaux champs de l'événement:

### 1. Interface Event étendue

```typescript
interface Event {
  id: number;
  title: string;
  slug: string;
  description: string;
  full_description?: string;
  date: string;
  end_date?: string;              // NOUVEAU
  time: string;
  end_time?: string;              // NOUVEAU
  location: string;
  venue_details?: string;         // NOUVEAU
  image: string;
  agenda?: Array<{                // NOUVEAU
    day: string;
    time: string;
    activities: string;
  }>;
  capacity?: number;              // NOUVEAU
  registered?: number;            // NOUVEAU
  contact_phone?: string;         // NOUVEAU
  contact_email?: string;         // NOUVEAU
  organizer?: string;             // NOUVEAU
  registration_deadline?: string; // NOUVEAU
  sponsors?: string[];            // NOUVEAU
  event_prices: EventPrice[];
}
```

### 2. Affichage des informations enrichies

#### Dans l'étape de confirmation (Étape 3)
- **Date limite d'inscription**: Affichage d'une alerte visuelle si `registration_deadline` existe
- **Dates complètes**: Affichage de la date de début et de fin (`date` - `end_date`)
- **Lieu détaillé**: Utilisation de `venue_details` si disponible, sinon `location`
- **Organisateur**: Affichage du nom de l'organisateur

#### Dans le billet généré (Étape 5)
- **Informations complètes de l'événement**:
  - Dates: `date` - `end_date` (si disponible)
  - Horaires: `time` - `end_time` (si disponible)
  - Lieu: `venue_details` ou `location`
  - Organisateur: "Organisé par {organizer}"

- **Informations de contact de l'organisateur** (en bas du billet):
  - Téléphone: `contact_phone`
  - Email: `contact_email`
  - Affichage conditionnel uniquement si les données existent

#### Dans les instructions de paiement Orange Money
- **Numéro du bénéficiaire**: Utilise `event.contact_phone` au lieu d'un numéro codé en dur
- **Nom du bénéficiaire**: Utilise `event.organizer` au lieu d'un nom codé en dur

### 3. Améliorations visuelles

#### Alerte date limite d'inscription
```tsx
{event.registration_deadline && (
  <motion.div className="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 mb-6">
    <Calendar className="w-5 h-5 text-amber-600" />
    <div>
      <p className="font-semibold text-amber-900">Date limite d'inscription</p>
      <p className="text-amber-700">
        {new Date(event.registration_deadline).toLocaleDateString('fr-FR')}
      </p>
    </div>
  </motion.div>
)}
```

#### Section contact dans le billet
```tsx
{(event.contact_phone || event.contact_email) && (
  <div style={{ backgroundColor: '#f9fafb', padding: '12px' }}>
    <p>Contact organisateur</p>
    {event.contact_phone && <p>📞 {event.contact_phone}</p>}
    {event.contact_email && <p>✉️ {event.contact_email}</p>}
  </div>
)}
```

### 4. Exemple de données affichées

Avec l'événement "Le Grand Salon de l'Autiste":

**Étape de confirmation:**
- Date limite: 10 avril 2026
- Dates: 15 avril 2026 - 16 avril 2026
- Lieu: Fleuve Congo Hôtel Kinshasa
- Organisateur: Never Limit Children

**Billet généré:**
- Titre: Le Grand Salon de l'Autiste
- Dates: 15 avril 2026 - 16 avril 2026
- Horaires: 08h00 - 16h00
- Lieu: Fleuve Congo Hôtel Kinshasa
- Organisé par: Never Limit Children
- Contact: 📞 +243 844 338 747 / ✉️ info@nlcrdc.org

**Instructions Orange Money:**
- Numéro bénéficiaire: +243 844 338 747
- Nom: Never Limit Children

### 5. Compatibilité ascendante

Tous les nouveaux champs sont optionnels (`?`) pour assurer la compatibilité avec les événements existants qui n'ont pas ces informations. Le composant affiche les informations uniquement si elles sont disponibles.

### 6. Formatage des dates

La date limite d'inscription est formatée en français:
```typescript
new Date(event.registration_deadline).toLocaleDateString('fr-FR', { 
  day: 'numeric', 
  month: 'long', 
  year: 'numeric' 
})
// Résultat: "10 avril 2026"
```

## Fichiers modifiés

- `EventInscriptionPage-v2.tsx` - Composant principal mis à jour

## Tests recommandés

1. Tester avec un événement complet (tous les champs remplis)
2. Tester avec un événement minimal (champs optionnels vides)
3. Vérifier l'affichage du billet PDF avec les nouvelles informations
4. Vérifier que les instructions Orange Money utilisent bien les données dynamiques
5. Tester l'affichage de la date limite d'inscription
