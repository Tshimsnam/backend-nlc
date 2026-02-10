// PARTIE 4: ÉTAPE 5 - Affichage du QR Code (à ajouter après l'étape 4)

{/* ÉTAPE 5: QR Code pour paiement en caisse */}
{step === 5 && paymentMode === 'cash' && ticketData && (
  <div className="space-y-6">
    <div className="text-center">
      <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
        <CheckCircle className="w-8 h-8 text-green-600" />
      </div>
      <h2 className="text-2xl font-bold mb-2">Inscription enregistrée !</h2>
      <p className="text-muted-foreground">
        Présentez ce QR code à la caisse pour finaliser votre paiement
      </p>
    </div>

    {/* QR Code */}
    <div className="flex justify-center">
      <div className="bg-white p-6 rounded-xl border-2 border-accent shadow-lg">
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
        <Printer className="w-4 h-4" />
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
        <Download className="w-4 h-4" />
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
