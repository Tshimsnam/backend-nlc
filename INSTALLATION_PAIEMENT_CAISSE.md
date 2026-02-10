# Installation du système de paiement en caisse

## ✅ Backend - TERMINÉ

Le backend est déjà configuré avec:
- Mode de paiement "cash" ajouté
- Génération de QR code avec référence du ticket
- Endpoints pour validation admin
- Status "pending_cash" pour les tickets en attente

### Routes API disponibles:
- `POST /api/events/{event}/register` - Créer un ticket (en ligne ou caisse)
- `GET /api/payment-modes` - Liste des modes de paiement (inclut "cash")
- `GET /api/tickets/pending-cash` - Liste des tickets en attente (admin)
- `POST /api/tickets/{reference}/validate-cash` - Valider un paiement (admin)

## 📦 Frontend - À installer

### 1. Installer la bibliothèque QR Code
```bash
cd frontend
npm install qrcode.react
```

### 2. Mettre à jour EventInscriptionPage.tsx

#### A. Ajouter l'import QRCodeSVG (ligne ~11)
```typescript
import { QRCodeSVG } from 'qrcode.react';
import { CheckCircle, Printer, Download } from "lucide-react";
```

#### B. Ajouter les nouveaux états (après les états existants, ligne ~60)
```typescript
// NOUVEAUX ÉTATS pour paiement en caisse
const [qrData, setQrData] = useState<string | null>(null);
const [ticketData, setTicketData] = useState<any>(null);
const [paymentMode, setPaymentMode] = useState<'online' | 'cash' | null>(null);
```

#### C. Remplacer la fonction handleSubmit (ligne ~140)
Copier le contenu de `EventInscriptionPage_PART3_HandleSubmit.tsx`

#### D. Ajouter l'étape 5 pour le QR Code (après l'étape 4, ligne ~450)
Copier le contenu de `EventInscriptionPage_PART4_Step5_QRCode.tsx`

#### E. Mettre à jour l'indicateur d'étapes (ligne ~220)
Ajouter après l'étape 4:
```typescript
{paymentMode === 'cash' && (
  <>
    <div className="w-8 md:w-12 h-0.5 bg-muted"></div>
    <div className={`flex items-center gap-2 ${step >= 5 ? "text-accent" : "text-muted-foreground"}`}>
      <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${step >= 5 ? "bg-accent text-white" : "bg-muted"}`}>
        5
      </div>
      <span className="text-xs md:text-sm font-medium whitespace-nowrap">QR Code</span>
    </div>
  </>
)}
```

## 🧪 Test du système

### Test paiement en caisse:
1. Aller sur http://192.168.241.9:8080/evenements
2. Sélectionner un événement
3. Cliquer sur "S'inscrire"
4. Choisir un tarif
5. Remplir les informations
6. Sélectionner "Paiement en caisse"
7. Confirmer
8. ✅ Vous devriez voir le QR code avec la référence du ticket

### Test paiement en ligne (MaxiCash):
1. Même processus mais sélectionner Mobile Money / Carte / PayPal
2. ✅ Vous devriez être redirigé vers MaxiCash

## 📋 Prochaines étapes (interface admin)

Créer une page admin pour:
1. Scanner ou entrer la référence du ticket
2. Afficher les informations du ticket
3. Valider le paiement en caisse
4. Marquer le ticket comme "completed"

Cette interface utilisera:
- `GET /api/tickets/pending-cash` - Liste des tickets en attente
- `POST /api/tickets/{reference}/validate-cash` - Valider le paiement

## 📁 Fichiers créés

- `CASH_PAYMENT_UPDATES.md` - Documentation complète
- `EventInscriptionPage_PART1.tsx` - Imports et types
- `EventInscriptionPage_PART2_States.tsx` - États du composant
- `EventInscriptionPage_PART3_HandleSubmit.tsx` - Fonction handleSubmit modifiée
- `EventInscriptionPage_PART4_Step5_QRCode.tsx` - Étape 5 avec QR code
- `INSTALLATION_PAIEMENT_CAISSE.md` - Ce fichier

## ⚠️ Important

Le backend est déjà prêt et fonctionnel. Il suffit de:
1. Installer `qrcode.react` dans le frontend
2. Mettre à jour `EventInscriptionPage.tsx` avec les modifications ci-dessus
3. Tester le flux complet

Le mode "Paiement en caisse" apparaîtra automatiquement dans la liste des modes de paiement.
