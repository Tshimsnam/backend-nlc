# Guide de différenciation visuelle des billets

## 🎯 Objectif

Permettre d'identifier facilement si un billet est:
- **Billet Physique**: Créé via QR code physique scanné par l'app mobile
- **Billet En Ligne**: Généré directement sur le site web

---

## 🎨 Design des badges

### Billet Physique (Purple/Violet)
```
┌─────────────────────────────────┐
│  🟣  Billet Physique            │
│      QR: ABC12345...            │
└─────────────────────────────────┘
```

**Caractéristiques:**
- Couleur: Purple/Violet (#8B5CF6 → #7C3AED)
- Icône: QR Code
- Badge: "Physique" (purple-100 background)
- Sous-texte: Affiche les 8 premiers caractères du physical_qr_id

### Billet En Ligne (Blue/Bleu)
```
┌─────────────────────────────────┐
│  🔵  Billet En Ligne            │
│      Généré sur le site         │
└─────────────────────────────────┘
```

**Caractéristiques:**
- Couleur: Blue/Bleu (#3B82F6 → #2563EB)
- Icône: Ordinateur/Écran
- Badge: "En ligne" (blue-100 background)
- Sous-texte: "Généré sur le site"

---

## 📊 Affichage dans le Dashboard

### 1. Colonne "Référence"

Chaque référence affiche maintenant un badge:

```html
<!-- Billet Physique -->
<span class="font-mono">TKT-ABC123</span>
<span class="badge-purple">
    🔲 Physique
</span>

<!-- Billet En Ligne -->
<span class="font-mono">TKT-XYZ789</span>
<span class="badge-blue">
    💻 En ligne
</span>
```

### 2. Colonne "Type" (NOUVEAU)

Une nouvelle colonne dédiée avec icône et description:

**Billet Physique:**
```
┌──────────┐
│  🟣 QR   │  Billet Physique
│          │  QR: ABC12345...
└──────────┘
```

**Billet En Ligne:**
```
┌──────────┐
│  🔵 💻   │  Billet En Ligne
│          │  Généré sur le site
└──────────┘
```

---

## 🔍 Identification technique

### Dans la base de données

```php
// Billet Physique
$ticket->physical_qr_id !== null  // true

// Billet En Ligne
$ticket->physical_qr_id === null  // true
```

### Dans le code Blade

```blade
@if($ticket->physical_qr_id)
    {{-- Billet Physique --}}
    <span class="badge-purple">Physique</span>
@else
    {{-- Billet En Ligne --}}
    <span class="badge-blue">En ligne</span>
@endif
```

---

## 🎨 Classes CSS utilisées

### Badge dans la référence

```html
<!-- Physique -->
<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
    <svg class="w-3 h-3 mr-1">...</svg>
    Physique
</span>

<!-- En ligne -->
<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
    <svg class="w-3 h-3 mr-1">...</svg>
    En ligne
</span>
```

### Colonne Type

```html
<!-- Physique -->
<div class="flex items-center gap-2">
    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-sm">
        <svg class="w-5 h-5 text-white">...</svg>
    </div>
    <div>
        <div class="text-sm font-semibold text-purple-900">Billet Physique</div>
        <div class="text-xs text-purple-600">QR: ABC12345...</div>
    </div>
</div>

<!-- En ligne -->
<div class="flex items-center gap-2">
    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
        <svg class="w-5 h-5 text-white">...</svg>
    </div>
    <div>
        <div class="text-sm font-semibold text-blue-900">Billet En Ligne</div>
        <div class="text-xs text-blue-600">Généré sur le site</div>
    </div>
</div>
```

---

## 📱 Affichage dans l'app mobile

### Lors du scan

Quand un agent scanne un billet, l'app affiche:

**Billet Physique:**
```
✅ Billet Physique Validé
━━━━━━━━━━━━━━━━━━━━━━
🔲 QR Physique: ABC12345...
👤 Jean Dupont
📧 jean@example.com
💰 50 USD
```

**Billet En Ligne:**
```
✅ Billet En Ligne Validé
━━━━━━━━━━━━━━━━━━━━━━
💻 Généré sur le site
👤 Marie Martin
📧 marie@example.com
💰 50 USD
```

---

## 🔄 Flux de création

### Billet Physique
```
1. Agent génère QR physique dans l'app
2. Participant scanne le QR
3. Participant remplit le formulaire
4. Billet créé avec physical_qr_id
5. Badge PURPLE affiché partout
```

### Billet En Ligne
```
1. Participant visite le site
2. Participant s'inscrit à l'événement
3. Participant remplit le formulaire
4. Billet créé sans physical_qr_id
5. Badge BLUE affiché partout
```

---

## 📊 Statistiques

### Dashboard - Cartes de stats

Vous pouvez ajouter des stats séparées:

```blade
<!-- Billets Physiques -->
<div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6">
    <h3 class="text-3xl font-bold text-purple-900">
        {{ $stats['physical_tickets'] }}
    </h3>
    <p class="text-purple-700">Billets Physiques</p>
</div>

<!-- Billets En Ligne -->
<div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6">
    <h3 class="text-3xl font-bold text-blue-900">
        {{ $stats['online_tickets'] }}
    </h3>
    <p class="text-blue-700">Billets En Ligne</p>
</div>
```

### Calcul dans le contrôleur

```php
$stats['physical_tickets'] = Ticket::whereNotNull('physical_qr_id')->count();
$stats['online_tickets'] = Ticket::whereNull('physical_qr_id')->count();
```

---

## 🎯 Avantages de cette différenciation

1. **Traçabilité**: Savoir d'où vient chaque billet
2. **Analyse**: Comparer les canaux de vente (physique vs en ligne)
3. **Support**: Identifier rapidement le type de billet lors du support
4. **Validation**: Les agents savent quel type de billet ils scannent
5. **Reporting**: Générer des rapports par canal

---

## 🔧 Personnalisation

### Changer les couleurs

```css
/* Billet Physique - Changer de purple à orange */
.badge-physical {
    background: #FED7AA; /* orange-100 */
    color: #9A3412;      /* orange-800 */
    border-color: #FDBA74; /* orange-200 */
}

/* Billet En Ligne - Changer de blue à green */
.badge-online {
    background: #D1FAE5; /* green-100 */
    color: #065F46;      /* green-800 */
    border-color: #A7F3D0; /* green-200 */
}
```

### Ajouter des icônes personnalisées

Vous pouvez utiliser d'autres icônes de Heroicons ou Font Awesome:
- Billet Physique: 🎫, 📱, 🔲
- Billet En Ligne: 💻, 🌐, 📧

---

## ✅ Checklist d'implémentation

- [x] Badge dans la colonne "Référence"
- [x] Nouvelle colonne "Type" avec icône
- [x] Gradient de couleur distinct (purple vs blue)
- [x] Affichage du physical_qr_id tronqué
- [x] Icônes SVG différentes
- [ ] Filtres par type dans le dashboard
- [ ] Statistiques séparées
- [ ] Export avec indication du type
- [ ] Graphiques par canal

---

## 📚 Fichiers modifiés

- `resources/views/admin/dashboard.blade.php` - Affichage des badges et colonne Type

---

## 🎨 Aperçu visuel

```
┌─────────────────────────────────────────────────────────────────┐
│ Référence          │ Type                │ Participant         │
├─────────────────────────────────────────────────────────────────┤
│ TKT-ABC123         │ 🟣 Billet Physique  │ Jean Dupont        │
│ [Physique]         │ QR: ABC12345...     │ jean@example.com   │
├─────────────────────────────────────────────────────────────────┤
│ TKT-XYZ789         │ 🔵 Billet En Ligne  │ Marie Martin       │
│ [En ligne]         │ Généré sur le site  │ marie@example.com  │
└─────────────────────────────────────────────────────────────────┘
```

---

**Date de création**: 20 Février 2026
**Version**: 1.0
**Statut**: ✅ Implémenté
