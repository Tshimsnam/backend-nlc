# Mise à jour du compteur `registered` dans la table `events`

## Objectif
Incrémenter automatiquement le compteur `registered` dans la table `events` chaque fois qu'un billet est activé ou qu'un paiement est validé.

## Modifications effectuées

### 1. Modèle Event (`app/Models/Event.php`)
- Ajout de `'registered'` dans le tableau `$fillable` pour permettre les mises à jour de cette colonne

### 2. PhysicalTicketController (`app/Http/Controllers/API/PhysicalTicketController.php`)
- Méthode `createFromPhysicalQR()` : Ajout de l'incrémentation du compteur après la création d'un ticket physique
```php
Event::where('id', $validated['event_id'])->increment('registered');
```

### 3. TicketController (`app/Http/Controllers/API/TicketController.php`)
- Méthode `validateCashPayment()` : Ajout de l'incrémentation du compteur après validation d'un paiement en caisse
```php
Event::where('id', $ticket->event_id)->increment('registered');
```

### 4. OrangeMoneyWebhookController (`app/Http/Controllers/Webhooks/OrangeMoneyWebhookController.php`)
- Méthode `handle()` : Ajout de l'incrémentation du compteur après validation d'un paiement Orange Money
```php
if ($ticket->event) {
    $ticket->event->increment('registered');
}
```

### 5. MpesaWebhookController (`app/Http/Controllers/Webhooks/MpesaWebhookController.php`)
- Méthode `handle()` : Ajout de l'incrémentation du compteur après validation d'un paiement M-Pesa
```php
if ($ticket->event) {
    $ticket->event->increment('registered');
}
```

### 6. ProcessMaxiCashWebhook Job (`app/Jobs/ProcessMaxiCashWebhook.php`)
- ✅ Déjà implémenté : Le job incrémente déjà le compteur `registered` pour les paiements MaxiCash

## Points d'entrée couverts

Tous les scénarios de validation de billets sont maintenant couverts :

1. ✅ **Billets physiques** : Activation via QR code physique
2. ✅ **Paiement en caisse** : Validation manuelle par un agent
3. ✅ **Paiement MaxiCash** : Validation automatique via webhook
4. ✅ **Paiement Orange Money** : Validation automatique via webhook
5. ✅ **Paiement M-Pesa** : Validation automatique via webhook

## Comportement
- Le compteur `registered` est incrémenté de 1 à chaque fois qu'un billet passe au statut `completed`
- L'incrémentation est atomique (utilise `increment()` de Laravel)
- Aucune duplication : chaque billet n'incrémente le compteur qu'une seule fois

## Test
Pour vérifier que le compteur fonctionne correctement :
1. Créer un événement
2. Activer un billet (physique ou via paiement)
3. Vérifier que la colonne `registered` de l'événement a été incrémentée de 1
