# Guide - Scan QR Code Événement

## 🎯 Objectif

Compter automatiquement combien de personnes scannent le QR code de l'événement pour mesurer l'intérêt et l'efficacité des campagnes marketing.

---

## 📊 Comment ça fonctionne ?

### 1. Génération du QR Code

Utilisez le fichier `qr-code-event-scan.html` pour générer un QR code qui pointe vers votre événement.

**URL générée:**
```
https://votre-site.com/evenements/mon-evenement?qr=true
```

Le paramètre `?qr=true` indique que l'utilisateur vient d'un QR code.

### 2. Scan du QR Code

Quand quelqu'un scanne le QR code avec son téléphone:

```
┌─────────────────────────────────────┐
│  Personne scanne le QR code         │
│           ↓                         │
│  Redirigée vers l'URL               │
│  /evenements/mon-evenement?qr=true  │
│           ↓                         │
│  Page EventDetailPage.tsx           │
│  ou EventInscriptionPage-v2.tsx     │
│           ↓                         │
│  Détecte le paramètre ?qr=true      │
│           ↓                         │
│  Appelle POST /api/events/{slug}/scan│
│           ↓                         │
│  Scan enregistré dans event_scans   │
│           ↓                         │
│  Statistiques mises à jour          │
└─────────────────────────────────────┘
```

### 3. Enregistrement du Scan

Le frontend détecte automatiquement le paramètre `?qr=true` et enregistre le scan:

**Dans EventDetailPage.tsx:**
```typescript
const urlParams = new URLSearchParams(window.location.search);
const fromQR = urlParams.get('qr') === 'true' || urlParams.get('from') === 'qr';

if (fromQR) {
  try {
    await axios.post(`${API_URL}/events/${slug}/scan`);
    console.log('✅ Scan QR événement enregistré');
  } catch (scanError) {
    console.error('❌ Erreur scan:', scanError);
  }
}
```

**Dans EventInscriptionPage-v2.tsx:**
```typescript
const urlParams = new URLSearchParams(window.location.search);
const fromQR = urlParams.get('qr') === 'true' || urlParams.get('from') === 'qr';

if (fromQR) {
  try {
    await axios.post(`${API_URL}/events/${slug}/scan`);
    console.log('✅ Scan événement enregistré');
  } catch (scanErr) {
    console.error('Erreur scan:', scanErr);
  }
}
```

---

## 🔧 Configuration

### Fichiers Modifiés

1. **qr-code-event-scan.html**
   - Ajoute automatiquement `?qr=true` à l'URL générée
   - Interface moderne pour générer les QR codes

2. **qr-code-generator-local.html**
   - Ajoute automatiquement `?qr=true` à l'URL
   - Pour les tests en local

3. **EventDetailPage.tsx**
   - Détecte le paramètre `?qr=true`
   - Enregistre le scan automatiquement

4. **EventInscriptionPage-v2.tsx**
   - Détecte le paramètre `?qr=true`
   - Enregistre le scan automatiquement

---

## 📱 Cas d'Utilisation

### Cas 1: Affiche Publicitaire

```
┌─────────────────────────────────────┐
│                                     │
│   ÉVÉNEMENT SPÉCIAL                 │
│   Le trouble du spectre autistique  │
│                                     │
│   📅 15 Mars 2026                   │
│   📍 Kinshasa                       │
│                                     │
│   ┌─────────────────┐               │
│   │                 │               │
│   │   [QR CODE]     │               │
│   │   ?qr=true      │               │
│   │                 │               │
│   └─────────────────┘               │
│                                     │
│   Scannez pour vous inscrire        │
│                                     │
└─────────────────────────────────────┘
```

**Résultat:**
- Chaque scan est enregistré
- Vous savez combien de personnes ont vu l'affiche
- Vous pouvez mesurer l'efficacité de l'emplacement

### Cas 2: Réseaux Sociaux

```
Post Facebook/Instagram:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🎉 Événement à venir!

Le trouble du spectre autistique et la scolarité

📅 15 Mars 2026
📍 Kinshasa

[IMAGE avec QR CODE]

Scannez le QR code pour:
✓ Voir les détails
✓ Choisir votre tarif
✓ Vous inscrire en ligne

#Événement #Autisme #Éducation
```

**Résultat:**
- Vous savez combien de personnes ont scanné depuis les réseaux sociaux
- Vous pouvez comparer l'efficacité de différentes plateformes

### Cas 3: Flyers Distribués

```
Imprimez 1000 flyers avec le QR code
Distribuez-les dans différents quartiers

Résultats après 1 semaine:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Quartier A: 45 scans (4.5%)
Quartier B: 78 scans (7.8%)
Quartier C: 23 scans (2.3%)

→ Quartier B est le plus intéressé!
```

---

## 📊 Statistiques Disponibles

### API Endpoint

**Obtenir les statistiques de scan:**
```http
GET /api/events/{slug}/scans

Response:
{
  "event": {
    "id": 1,
    "title": "Le trouble du spectre autistique",
    "slug": "le-grand-salon-de-lautisme"
  },
  "total_scans": 245,
  "scans_by_device": [
    {
      "device_type": "mobile",
      "count": 180
    },
    {
      "device_type": "desktop",
      "count": 50
    },
    {
      "device_type": "tablet",
      "count": 15
    }
  ],
  "recent_scans": [
    {
      "id": 245,
      "scanned_at": "2026-02-18T14:30:00Z",
      "device_type": "mobile",
      "ip_address": "192.168.1.100"
    }
  ]
}
```

### Dashboard Admin

Dans le dashboard admin, vous pouvez voir:

```
┌─────────────────────────────────────────────────────────┐
│  Événement: Le trouble du spectre autistique            │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                         │
│  📊 Scans QR Code Marketing                             │
│                                                         │
│  Total: 245 scans                                       │
│                                                         │
│  Par appareil:                                          │
│  📱 Mobile:  180 (73%)  ████████████████████████        │
│  💻 Desktop:  50 (20%)  ████████                        │
│  📱 Tablet:   15 (6%)   ███                             │
│                                                         │
│  Conversion:                                            │
│  245 scans → 89 inscriptions (36%)                      │
│                                                         │
│  Évolution (7 derniers jours):                          │
│  Lun: 12 scans                                          │
│  Mar: 28 scans                                          │
│  Mer: 45 scans ← Pic!                                   │
│  Jeu: 38 scans                                          │
│  Ven: 52 scans ← Pic!                                   │
│  Sam: 41 scans                                          │
│  Dim: 29 scans                                          │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🔍 Données Enregistrées

Pour chaque scan, le système enregistre:

```sql
CREATE TABLE event_scans (
    id BIGINT PRIMARY KEY,
    event_id BIGINT,              -- ID de l'événement
    ip_address VARCHAR(45),       -- Adresse IP du visiteur
    user_agent TEXT,              -- Navigateur/appareil
    device_type VARCHAR(20),      -- mobile/desktop/tablet
    scanned_at TIMESTAMP,         -- Date et heure du scan
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Exemple d'enregistrement:**
```json
{
  "id": 245,
  "event_id": 1,
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)...",
  "device_type": "mobile",
  "scanned_at": "2026-02-18T14:30:00Z"
}
```

---

## 🎨 Personnalisation du QR Code

### Ajouter un Logo

Vous pouvez ajouter votre logo au centre du QR code:

```javascript
qrcodeInstance = new QRCode(qrcodeDiv, {
    text: url,
    width: 300,
    height: 300,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H, // High = 30% de redondance
    // Permet d'ajouter un logo sans perdre la lisibilité
});
```

### Couleurs Personnalisées

```javascript
qrcodeInstance = new QRCode(qrcodeDiv, {
    text: url,
    width: 300,
    height: 300,
    colorDark: "#1E40AF",    // Bleu foncé
    colorLight: "#EFF6FF",   // Bleu très clair
    correctLevel: QRCode.CorrectLevel.H
});
```

---

## 📈 Métriques Clés (KPIs)

### 1. Taux de Scan
```
Nombre de scans / Nombre de QR codes distribués
```

**Exemple:**
- 1000 flyers distribués
- 78 scans enregistrés
- Taux de scan: 7.8%

### 2. Taux de Conversion
```
Nombre d'inscriptions / Nombre de scans
```

**Exemple:**
- 245 scans
- 89 inscriptions
- Taux de conversion: 36%

### 3. Appareil Préféré
```
Scans mobile / Total scans
```

**Exemple:**
- 180 scans mobile sur 245 total
- 73% des scans sont sur mobile
- → Optimisez pour mobile!

### 4. Pics d'Activité
```
Identifier les jours/heures avec le plus de scans
```

**Exemple:**
- Vendredi: 52 scans (pic de la semaine)
- Mercredi: 45 scans
- → Postez sur les réseaux sociaux le mercredi/vendredi

---

## 🔒 Sécurité & Confidentialité

### Données Anonymes

Le système n'enregistre PAS:
- ❌ Nom de la personne
- ❌ Email
- ❌ Numéro de téléphone
- ❌ Localisation GPS précise

Le système enregistre SEULEMENT:
- ✅ Adresse IP (anonymisée)
- ✅ Type d'appareil (mobile/desktop/tablet)
- ✅ Date et heure du scan
- ✅ Navigateur utilisé

### Conformité RGPD

Les données collectées sont:
- Anonymes
- Utilisées uniquement pour des statistiques
- Conservées pendant 6 mois maximum
- Supprimées automatiquement après l'événement

---

## 🛠️ Maintenance

### Nettoyage Automatique

Ajoutez un cron job pour nettoyer les vieux scans:

```sql
-- Supprimer les scans de plus de 6 mois
DELETE FROM event_scans 
WHERE scanned_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

### Backup des Statistiques

```bash
# Exporter les statistiques avant nettoyage
mysqldump -u user -p database event_scans > event_scans_backup.sql
```

---

## 🎯 Bonnes Pratiques

### 1. Taille du QR Code

**Minimum recommandé:**
- Affiche A4: 3cm x 3cm
- Affiche A3: 5cm x 5cm
- Billboard: 20cm x 20cm

**Règle générale:**
```
Taille QR = Distance de scan / 10
```

**Exemples:**
- Scan à 50cm → QR de 5cm
- Scan à 2m → QR de 20cm

### 2. Contraste

- ✅ Noir sur blanc (meilleur)
- ✅ Bleu foncé sur blanc
- ⚠️ Couleurs claires (moins lisible)
- ❌ Jaune sur blanc (illisible)

### 3. Emplacement

**Sur une affiche:**
- En bas à droite
- Bien visible
- Pas trop petit
- Avec un appel à l'action

**Appels à l'action efficaces:**
- ✅ "Scannez pour vous inscrire"
- ✅ "Scannez pour plus d'infos"
- ✅ "Inscription rapide via QR code"
- ❌ "QR code" (pas assez incitatif)

### 4. Test Avant Distribution

**Checklist:**
- [ ] Scanner le QR code avec plusieurs téléphones
- [ ] Vérifier que l'URL est correcte
- [ ] Vérifier que le scan est enregistré
- [ ] Tester sur iOS et Android
- [ ] Vérifier la lisibilité à distance

---

## 📱 Exemple Complet

### Campagne Marketing Complète

**Objectif:** Promouvoir l'événement "Le trouble du spectre autistique"

**Actions:**
1. Créer le QR code avec `qr-code-event-scan.html`
2. Imprimer 500 flyers avec le QR code
3. Distribuer dans 5 quartiers différents
4. Poster sur Facebook/Instagram avec le QR code
5. Afficher sur 3 panneaux publicitaires

**Suivi:**
```
Semaine 1:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Source          Scans    Inscriptions    Taux
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Flyers          78       28              36%
Facebook        145      52              36%
Instagram       89       34              38%
Panneaux        34       12              35%
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TOTAL           346      126             36%

Insights:
→ Instagram a le meilleur taux de conversion (38%)
→ Facebook génère le plus de scans (145)
→ Les panneaux sont moins efficaces (34 scans)

Actions:
→ Augmenter le budget Instagram
→ Améliorer le design des panneaux
→ Continuer la distribution de flyers
```

---

## 🆘 Dépannage

### Le scan n'est pas enregistré

**Vérifications:**
1. Le paramètre `?qr=true` est-il dans l'URL?
2. L'API `/api/events/{slug}/scan` fonctionne-t-elle?
3. Y a-t-il des erreurs dans la console du navigateur?
4. Le backend est-il accessible?

**Solution:**
```bash
# Tester l'API manuellement
curl -X POST https://votre-api.com/api/events/mon-evenement/scan

# Vérifier les logs backend
tail -f storage/logs/laravel.log
```

### Le QR code ne scanne pas

**Vérifications:**
1. Le QR code est-il assez grand?
2. Le contraste est-il suffisant?
3. L'URL est-elle correcte?
4. Le QR code est-il endommagé?

**Solution:**
- Régénérer le QR code
- Augmenter la taille
- Utiliser noir sur blanc
- Tester avec plusieurs téléphones

---

## 📚 Ressources

### Fichiers Importants

- `qr-code-event-scan.html` - Générateur QR production
- `qr-code-generator-local.html` - Générateur QR local
- `EventDetailPage.tsx` - Page détails événement
- `EventInscriptionPage-v2.tsx` - Page inscription
- `EventScanController.php` - API backend
- `GUIDE_QR_CODES.md` - Guide complet des 2 types de QR

### Documentation API

- `API_DOCUMENTATION.md` - Tous les endpoints
- `QR_SCAN_SYSTEM_GUIDE.md` - Système scan billets

---

## ✅ Résumé

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  SCAN QR CODE ÉVÉNEMENT                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                         │
│  1. Générer QR code avec ?qr=true                       │
│  2. Distribuer (affiches, réseaux, flyers)             │
│  3. Les scans sont enregistrés automatiquement          │
│  4. Voir les stats dans le dashboard admin              │
│  5. Analyser et optimiser vos campagnes                 │
│                                                         │
│  📊 Métriques:                                          │
│  • Nombre de scans                                      │
│  • Type d'appareil                                      │
│  • Taux de conversion                                   │
│  • Évolution dans le temps                              │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

**Dernière mise à jour:** 18 Février 2026
