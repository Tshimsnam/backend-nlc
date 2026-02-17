# Instructions pour le Flux Simplifié

## Changements à apporter manuellement

Le fichier `EventInscriptionPage.tsx` est trop complexe pour être modifié automatiquement. Voici les changements à faire :

### 1. Supprimer les variables inutiles

```typescript
// SUPPRIMER cette ligne :
const [paymentMode, setPaymentMode] = useState<'online' | 'cash' | null>(null);
const [paymentModes, setPaymentModes] = useState<PaymentMode[]>([]);

// SUPPRIMER aussi :
const modesRes = await axios.get(`${API_URL}/events/${eventData.id}/tickets/payment-modes`);
setPaymentModes(modesRes.data);
```

### 2. Modifier le nombre d'étapes

```typescript
const totalSteps = 4; // Au lieu de 5
```

### 3. Supprimer toute l'étape 3 (Mode de paiement)

Supprimer tout le bloc entre :
```typescript
{/* ÉTAPE 3: Mode de paiement */}
// ... jusqu'à ...
)}
```

### 4. Renommer l'étape 4 en étape 3 (Confirmation)

```typescript
{/* ÉTAPE 3: Confirmation */}
{step === 3 && (
  // ... contenu de confirmation sans la carte "Mode de paiement"
)}
```

### 5. Créer la nouvelle étape 4 (Billet + Instructions)

Remplacer tout le contenu de l'étape 5 par :

```typescript
{/* ÉTAPE 4: Billet avec Instructions de Paiement */}
{step === 4 && ticketData && (
  <motion.div className="space-y-8">
    {/* En-tête avec référence */}
    <div className="text-center">
      <CheckCircle className="w-10 h-10 text-green-600" />
      <h2>Inscription réussie !</h2>
      <p>Votre référence : <span className="font-mono font-bold text-2xl">{ticketData.reference}</span></p>
      <p>Choisissez votre mode de paiement ci-dessous</p>
    </div>

    {/* Grille avec 2 cartes côte à côte */}
    <div className="grid md:grid-cols-2 gap-6">
      {/* Carte M-Pesa */}
      <div className="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-3xl p-8">
        <h3 className="text-2xl font-bold text-green-900">M-Pesa</h3>
        <div className="space-y-3">
          <div>1. Composez *1122#</div>
          <div>2. Choisissez 5 - Mes paiements</div>
          <div>3. Entrez 097435</div>
          <div>4. Entrez {ticketData.amount} {ticketData.currency}</div>
          <div>5. Validez avec PIN</div>
        </div>
      </div>

      {/* Carte Orange Money */}
      <div className="bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-300 rounded-3xl p-8">
        <h3 className="text-2xl font-bold text-orange-900">Orange Money</h3>
        <div className="space-y-3">
          <div>1. Composez #144#</div>
          <div>2. Paiement marchand</div>
          <div>3. Numéro marchand [À VENIR]</div>
          <div>4. Entrez {ticketData.amount} {ticketData.currency}</div>
          <div>5. Validez avec PIN</div>
        </div>
      </div>
    </div>

    {/* Billet avec QR code (garder l'existant) */}
    <div id="ticket-to-download">
      {/* ... code du billet existant ... */}
    </div>

    {/* Boutons (garder l'existant) */}
    <div className="grid sm:grid-cols-2 gap-4">
      <Button onClick={printTicket}>Imprimer</Button>
      <Button onClick={downloadTicketPDF}>Télécharger</Button>
    </div>
  </motion.div>
)}
```

### 6. Modifier handleSubmit

```typescript
const handleSubmit = async () => {
  // ... code existant ...
  
  if (res.data.success) {
    setTicketData(res.data.ticket);
    setQrData(qrInfo);
    setStep(4); // Aller directement à l'étape 4
  }
};
```

### 7. Modifier la barre de progression

```typescript
<div className="flex justify-between mt-4">
  {[1, 2, 3, 4].map((s) => (
    <div>
      <div className={step >= s ? "active" : ""}>{s}</div>
      <span>
        {s === 1 && "Tarif"}
        {s === 2 && "Infos"}
        {s === 3 && "Confirmation"}
        {s === 4 && "Billet"}
      </span>
    </div>
  ))}
</div>
```

---

## Résultat final

Le flux devient :

1. **Étape 1** : Choisir le tarif
2. **Étape 2** : Remplir les informations
3. **Étape 3** : Confirmer (sans sélection de mode de paiement)
4. **Étape 4** : Voir la référence + 2 cartes (M-Pesa et Orange Money) + Billet avec QR code

---

## Alternative : Fichier complet

Si c'est trop complexe, je peux créer un fichier `EventInscriptionPage-simplifie.tsx` complet avec le nouveau flux.

Voulez-vous que je crée ce fichier complet ?
