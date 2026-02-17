# Système de Tracking QR Code - Documentation

## 🎯 Fonctionnalité

Système pour générer des QR codes pour les événements et tracker le nombre de personnes qui scannent le code.

## 📊 Base de Données

### Table: event_scans
```sql
- id
- event_id (foreign key vers events)
- ip_address
- user_agent
- device_type (mobile, desktop, tablet)
- scanned_at
- timestamps
```

## 🔗 API Endpoints

### 1. Enregistrer un Scan
```
POST /api/events/{slug}/scan
```

**Paramètres:**
- `slug`: Le slug de l'événement

**Réponse:**
```json
{
  "success": true,
  "message": "Scan enregistré avec succès",
  "event": {
    "title": "Le trouble du spectre autistique...",
    "slug": "le-trouble-du-spectre-autistique-et-la-scolarite"
  }
}
```

### 2. Obtenir les Statistiques
```
GET /api/events/{slug}/scans
```

**Réponse:**
```json
{
  "event": {...},
  "total_scans": 150,
  "scans_by_device": [
    {"device_type": "mobile", "count": 100},
    {"device_type": "desktop", "count": 45},
    {"device_type": "tablet", "count": 5}
  ],
  "recent_scans": [...]
}
```

## 📱 Génération du QR Code

### URL à Encoder
```
https://www.nlcrdc.org/evenements/le-trouble-du-spectre-autistique-et-la-scolarite
```

### Générateur en Ligne
Utiliser: https://www.qr-code-generator.com/

### Code React (Frontend)
```tsx
import { QRCodeSVG } from 'qrcode.react';

<QRCodeSVG 
  value="https://www.nlcrdc.org/evenements/le-trouble-du-spectre-autistique-et-la-scolarite"
  size={256}
  level="H"
  includeMargin={true}
/>
```

## 🔄 Flux de Tracking

### 1. Utilisateur Scanne le QR Code
- QR code contient l'URL de l'événement
- Redirige vers la page de détail de l'événement

### 2. Page de Détail Charge
```tsx
useEffect(() => {
  // Enregistrer le scan
  axios.post(`${API_URL}/events/${slug}/scan`)
    .then(() => console.log('Scan enregistré'))
    .catch(err => console.error(err));
}, [slug]);
```

### 3. Backend Enregistre
- IP address
- User agent
- Type d'appareil (mobile/desktop/tablet)
- Timestamp

### 4. Dashboard Admin Affiche
- Total des scans
- Scans par appareil
- Scans récents

## 📊 Dashboard Admin

### Nouvelle Carte de Statistiques
```blade
<!-- Scans QR Code -->
<div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
        </div>
        <span class="text-sm text-gray-500">QR Scans</span>
    </div>
    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_qr_scans'] }}</h3>
    <p class="text-sm text-gray-600 mt-1">Scans totaux</p>
</div>
```

## 🎨 Intégration Frontend

### EventDetailPage.tsx
```tsx
import { useEffect } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';

const EventDetailPage = () => {
  const { slug } = useParams();
  const API_URL = import.meta.env.VITE_API_URL;

  useEffect(() => {
    // Enregistrer le scan quand la page charge
    const recordScan = async () => {
      try {
        await axios.post(`${API_URL}/events/${slug}/scan`);
      } catch (error) {
        console.error('Erreur lors de l\'enregistrement du scan:', error);
      }
    };

    recordScan();
  }, [slug]);

  // ... reste du composant
};
```

## 🔧 Installation

### 1. Installer le Package Agent (Détection d'appareil)
```bash
composer require jenssegers/agent
```

### 2. Exécuter la Migration
```bash
php artisan migrate
```

### 3. Nettoyer le Cache
```bash
php artisan route:clear
php artisan config:clear
```

## 📈 Statistiques Disponibles

### Dashboard Admin
- **Total Scans**: Nombre total de scans QR
- **Scans par Appareil**: Mobile, Desktop, Tablet
- **Scans Récents**: 10 derniers scans avec détails
- **Scans par Événement**: Statistiques par événement

### API
- Total des scans
- Répartition par type d'appareil
- Historique des scans
- Adresses IP (pour détecter les doublons)

## 🔒 Sécurité

### Protection contre les Abus
- Enregistrement de l'IP
- Détection du User Agent
- Possibilité de filtrer les doublons

### Anonymisation
- Pas de données personnelles stockées
- Seulement IP et User Agent
- Conformité RGPD

## 🧪 Tests

### Test Enregistrement Scan
```bash
curl -X POST http://localhost:8000/api/events/le-trouble-du-spectre-autistique-et-la-scolarite/scan \
  -H "Content-Type: application/json"
```

### Test Récupération Stats
```bash
curl -X GET http://localhost:8000/api/events/le-trouble-du-spectre-autistique-et-la-scolarite/scans
```

## 📱 QR Code pour l'Événement

### URL
```
https://www.nlcrdc.org/evenements/le-trouble-du-spectre-autistique-et-la-scolarite
```

### Générer le QR Code
1. Aller sur https://www.qr-code-generator.com/
2. Coller l'URL ci-dessus
3. Choisir la taille (recommandé: 300x300px minimum)
4. Télécharger en PNG ou SVG
5. Imprimer ou partager

### Utilisation
- Afficher sur des affiches
- Partager sur les réseaux sociaux
- Inclure dans les emails
- Imprimer sur des flyers

## 📊 Exemple de Données

### Scan Enregistré
```json
{
  "id": 1,
  "event_id": 5,
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)",
  "device_type": "mobile",
  "scanned_at": "2026-02-17 18:30:00"
}
```

### Statistiques
```json
{
  "total_scans": 150,
  "mobile": 100,
  "desktop": 45,
  "tablet": 5
}
```

## 🚀 Prochaines Étapes

- [ ] Ajouter graphique d'évolution des scans
- [ ] Filtrer les scans par période
- [ ] Export des données en CSV
- [ ] Détection des doublons (même IP)
- [ ] Géolocalisation des scans
- [ ] Notifications en temps réel

---

**Date:** Février 2026

**Statut:** ✅ SYSTÈME COMPLET
