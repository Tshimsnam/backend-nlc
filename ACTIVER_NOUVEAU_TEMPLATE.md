# 🎫 Activer le Nouveau Template Email (Boarding Pass)

## Aperçu
Le nouveau template "Boarding Pass" est maintenant disponible avec le logo NLC intégré !

## Prévisualisation
Ouvrez `compare-email-templates.html` dans votre navigateur pour comparer les deux designs côte à côte.

---

## Activation en 3 étapes

### Étape 1 : Ouvrir le fichier TicketController
```
app/Http/Controllers/API/TicketController.php
```

### Étape 2 : Trouver la méthode sendNotification()
Cherchez la ligne 260 environ (méthode `sendNotification()`)

### Étape 3 : Remplacer l'import et l'envoi
```php
// ❌ ANCIEN (ligne ~15)
use App\Mail\TicketNotificationMail;

// ✅ NOUVEAU
use App\Mail\TicketBoardingPassMail;
```

```php
// ❌ ANCIEN (ligne ~260)
Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));

// ✅ NOUVEAU
Mail::to($ticket->email)->send(new TicketBoardingPassMail($ticket));
```

---

## Code complet à modifier

### Dans les imports (début du fichier)
```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Event;
use App\Models\EventPrice;
use App\Models\Ticket;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketBoardingPassMail;  // ← Changer ici
```

### Dans la méthode sendNotification()
```php
public function sendNotification(string $ticketNumber): JsonResponse
{
    try {
        $ticket = Ticket::with(['event', 'price'])
            ->where('reference', $ticketNumber)
            ->firstOrFail();

        if (empty($ticket->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce ticket n\'a pas d\'adresse email associée.',
            ], 400);
        }

        // ✅ Utiliser le nouveau template
        Mail::to($ticket->email)->send(new TicketBoardingPassMail($ticket));

        return response()->json([
            'success' => true,
            'message' => 'Notification envoyée avec succès à ' . $ticket->email,
            'ticket' => [
                'reference' => $ticket->reference,
                'full_name' => $ticket->full_name,
                'email' => $ticket->email,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi de la notification : ' . $e->getMessage(),
        ], 500);
    }
}
```

---

## Vérification

### Test rapide
```bash
php artisan tinker
```

```php
$ticket = \App\Models\Ticket::first();
Mail::to('votre-email@example.com')->send(new \App\Mail\TicketBoardingPassMail($ticket));
```

### Via l'API
```bash
POST /api/tickets/{reference}/send-notification
```

---

## Caractéristiques du nouveau template

✅ Logo NLC en haut du header  
✅ Design type billet d'avion  
✅ Header avec gradient violet  
✅ Badge de statut (Payé/En attente)  
✅ Nom de l'événement en grand  
✅ Grille de détails (lieu, montant, catégorie)  
✅ QR code bien visible avec cadre blanc  
✅ Section détachable avec code-barres  
✅ Footer avec branding NLC  
✅ Responsive (excellent sur mobile)  

---

## Retour à l'ancien template

Si vous préférez l'ancien design, changez simplement :

```php
use App\Mail\TicketNotificationMail;
Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));
```

---

## Support

Les deux templates incluent maintenant le logo NLC :
- **Template Classique** : `resources/views/emails/ticket-notification.blade.php`
- **Template Boarding Pass** : `resources/views/emails/ticket-boarding-pass.blade.php`

URL du logo : `https://www.nlcrdc.org/wp-content/uploads/2023/02/LogoWeb2-1.png`
