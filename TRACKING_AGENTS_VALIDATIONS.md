# Tracking des Validations par Agent

## 📊 Vue d'Ensemble

Le système enregistre automatiquement quel agent a validé chaque billet. Cela permet de:
- Suivre les performances de chaque agent
- Voir les statistiques de validation par agent
- Afficher l'évolution des validations sur 30 jours
- Séparer les billets physiques des billets en ligne

## 🔑 Champ `validated_by` dans la Table `tickets`

### Migration
```php
Schema::table('tickets', function (Blueprint $table) {
    $table->foreignId('validated_by')
        ->nullable()
        ->after('payment_status')
        ->constrained('users')
        ->onDelete('set null');
});
```

### Quand est-il rempli?

Le champ `validated_by` est automatiquement rempli dans les cas suivants:

#### 1. Validation de Paiement en Caisse (API)
**Route:** `POST /api/tickets/{reference}/validate-cash`

**Controller:** `TicketController::validateCashPayment()`

```php
// Récupérer l'utilisateur connecté (agent qui valide)
$userId = $request->user() ? $request->user()->id : null;

$ticket->update([
    'payment_status' => 'completed',
    'validated_by' => $userId, // ✅ Enregistré ici
]);
```

**Utilisation:**
```javascript
// L'agent doit être authentifié
const response = await fetch('/api/tickets/ABC123/validate-cash', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
```

#### 2. Activation de Billet Physique
**Route:** `POST /api/tickets/physical`

**Controller:** `PhysicalTicketController::createFromPhysicalQR()`

```php
// Récupérer l'utilisateur connecté (agent qui active)
$userId = $request->user() ? $request->user()->id : null;

$ticket = Ticket::create([
    // ... autres champs
    'payment_status' => 'completed',
    'validated_by' => $userId, // ✅ Enregistré ici
]);
```

**Utilisation:**
```javascript
// L'agent doit être authentifié
const response = await fetch('/api/tickets/physical', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    physical_qr_id: 'PHY-QR-001-ABC123',
    event_id: 1,
    full_name: 'John Doe',
    email: 'john@example.com',
    phone: '+243812345678',
    event_price_id: 1
  })
});
```

#### 3. Validation Web (Dashboard Admin)
**Route:** `POST /admin/tickets/{reference}/validate`

**Controller:** `DashboardController::validateTicketWeb()`

```php
$user = session('admin_user');

$ticket->update([
    'payment_status' => 'completed',
    'validated_by' => $user->id // ✅ Enregistré ici
]);
```

## 📱 Tracking des Scans (Table `ticket_scans`)

En plus du champ `validated_by`, chaque scan de billet est enregistré dans la table `ticket_scans`:

### Structure
```php
Schema::create('ticket_scans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
    $table->foreignId('event_id')->constrained()->onDelete('cascade');
    $table->foreignId('scanned_by')->nullable()->constrained('users')->onDelete('set null');
    $table->string('scan_location')->nullable();
    $table->string('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamp('scanned_at');
    $table->timestamps();
});
```

### Quand est-il rempli?

**Route:** `POST /api/tickets/scan` ou `POST /api/qr-scan`

**Controller:** `QRScanController::scan()`

```php
TicketScan::create([
    'ticket_id' => $ticket->id,
    'event_id' => $ticket->event_id,
    'scanned_by' => auth()->id(), // ✅ Agent qui scanne
    'scan_location' => $request->scan_location ?? 'Entrée',
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'scanned_at' => now(),
]);
```

## 📊 Page de Détails de l'Agent

**URL:** `http://localhost:8000/admin/agents/{id}/details`

**Controller:** `DashboardController::agentDetails()`

### Statistiques Affichées

#### 1. Statistiques Globales
```php
$stats = [
    'total_validations' => Ticket::where('validated_by', $agent->id)->count(),
    'physical_validations' => Ticket::where('validated_by', $agent->id)
        ->whereNotNull('physical_qr_id')->count(),
    'online_validations' => Ticket::where('validated_by', $agent->id)
        ->whereNull('physical_qr_id')->count(),
    'total_revenue' => Ticket::where('validated_by', $agent->id)
        ->where('payment_status', 'completed')->sum('amount'),
];
```

#### 2. Évolution sur 30 Jours
```php
$validationsEvolution = Ticket::select(
    DB::raw('DATE(updated_at) as date'),
    DB::raw('COUNT(*) as total'),
    DB::raw('SUM(CASE WHEN physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical'),
    DB::raw('SUM(CASE WHEN physical_qr_id IS NULL THEN 1 ELSE 0 END) as online')
)
    ->where('validated_by', $agent->id)
    ->where('updated_at', '>=', now()->subDays(30))
    ->groupBy('date')
    ->orderBy('date')
    ->get();
```

#### 3. Validations par Événement
```php
$validationsByEvent = Ticket::select(
    'events.id',
    'events.title',
    DB::raw('COUNT(*) as total'),
    DB::raw('SUM(CASE WHEN tickets.physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical'),
    DB::raw('SUM(CASE WHEN tickets.physical_qr_id IS NULL THEN 1 ELSE 0 END) as online'),
    DB::raw('SUM(CASE WHEN tickets.payment_status = "completed" THEN tickets.amount ELSE 0 END) as revenue')
)
    ->join('events', 'tickets.event_id', '=', 'events.id')
    ->where('tickets.validated_by', $agent->id)
    ->groupBy('events.id', 'events.title')
    ->orderByDesc('total')
    ->get();
```

#### 4. Dernières Validations
```php
$recentValidations = Ticket::with(['event', 'price'])
    ->where('validated_by', $agent->id)
    ->orderBy('updated_at', 'desc')
    ->limit(20)
    ->get();
```

## 🔐 Authentification Requise

Pour que le tracking fonctionne, l'agent doit être authentifié:

### Via API (Mobile App)
```javascript
// 1. Login
const loginResponse = await fetch('/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'agent@example.com',
    password: 'password123'
  })
});

const { token } = await loginResponse.json();

// 2. Utiliser le token pour les requêtes
const response = await fetch('/api/tickets/ABC123/validate-cash', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

### Via Web (Dashboard Admin)
```php
// Session automatique après login
Route::post('/login', [AuthController::class, 'webLogin']);

// L'utilisateur est stocké en session
$user = session('admin_user');
```

## 📈 Différence entre `validated_by` et `scanned_by`

### `validated_by` (Table `tickets`)
- **Quand:** Lors de la validation du paiement ou activation du billet
- **Signification:** L'agent qui a validé/activé le billet
- **Unicité:** Un seul agent par billet
- **Utilisation:** Statistiques de validation par agent

### `scanned_by` (Table `ticket_scans`)
- **Quand:** À chaque scan du billet (entrée, contrôle, etc.)
- **Signification:** L'agent qui a scanné le billet
- **Unicité:** Plusieurs scans possibles par différents agents
- **Utilisation:** Historique des scans, contrôle d'accès

## 🎯 Exemple Complet

### Scénario: Agent Active un Billet Physique

```javascript
// 1. Agent se connecte
const loginResponse = await fetch('/api/login', {
  method: 'POST',
  body: JSON.stringify({
    email: 'agent@example.com',
    password: 'password123'
  })
});
const { token, user } = await loginResponse.json();
// user.id = 5

// 2. Agent scanne un QR physique
const checkResponse = await fetch('/api/tickets/physical/check', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: JSON.stringify({
    physical_qr_id: 'PHY-QR-001-ABC123'
  })
});
// { is_activated: false } - Disponible

// 3. Agent active le billet
const activateResponse = await fetch('/api/tickets/physical', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: JSON.stringify({
    physical_qr_id: 'PHY-QR-001-ABC123',
    event_id: 1,
    full_name: 'John Doe',
    email: 'john@example.com',
    phone: '+243812345678',
    event_price_id: 1
  })
});

// Résultat dans la base de données:
// tickets table:
// - reference: TKT-1234567890-ABC123
// - physical_qr_id: PHY-QR-001-ABC123
// - payment_status: completed
// - validated_by: 5 ✅ (ID de l'agent)

// 4. Participant entre à l'événement (scan)
const scanResponse = await fetch('/api/tickets/scan', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: JSON.stringify({
    reference: 'TKT-1234567890-ABC123',
    scan_location: 'Entrée principale'
  })
});

// Résultat dans la base de données:
// ticket_scans table:
// - ticket_id: 123
// - scanned_by: 5 ✅ (ID de l'agent)
// - scan_location: Entrée principale
// - scanned_at: 2026-02-21 14:30:00

// tickets table (mis à jour):
// - scan_count: 1
// - first_scanned_at: 2026-02-21 14:30:00
// - last_scanned_at: 2026-02-21 14:30:00
```

### Résultat sur la Page de Détails de l'Agent

L'administrateur peut maintenant voir sur `/admin/agents/5/details`:

- **Total validations:** 1
- **Billets physiques:** 1 (🔲)
- **Billets en ligne:** 0 (💻)
- **Revenus générés:** 50.00 USD
- **Graphique:** Point sur le 21/02/2026 avec 1 validation physique
- **Par événement:** Le Grand Salon de l'Autiste - 1 validation
- **Dernières validations:** TKT-1234567890-ABC123 - John Doe - 50 USD

## 🔍 Requêtes SQL Utiles

### Voir tous les billets validés par un agent
```sql
SELECT 
    t.reference,
    t.full_name,
    t.amount,
    t.currency,
    CASE 
        WHEN t.physical_qr_id IS NOT NULL THEN 'Physique'
        ELSE 'En ligne'
    END as type,
    e.title as event,
    t.updated_at as validated_at
FROM tickets t
JOIN events e ON t.event_id = e.id
WHERE t.validated_by = 5
ORDER BY t.updated_at DESC;
```

### Statistiques par agent
```sql
SELECT 
    u.id,
    u.name,
    COUNT(*) as total_validations,
    SUM(CASE WHEN t.physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical,
    SUM(CASE WHEN t.physical_qr_id IS NULL THEN 1 ELSE 0 END) as online,
    SUM(t.amount) as total_revenue
FROM users u
LEFT JOIN tickets t ON u.id = t.validated_by
WHERE t.payment_status = 'completed'
GROUP BY u.id, u.name
ORDER BY total_validations DESC;
```

### Historique des scans par agent
```sql
SELECT 
    u.name as agent,
    t.reference,
    ts.scan_location,
    ts.scanned_at
FROM ticket_scans ts
JOIN users u ON ts.scanned_by = u.id
JOIN tickets t ON ts.ticket_id = t.id
WHERE ts.scanned_by = 5
ORDER BY ts.scanned_at DESC
LIMIT 20;
```

## 📝 Notes Importantes

1. **Authentification obligatoire:** L'agent doit être connecté pour que `validated_by` soit rempli
2. **Middleware auth:sanctum:** Les routes API doivent utiliser ce middleware
3. **Session pour le web:** Le dashboard admin utilise les sessions Laravel
4. **Nullable:** Le champ `validated_by` est nullable pour les anciens tickets
5. **onDelete('set null'):** Si un agent est supprimé, les tickets restent mais `validated_by` devient NULL

## 🚀 Prochaines Étapes

1. ✅ Champ `validated_by` ajouté à la table `tickets`
2. ✅ Tracking automatique dans `validateCashPayment()`
3. ✅ Tracking automatique dans `createFromPhysicalQR()`
4. ✅ Tracking automatique dans `validateTicketWeb()`
5. ✅ Page de détails agent avec statistiques complètes
6. ✅ Séparation physique/en ligne dans les stats
7. ✅ Graphique d'évolution sur 30 jours

## 📞 Support

Pour toute question sur le tracking des agents:
- Documentation: `AGENT_DETAILS_FEATURE.md`
- Page de détails: `/admin/agents/{id}/details`
- Email: support@nlcrdc.org
