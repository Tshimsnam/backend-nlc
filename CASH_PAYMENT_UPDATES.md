# Modifications pour le paiement en caisse

## Backend - Terminé ✅

### 1. PaymentStatus Enum
- Ajouté `PendingCash = 'pending_cash'` pour les paiements en attente de validation en caisse

### 2. TicketController
- Ajouté mode de paiement "cash" dans `paymentModes()`
- Modifié `store()` pour gérer le paiement en caisse (retourne QR code au lieu de rediriger vers MaxiCash)
- Ajouté `validateCashPayment()` pour valider un paiement en caisse (admin uniquement)
- Ajouté `pendingCashPayments()` pour lister tous les tickets en attente de paiement en caisse

### 3. Routes API
- `GET /api/tickets/pending-cash` - Liste des tickets en attente (admin)
- `POST /api/tickets/{ticketNumber}/validate-cash` - Valider un paiement en caisse (admin)

## Frontend - À faire

### Modifications dans EventInscriptionPage.tsx

#### 1. Ajouter l'état pour le QR code et le mode de paiement
```typescript
const [qrData, setQrData] = useState<string | null>(null);
const [ticketData, setTicketData] = useState<any>(null);
const [paymentMode, setPaymentMode] = useState<'online' | 'cash' | null>(null);
```

#### 2. Modifier handleSubmit pour gérer les deux modes
```typescript
const handleSubmit = async () => {
  if (!validateStep2() || !validateStep3() || !event) return;

  setSubmitting(true);
  setError(null);

  try {
    const baseUrl = window.location.origin;
    const payload = {
      ...formData,
      success_url: `${baseUrl}/paiement/success`,
      cancel_url: `${baseUrl}/paiement/cancel`,
      failure_url: `${baseUrl}/paiement/failure`,
    };

    const res = await axios.post(
      `${API_URL}/events/${event.id}/register`,
      payload
    );

    if (res.data.success) {
      if (res.data.payment_mode === 'cash') {
        // Paiement en caisse - afficher QR code
        setPaymentMode('cash');
        setTicketData(res.data.ticket);
        setQrData(res.data.ticket.qr_data);
        setStep(5); // Nouvelle étape pour afficher le QR code
      } else if (res.data.redirect_url) {
        // Paiement en ligne - rediriger vers MaxiCash
        window.location.href = res.data.redirect_url;
      }
    } else {
      setError(res.data.message || "Erreur lors de l'inscription");
    }
  } catch (err: any) {
    const errorMsg = err.response?.data?.message || "Erreur lors de l'inscription";
    setError(errorMsg);
  } finally {
    setSubmitting(false);
  }
};
```

#### 3. Ajouter l'étape 5 pour afficher le QR code
```typescript
{/* ÉTAPE 5: QR Code pour paiement en caisse */}
{step === 5 && paymentMode === 'cash' && ticketData && (
  <div className="space-y-6">
    <div className="text-center">
      <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
        <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h2 className="text-2xl font-bold mb-2">Inscription enregistrée !</h2>
      <p className="text-muted-foreground">
        Présentez ce QR code à la caisse pour finaliser votre paiement
      </p>
    </div>

    {/* QR Code */}
    <div className="flex justify-center">
      <div className="bg-white p-6 rounded-xl border-2 border-accent">
        <QRCodeSVG 
          value={qrData || ''} 
          size={256}
          level="H"
          includeMargin={true}
        />
      </div>
    </div>

    {/* Informations du ticket */}
    <div className="space-y-4">
      <div className="bg-secondary p-4 rounded-xl">
        <h3 className="font-semibold mb-2">Référence du ticket</h3>
        <p className="text-2xl font-mono font-bold text-accent">{ticketData.reference}</p>
      </div>

      <div className="bg-secondary p-4 rounded-xl">
        <h3 className="font-semibold mb-2">Montant à payer</h3>
        <p className="text-3xl font-bold text-accent">
          {ticketData.amount} {ticketData.currency}
        </p>
      </div>

      <div className="bg-secondary p-4 rounded-xl">
        <h3 className="font-semibold mb-2">Participant</h3>
        <p className="text-muted-foreground">{ticketData.full_name}</p>
        <p className="text-sm text-muted-foreground">{ticketData.email}</p>
        <p className="text-sm text-muted-foreground">{ticketData.phone}</p>
      </div>

      <div className="bg-secondary p-4 rounded-xl">
        <h3 className="font-semibold mb-2">Événement</h3>
        <p className="text-muted-foreground">{ticketData.event}</p>
        <p className="text-sm text-muted-foreground">Catégorie: {ticketData.category}</p>
      </div>
    </div>

    {/* Instructions */}
    <div className="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
      <h4 className="font-semibold mb-2">📋 Instructions</h4>
      <ol className="text-sm space-y-1 list-decimal list-inside">
        <li>Présentez ce QR code à la caisse</li>
        <li>Effectuez le paiement de {ticketData.amount} {ticketData.currency}</li>
        <li>Votre ticket sera validé immédiatement</li>
        <li>Vous recevrez une confirmation par email</li>
      </ol>
    </div>

    {/* Boutons d'action */}
    <div className="flex flex-col gap-3">
      <Button 
        onClick={() => window.print()} 
        variant="outline" 
        className="gap-2"
      >
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Imprimer le ticket
      </Button>
      
      <Button 
        onClick={() => {
          const canvas = document.querySelector('canvas');
          if (canvas) {
            const url = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = `ticket-${ticketData.reference}.png`;
            link.href = url;
            link.click();
          }
        }}
        variant="outline"
        className="gap-2"
      >
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Télécharger le QR code
      </Button>

      <Link to="/evenements">
        <Button className="w-full">
          Retour aux événements
        </Button>
      </Link>
    </div>
  </div>
)}
```

#### 4. Installer la bibliothèque QR Code
```bash
npm install qrcode.react
```

#### 5. Importer QRCodeSVG
```typescript
import { QRCodeSVG } from 'qrcode.react';
```

#### 6. Mettre à jour l'indicateur d'étapes (ajouter étape 5 si paiement en caisse)
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

## Test du flux

### Paiement en ligne (MaxiCash)
1. Sélectionner un tarif
2. Remplir les informations
3. Choisir Mobile Money / Carte / PayPal / MaxiCash
4. Confirmer
5. Redirection vers MaxiCash

### Paiement en caisse
1. Sélectionner un tarif
2. Remplir les informations
3. Choisir "Paiement en caisse"
4. Confirmer
5. Affichage du QR code avec référence du ticket
6. Imprimer ou télécharger le QR code
7. Se présenter à la caisse avec le QR code

### Validation admin (à créer plus tard)
1. Scanner le QR code ou entrer la référence
2. Vérifier les informations
3. Confirmer le paiement
4. Le ticket passe de "pending_cash" à "completed"
