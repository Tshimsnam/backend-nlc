# Guide des Templates Email pour les Billets

## Templates Disponibles

### 1. Template Classique (ticket-notification.blade.php)
Design détaillé avec toutes les informations du billet et de l'événement.

**Utilisation :**
```php
use App\Mail\TicketNotificationMail;

Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));
```

**Caractéristiques :**
- Design avec gradient violet
- Sections détaillées (ticket info, event info, QR code)
- Badges de statut colorés
- Notes importantes pour paiement en attente
- Section contact complète

---

### 2. Template Boarding Pass (ticket-boarding-pass.blade.php) ✨ NOUVEAU
Design moderne inspiré des billets d'avion, épuré et professionnel.

**Utilisation :**
```php
use App\Mail\TicketBoardingPassMail;

Mail::to($ticket->email)->send(new TicketBoardingPassMail($ticket));
```

**Caractéristiques :**
- Design type "boarding pass" (billet d'avion)
- Header avec gradient violet et badge de statut
- Section route avec nom de l'événement en grand
- Grille de détails (lieu, montant, catégorie)
- QR code centré avec cadre blanc
- Section détachable (tear-off) avec code-barres
- Footer avec branding NLC
- Avertissement pour paiement en attente

---

## Changer le Template par Défaut

### Option 1 : Modifier le TicketController
Dans `app/Http/Controllers/API/TicketController.php`, méthode `sendNotification()` :

```php
// Ancien (template classique)
Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));

// Nouveau (template boarding pass)
use App\Mail\TicketBoardingPassMail;
Mail::to($ticket->email)->send(new TicketBoardingPassMail($ticket));
```

### Option 2 : Modifier la vue dans TicketNotificationMail
Dans `app/Mail/TicketNotificationMail.php`, méthode `content()` :

```php
return new Content(
    view: 'emails.ticket-boarding-pass', // Changer ici
    with: [
        'ticket' => $this->ticket,
        'event' => $this->ticket->event,
        'price' => $this->ticket->price,
    ],
);
```

---

## Personnalisation

### Couleurs du Boarding Pass
Dans `resources/views/emails/ticket-boarding-pass.blade.php` :

```css
/* Header gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Status badge completed */
.status-completed {
    background: #4caf50;
}

/* Status badge pending */
.status-pending {
    background: #ff9800;
}
```

### Logo ou Image
Pour ajouter un logo dans le header :

```html
<div class="pass-header">
    <img src="URL_DU_LOGO" alt="Logo" style="height: 40px; margin-bottom: 15px;">
    <div class="pass-header-top">
        <!-- ... -->
    </div>
</div>
```

---

## Variables Disponibles

Les deux templates ont accès aux mêmes variables :

- `$ticket` : Objet Ticket complet
  - `$ticket->reference` : Référence du billet
  - `$ticket->full_name` : Nom du participant
  - `$ticket->email` : Email
  - `$ticket->phone` : Téléphone
  - `$ticket->amount` : Montant
  - `$ticket->currency` : Devise
  - `$ticket->payment_status` : Statut (completed, pending_cash, etc.)
  - `$ticket->qr_data` : Données pour le QR code

- `$event` : Objet Event
  - `$event->title` : Titre de l'événement
  - `$event->date` : Date de début
  - `$event->end_date` : Date de fin
  - `$event->time` : Heure de début
  - `$event->end_time` : Heure de fin
  - `$event->location` : Lieu
  - `$event->venue_details` : Détails du lieu
  - `$event->contact_email` : Email de contact
  - `$event->contact_phone` : Téléphone de contact

- `$price` : Objet EventPrice
  - `$price->label` : Label du tarif
  - `$price->category` : Catégorie
  - `$price->amount` : Montant

---

## Test des Templates

### Tester l'envoi d'email
```bash
php artisan tinker
```

```php
$ticket = \App\Models\Ticket::first();

// Test template classique
Mail::to('test@example.com')->send(new \App\Mail\TicketNotificationMail($ticket));

// Test template boarding pass
Mail::to('test@example.com')->send(new \App\Mail\TicketBoardingPassMail($ticket));
```

### Prévisualiser dans le navigateur
Créer une route temporaire dans `routes/web.php` :

```php
Route::get('/preview-email/{reference}', function($reference) {
    $ticket = \App\Models\Ticket::where('reference', $reference)
        ->with(['event', 'price'])
        ->firstOrFail();
    
    return view('emails.ticket-boarding-pass', [
        'ticket' => $ticket,
        'event' => $ticket->event,
        'price' => $ticket->price,
    ]);
});
```

Puis visiter : `http://localhost:8000/preview-email/TKT-123456`

---

## Recommandation

Le template **Boarding Pass** est recommandé pour :
- ✅ Design moderne et professionnel
- ✅ Meilleure lisibilité sur mobile
- ✅ Look premium qui inspire confiance
- ✅ QR code bien mis en valeur
- ✅ Informations essentielles facilement identifiables

Le template **Classique** est recommandé pour :
- ✅ Plus d'informations détaillées
- ✅ Design coloré et convivial
- ✅ Sections bien séparées
