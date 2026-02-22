# Guide des QR Codes - Système Complet

## 📊 Vue d'ensemble

Le système utilise **DEUX types de QR codes** différents avec des objectifs distincts:

### 1. QR Code Événement (Marketing)
**Fichier:** `qr-code-event-scan.html`

**Objectif:** Compter combien de personnes scannent pour voir l'événement

**Utilisation:**
- Affiches publicitaires
- Réseaux sociaux
- Flyers
- Campagnes marketing

**Ce qui se passe quand on scanne:**
1. La personne est redirigée vers la page de l'événement
2. Le scan est enregistré dans la table `event_scans`
3. On peut voir les statistiques dans le dashboard admin

### 2. QR Code Billet (Validation)
**Fichier:** Généré automatiquement sur chaque billet

**Objectif:** Valider l'entrée d'un participant à l'événement

**Utilisation:**
- Imprimé sur chaque billet
- Scanné à l'entrée de l'événement
- Validation par l'application mobile

**Ce qui se passe quand on scanne:**
1. L'agent scanne le QR code avec l'app mobile
2. Le scan est enregistré dans la table `ticket_scans`
3. Le compteur du billet s'incrémente
4. L'agent voit les infos du participant

---

## 🎯 Différences Clés

| Aspect | QR Code Événement | QR Code Billet |
|--------|-------------------|----------------|
| **Objectif** | Marketing & Statistiques | Validation d'entrée |
| **Contenu** | URL de l'événement | Données du billet (JSON) |
| **Table BD** | `event_scans` | `ticket_scans` |
| **Qui scanne** | Grand public | Agents/Contrôleurs |
| **Quand** | Avant l'événement | Jour de l'événement |
| **Résultat** | Visite de la page | Validation d'entrée |

---

## 📱 QR Code Événement - Détails

### Génération

Utilisez le fichier `qr-code-event-scan.html` pour générer le QR code.

**Étapes:**
1. Ouvrir `qr-code-event-scan.html` dans un navigateur
2. Choisir l'environnement (Local ou Production)
3. Entrer le slug de l'événement
4. Cliquer sur "Générer le QR Code"
5. Télécharger ou imprimer

### Contenu du QR Code

```
https://votre-site.com/evenements/le-grand-salon-de-lautisme
```

Simple URL vers la page de l'événement.

### Flux de Scan

```
Personne scanne QR
    ↓
Redirigée vers page événement
    ↓
Frontend appelle automatiquement:
POST /api/events/{slug}/scan
    ↓
Scan enregistré dans event_scans
    ↓
Statistiques mises à jour
```

### API Endpoint

**Enregistrer un scan:**
```http
POST /api/events/{slug}/scan
Content-Type: application/json

Headers automatiques:
- IP Address
- User Agent
- Device Type (mobile/tablet/desktop)

Response:
{
  "success": true,
  "message": "Scan enregistré avec succès",
  "event": {
    "title": "Mon Événement",
    "slug": "mon-evenement"
  }
}
```

**Obtenir les statistiques:**
```http
GET /api/events/{slug}/scans

Response:
{
  "event": {...},
  "total_scans": 245,
  "scans_by_device": [
    { "device_type": "mobile", "count": 180 },
    { "device_type": "desktop", "count": 50 },
    { "device_type": "tablet", "count": 15 }
  ],
  "recent_scans": [...]
}
```

### Table `event_scans`

```sql
CREATE TABLE event_scans (
    id BIGINT PRIMARY KEY,
    event_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(20),
    scanned_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Cas d'utilisation

#### 1. Affiche Publicitaire
```
┌─────────────────────────┐
│                         │
│   ÉVÉNEMENT SPÉCIAL     │
│                         │
│   15 Mars 2026          │
│   Stade des Martyrs     │
│                         │
│   [QR CODE ÉVÉNEMENT]   │
│                         │
│   Scannez pour          │
│   plus d'infos          │
│                         │
└─────────────────────────┘
```

#### 2. Post Réseaux Sociaux
```
🎉 Événement à venir!

📅 15 Mars 2026
📍 Stade des Martyrs

Scannez le QR code pour:
✓ Voir les détails
✓ Choisir votre tarif
✓ Vous inscrire

[QR CODE ÉVÉNEMENT]
```

#### 3. Flyer
```
Imprimez le QR code sur des flyers
distribués dans la ville.

Chaque scan = 1 personne intéressée
Mesurez l'efficacité de votre campagne!
```

---

## 🎫 QR Code Billet - Détails

### Génération

Le QR code est généré automatiquement lors de la création du billet dans `EventInscriptionPage-v2.tsx`.

### Contenu du QR Code

```json
{
  "reference": "REF123ABC",
  "event": "Le trouble du spectre autistique",
  "participant": "Jean Dupont",
  "email": "jean@example.com",
  "phone": "+243812345678",
  "amount": "50.00",
  "currency": "USD",
  "category": "medecin",
  "date": "2026-03-15",
  "location": "Kinshasa"
}
```

JSON complet avec toutes les informations du billet.

### Flux de Scan

```
Agent ouvre app mobile
    ↓
Scanne QR code du billet
    ↓
App envoie données à:
POST /api/tickets/scan
    ↓
Backend vérifie le billet
    ↓
Enregistre dans ticket_scans
    ↓
Incrémente scan_count
    ↓
Retourne infos participant
    ↓
Agent valide l'entrée
```

### API Endpoint

**Scanner un billet:**
```http
POST /api/tickets/scan
Authorization: Bearer {token}
Content-Type: application/json

{
  "qr_data": "{\"reference\":\"REF123\",\"event\":\"...\"}",
  "scan_location": "Entrée principale"
}

Response:
{
  "success": true,
  "message": "Billet scanné avec succès",
  "ticket": {
    "reference": "REF123",
    "full_name": "Jean Dupont",
    "scan_count": 3,
    "first_scanned_at": "2026-02-18T10:00:00Z",
    "last_scanned_at": "2026-02-18T14:30:00Z",
    ...
  },
  "scan_info": {
    "scan_count": 3,
    "is_first_scan": false
  }
}
```

### Table `ticket_scans`

```sql
CREATE TABLE ticket_scans (
    id BIGINT PRIMARY KEY,
    ticket_id BIGINT,
    event_id BIGINT,
    scanned_by BIGINT, -- ID de l'agent
    scan_location VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    scanned_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Colonnes ajoutées à `tickets`

```sql
ALTER TABLE tickets ADD COLUMN scan_count INT DEFAULT 0;
ALTER TABLE tickets ADD COLUMN first_scanned_at TIMESTAMP NULL;
ALTER TABLE tickets ADD COLUMN last_scanned_at TIMESTAMP NULL;
```

### Cas d'utilisation

#### 1. Entrée Principale
```
Agent à l'entrée:
1. Ouvre l'app mobile
2. Scanne le QR code du billet
3. Voit: "Jean Dupont - Médecin - 50 USD"
4. Valide l'entrée
5. Scan enregistré: "Entrée principale"
```

#### 2. Entrée VIP
```
Agent VIP:
1. Scanne le billet
2. Vérifie la catégorie
3. Si VIP → OK
4. Si pas VIP → Refusé
5. Scan enregistré: "Entrée VIP"
```

#### 3. Détection de Fraude
```
Si scan_count > 1:
→ Alerte: "Ce billet a déjà été scanné!"
→ Afficher l'historique des scans
→ Décision de l'agent
```

---

## 📊 Dashboard Admin - Statistiques

### Statistiques Événement (Marketing)

**Endpoint:** `GET /api/admin/dashboard/scan-stats`

**Affiche:**
- Nombre total de scans par événement
- Scans par type d'appareil (mobile/desktop/tablet)
- Scans par jour (graphique)
- Taux de conversion (scans → inscriptions)

**Exemple:**
```
Événement: Le trouble du spectre autistique
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 Scans QR Code Marketing
   Total: 450 scans
   
   Par appareil:
   📱 Mobile:  320 (71%)
   💻 Desktop: 100 (22%)
   📱 Tablet:   30 (7%)
   
   Conversion:
   450 scans → 120 inscriptions (27%)
```

### Statistiques Billets (Validation)

**Endpoint:** `GET /api/admin/dashboard/scan-stats`

**Affiche:**
- Nombre de billets scannés
- Taux de présence (billets scannés / billets vendus)
- Scans par lieu (Entrée principale, VIP, etc.)
- Scans par agent
- Historique des scans

**Exemple:**
```
Événement: Le trouble du spectre autistique
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🎫 Validation des Billets
   Billets vendus: 150
   Billets scannés: 120 (80%)
   Billets non scannés: 30 (20%)
   
   Par lieu:
   🚪 Entrée principale: 100
   ⭐ Entrée VIP: 20
   
   Par agent:
   👤 Agent Dupont: 80 scans
   👤 Agent Martin: 40 scans
```

---

## 🔄 Intégration Frontend

### Page Événement (Auto-scan)

Quand quelqu'un visite la page d'un événement via QR code, le scan est enregistré automatiquement:

```typescript
// EventDetailPage.tsx
useEffect(() => {
  // Vérifier si l'utilisateur vient d'un QR code
  const urlParams = new URLSearchParams(window.location.search);
  const fromQR = urlParams.get('qr') === 'true';
  
  if (fromQR) {
    // Enregistrer le scan
    axios.post(`${API_URL}/events/${slug}/scan`)
      .then(() => console.log('Scan enregistré'))
      .catch(err => console.error('Erreur scan:', err));
  }
}, [slug]);
```

### Génération QR Billet

```typescript
// EventInscriptionPage-v2.tsx
const qrInfo = JSON.stringify({
  reference: ticket.reference,
  event: event.title,
  participant: formData.full_name,
  email: formData.email,
  phone: formData.phone,
  amount: ticket.amount,
  currency: ticket.currency,
  category: ticket.category,
  date: event.date,
  location: event.location,
});

<QRCodeSVG 
  value={qrInfo} 
  size={150}
  level="H"
/>
```

---

## 📱 Application Mobile

### Scanner un Billet

```typescript
// ScanQRScreen.tsx
const handleScan = async (qrData: string) => {
  const response = await axios.post(
    '/api/tickets/scan',
    {
      qr_data: qrData,
      scan_location: 'Entrée principale'
    },
    {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }
  );
  
  if (response.data.success) {
    const { ticket, scan_info } = response.data;
    
    Alert.alert(
      scan_info.is_first_scan ? '✅ Premier Scan' : '✅ Billet Scanné',
      `${ticket.full_name}\nScan #${scan_info.scan_count}`
    );
  }
};
```

---

## 🎨 Design des QR Codes

### QR Code Événement (Marketing)

**Recommandations:**
- Taille: 300x300px minimum
- Couleur: Noir sur blanc (meilleure lisibilité)
- Marge: 20px autour du QR code
- Texte: "Scannez pour plus d'infos"
- Logo: Peut inclure un petit logo au centre

### QR Code Billet (Validation)

**Recommandations:**
- Taille: 150x150px (sur le billet)
- Couleur: Noir sur blanc uniquement
- Niveau de correction: H (High) - 30% de redondance
- Texte: "Présentez ce code pour valider votre billet"
- Pas de logo (pour maximiser la lisibilité)

---

## 🔒 Sécurité

### QR Code Événement
- ✅ Pas de données sensibles
- ✅ URL publique
- ✅ Pas d'authentification requise
- ✅ Peut être partagé librement

### QR Code Billet
- ⚠️ Contient des données personnelles
- ⚠️ Référence unique du billet
- ⚠️ Scan nécessite authentification
- ⚠️ Ne pas partager publiquement

---

## 📈 Métriques & Analytics

### Événement (Marketing)

**KPIs à suivre:**
- Nombre de scans par jour
- Taux de conversion (scans → inscriptions)
- Appareils utilisés (mobile vs desktop)
- Heures de pic de scans
- Géolocalisation (via IP)

### Billets (Validation)

**KPIs à suivre:**
- Taux de présence (scannés / vendus)
- Temps moyen entre scans
- Billets scannés plusieurs fois (fraude?)
- Performance des agents
- Flux d'entrée (pics d'affluence)

---

## 🛠️ Maintenance

### Nettoyage des Données

```sql
-- Supprimer les scans événement de plus de 6 mois
DELETE FROM event_scans 
WHERE scanned_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Supprimer les scans de billets d'événements passés (> 1 an)
DELETE FROM ticket_scans 
WHERE event_id IN (
  SELECT id FROM events 
  WHERE date < DATE_SUB(NOW(), INTERVAL 1 YEAR)
);
```

### Backup

```bash
# Backup des statistiques de scans
mysqldump -u user -p database event_scans ticket_scans > scans_backup.sql
```

---

## 📚 Ressources

### Fichiers Importants

- `qr-code-event-scan.html` - Générateur QR événement
- `qr-code-generator-local.html` - Générateur QR local (dev)
- `EventInscriptionPage-v2.tsx` - Génération QR billet
- `QRScanController.php` - API scan billets
- `EventScanController.php` - API scan événements
- `QR_SCAN_SYSTEM_GUIDE.md` - Guide système scan billets
- `README_APPLICATION_MOBILE.md` - Guide app mobile

### Documentation API

- Voir `API_DOCUMENTATION.md` pour tous les endpoints
- Voir `QR_SCAN_SYSTEM_GUIDE.md` pour détails techniques

---

## ❓ FAQ

**Q: Quelle est la différence entre les deux QR codes?**
R: Le QR événement est pour le marketing (compter les intéressés), le QR billet est pour valider l'entrée.

**Q: Puis-je utiliser le même QR code pour plusieurs événements?**
R: Non, chaque événement a son propre QR code unique.

**Q: Combien de fois un billet peut-il être scanné?**
R: Illimité. Le système compte chaque scan et alerte si c'est un re-scan.

**Q: Les scans fonctionnent-ils offline?**
R: Non, une connexion internet est requise pour enregistrer les scans.

**Q: Comment voir les statistiques?**
R: Dans le dashboard admin à `/admin` ou via l'API.

---

## 🎯 Résumé Rapide

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  QR CODE ÉVÉNEMENT (Marketing)                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  📊 Objectif: Compter les personnes intéressées         │
│  🔗 Contenu: URL de l'événement                         │
│  👥 Qui: Grand public                                   │
│  📍 Où: Affiches, réseaux sociaux, flyers              │
│  💾 Table: event_scans                                  │
│                                                         │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                                                         │
│  QR CODE BILLET (Validation)                            │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  🎫 Objectif: Valider l'entrée à l'événement            │
│  📦 Contenu: Données complètes du billet (JSON)         │
│  👮 Qui: Agents/Contrôleurs                             │
│  📍 Où: Entrée de l'événement                           │
│  💾 Table: ticket_scans                                 │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

**Dernière mise à jour:** 18 Février 2026
