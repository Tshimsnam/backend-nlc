// PARTIE 3: Fonction handleSubmit MODIFIÉE pour gérer les deux modes de paiement

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
      } else {
        setError(res.data.message || "Erreur lors de l'inscription");
      }
    } else {
      setError(res.data.message || "Erreur lors de l'inscription");
    }
  } catch (err: any) {
    const errorMsg = err.response?.data?.message || "Erreur lors de l'inscription";
    const ticketData = err.response?.data?.ticket;
    
    if (ticketData) {
      setError(
        `${errorMsg}\n\n` +
        `Référence: ${ticketData.reference}\n` +
        `Montant: ${ticketData.amount} ${ticketData.currency}\n\n` +
        `Note: Votre inscription a été enregistrée mais le paiement n'a pas pu être initié. ` +
        `Veuillez contacter le support avec la référence ci-dessus.`
      );
    } else {
      setError(errorMsg);
    }
  } finally {
    setSubmitting(false);
  }
};
