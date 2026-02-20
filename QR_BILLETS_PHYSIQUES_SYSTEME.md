# Système de QR Codes pour Billets Physiques

## Vue d'ensemble

Ce système permet de générer des QR codes vierges pour des billets physiques qui seront imprimés par un designer. Une fois scannés dans l'application mobile, ces QR codes permettent de créer un ticket validé en remplissant un formulaire.

## Flux de travail

### 1. Génération des QR Codes (Dashboard Admin)

**Étapes :**
1. L'admin accède à l'onglet "QR Billet Physique" dans le dashboard
2. Sélectionne un événement dans la liste déroulante
3. Indique le nombre de QR codes à générer (1-100)
4. Clique sur "Générer les QR Codes"
5. Les QR codes sont affichés en grille
6. L'admin clique sur "Télécharger Tout" pour imprimer tous les QR codes
7. Les QR codes sont envoyés au designer pour impression sur billets physiques

**Structure des données QR :**
```json
{
  "id": "PHY-1234567890-ABC123XYZ",
  "event_id": "5",
  "type": "physical_ticket",
  "created_at": "2024-02-19T10:30:00.000Z"
}
```

### 2. Scan du QR Code (Application Mobile)

**Quand un agent scanne le QR code :**
1. L'app détecte que `type === 'physical_ticket'`
2. L'app ouvre un formulaire de création de ticket
3. L'agent remplit les informations :
   - Nom complet du participant
   - Email
   - Numéro de téléphone
   - Montant (pré-rempli selon le prix de l'événement)
   - Mode de paiement : "Caisse" (fixe)
4. L'agent valide le formulaire
5. Un ticket est créé avec `payment_status = 'completed'` (directement validé)
6. Le QR code du billet physique est marqué comme utilisé

### 3. Création du Ticket (Backend)

**Endpoint à créer :** `POST /api/tickets/physical`

**Paramètres requis :**
- `physical_qr_id` : L'ID unique du QR physique (ex: PHY-1234567890-ABC123XYZ)
- `event_id` : ID de l'événement
- `full_name` : Nom complet du participant
- `email` : Email du participant
- `phone` : Numéro de téléphone
- `event_price_id` : ID du prix sélectionné (depuis event_prices)

**Paramètres automatiques (backend) :**
- `scanned_by` : ID de l'agent qui a scanné (récupéré via `auth()->id()`)

**Logique backend :**
1. Vérifier que le `physical_qr_id` n'a pas déjà été utilisé
2. Vérifier que l'événement existe
3. Vérifier que le `event_price_id` existe et correspond à l'événement
4. Récupérer le montant, la devise, la catégorie et le type de durée depuis `event_prices`
5. Créer un participant avec les informations :
   - `user_id` : ID de l'agent connecté (via `auth()->id()`)
   - Informations du participant (nom, email, téléphone)
   - Catégorie et durée (depuis event_prices)
6. Générer une référence unique pour le ticket
7. Créer le ticket avec :
   - `payment_status = 'completed'`
   - `pay_type = 'cash'`
   - `physical_qr_id` (nouveau champ à ajouter)
   - `participant_id` (lien vers le participant créé)
   - `event_price_id` (lien vers le prix sélectionné)
8. Générer le `qr_data` pour le ticket (pour validation à l'entrée)
9. Retourner le ticket créé avec les infos du participant

**Note importante :** Le champ `user_id` dans la table `participants` enregistre l'ID de l'agent qui a activé le billet physique, permettant une traçabilité complète.

## Modifications nécessaires

### 1. Migration pour ajouter le champ `physical_qr_id`

```php
Schema::table('tickets', function (Blueprint $table) {
    $table->string('physical_qr_id')->nullable()->unique()->after('reference');
    $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('set null')->after('event_id');
    $table->foreignId('event_price_id')->nullable()->constrained('event_prices')->onDelete('set null')->after('participant_id');
    $table->index('physical_qr_id');
});
```

### 2. Nouveau Controller : `PhysicalTicketController.php`

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Participant;
use App\Models\EventPrice;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhysicalTicketController extends Controller
{
    public function createFromPhysicalQR(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'physical_qr_id' => 'required|string|unique:tickets,physical_qr_id',
            'event_id' => 'required|exists:events,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'event_price_id' => 'required|exists:event_prices,id',
        ]);

        // Vérifier que le physical_qr_id commence par "PHY-"
        if (!str_starts_with($validated['physical_qr_id'], 'PHY-')) {
            return response()->json(['error' => 'QR code invalide'], 400);
        }

        // Vérifier que l'event_price_id correspond bien à l'événement
        $eventPrice = EventPrice::where('id', $validated['event_price_id'])
            ->where('event_id', $validated['event_id'])
            ->first();

        if (!$eventPrice) {
            return response()->json(['error' => 'Prix invalide pour cet événement'], 400);
        }

        // Créer le participant
        $participant = Participant::create([
            'event_id' => $validated['event_id'],
            'user_id' => auth()->id(), // L'agent qui scanne (automatique)
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'category' => $eventPrice->category,
            'duration_type' => $eventPrice->duration_type,
        ]);

        // Générer une référence unique
        $reference = 'TKT-' . time() . '-' . strtoupper(Str::random(6));

        // Créer le ticket
        $ticket = Ticket::create([
            'reference' => $reference,
            'physical_qr_id' => $validated['physical_qr_id'],
            'event_id' => $validated['event_id'],
            'participant_id' => $participant->id,
            'event_price_id' => $eventPrice->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'amount' => $eventPrice->amount,
            'currency' => $eventPrice->currency,
            'pay_type' => 'cash',
            'payment_status' => 'completed', // Directement validé
            'qr_data' => json_encode([
                'reference' => $reference,
                'event_id' => $validated['event_id'],
                'amount' => $eventPrice->amount,
                'currency' => $eventPrice->currency,
                'payment_mode' => 'cash',
                'category' => $eventPrice->category,
                'duration_type' => $eventPrice->duration_type,
            ])
        ]);

        // Charger les relations pour la réponse
        $ticket->load(['event', 'participant', 'price']);

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'participant' => $participant,
            'message' => 'Billet physique activé avec succès'
        ]);
    }

    /**
     * Récupérer les prix d'un événement
     */
    public function getEventPrices($eventId)
    {
        $event = Event::with('prices')->findOrFail($eventId);
        
        return response()->json([
            'success' => true,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date,
                'location' => $event->location,
            ],
            'prices' => $event->prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'category' => $price->category,
                    'duration_type' => $price->duration_type,
                    'amount' => $price->amount,
                    'currency' => $price->currency,
                    'label' => $price->label,
                    'description' => $price->description,
                    // Label complet pour l'affichage
                    'display_label' => $price->label . ($price->description ? ' - ' . $price->description : ''),
                ];
            }),
        ]);
    }
}
```

### 3. Route API

```php
Route::middleware('auth:sanctum')->group(function () {
    // Créer un ticket depuis un QR physique
    Route::post('/tickets/physical', [PhysicalTicketController::class, 'createFromPhysicalQR']);
    
    // Récupérer les prix d'un événement
    Route::get('/events/{eventId}/prices', [PhysicalTicketController::class, 'getEventPrices']);
});
```

## Interface Mobile (React Native)

### Composant de formulaire après scan

```tsx
interface PhysicalTicketFormProps {
  qrData: {
    id: string;
    event_id: string;
    type: string;
  };
  onSuccess: (ticket: Ticket) => void;
}

const PhysicalTicketForm: React.FC<PhysicalTicketFormProps> = ({ qrData, onSuccess }) => {
  const [formData, setFormData] = useState({
    full_name: '',
    email: '',
    phone: '',
    amount: '',
    currency: 'USD'
  });

  const handleSubmit = async () => {
    try {
      const response = await api.post('/tickets/physical', {
        physical_qr_id: qrData.id,
        event_id: qrData.event_id,
        ...formData
      });

      if (response.data.success) {
        Alert.alert('Succès', 'Billet physique activé avec succès');
        onSuccess(response.data.ticket);
      }
    } catch (error) {
      Alert.alert('Erreur', error.response?.data?.message || 'Une erreur est survenue');
    }
  };

  return (
    <View>
      <Text>Activer le Billet Physique</Text>
      <TextInput
        placeholder="Nom complet"
        value={formData.full_name}
        onChangeText={(text) => setFormData({...formData, full_name: text})}
      />
      <TextInput
        placeholder="Email"
        value={formData.email}
        onChangeText={(text) => setFormData({...formData, email: text})}
      />
      <TextInput
        placeholder="Téléphone"
        value={formData.phone}
        onChangeText={(text) => setFormData({...formData, phone: text})}
      />
      <TextInput
        placeholder="Montant"
        value={formData.amount}
        keyboardType="numeric"
        onChangeText={(text) => setFormData({...formData, amount: text})}
      />
      <Button title="Activer le Billet" onPress={handleSubmit} />
    </View>
  );
};
```

## Avantages du système

1. **Contrôle total** : Tous les billets physiques sont traçables dans le système
2. **Flexibilité** : Les billets peuvent être vendus à la caisse sans connexion internet au moment de la vente
3. **Sécurité** : Chaque QR code ne peut être utilisé qu'une seule fois
4. **Traçabilité** : On sait qui a activé chaque billet et quand
5. **Design personnalisé** : Le designer peut créer des billets physiques avec les QR codes

## Sécurité

- Chaque `physical_qr_id` est unique et ne peut être utilisé qu'une fois
- Le format `PHY-timestamp-random` garantit l'unicité
- L'authentification est requise pour activer un billet physique
- Les scans sont enregistrés avec l'ID de l'agent

## Traçabilité

Le système enregistre automatiquement l'ID de l'agent qui active chaque billet physique :

- **Table `participants`** : Le champ `user_id` contient l'ID de l'agent connecté (via `auth()->id()`)
- **Avantages** :
  - Savoir qui a activé chaque billet
  - Statistiques par agent (nombre de billets activés, montants)
  - Audit trail complet
  - Responsabilisation des agents

**Exemple de requête de traçabilité :**
```sql
SELECT 
  u.name as agent,
  COUNT(p.id) as billets_actives,
  SUM(t.amount) as total_montant
FROM participants p
JOIN users u ON p.user_id = u.id
JOIN tickets t ON t.participant_id = p.id
WHERE p.event_id = 1
GROUP BY u.id, u.name;
```

## Workflow complet

```
Admin Dashboard
    ↓
Génération QR codes (PHY-xxx)
    ↓
Téléchargement/Impression
    ↓
Designer crée billets physiques
    ↓
Billets distribués/vendus
    ↓
Agent scanne QR code
    ↓
Formulaire d'activation
    ↓
Ticket créé (validé)
    ↓
Entrée à l'événement (scan du ticket)
```
