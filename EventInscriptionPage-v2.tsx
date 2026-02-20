import { useEffect, useState } from "react";
import { useParams, useLocation, Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ArrowLeft, ArrowRight, CheckCircle, Download, Calendar, MapPin, Ticket, User, Mail, Phone } from "lucide-react";
import { QRCodeSVG } from 'qrcode.react';
import axios from "axios";
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import { motion, AnimatePresence } from "framer-motion";

// Types
interface EventPrice {
  id: number;
  event_id: number;
  category: string;
  duration_type: string;
  amount: string;
  currency: string;
  label: string;
  description?: string;
}

interface Event {
  id: number;
  title: string;
  slug: string;
  description: string;
  full_description?: string;
  date: string;
  end_date?: string;
  time: string;
  end_time?: string;
  location: string;
  venue_details?: string;
  image: string;
  agenda?: Array<{
    day: string;
    time: string;
    activities: string;
  }>;
  capacity?: number;
  registered?: number;
  contact_phone?: string;
  contact_email?: string;
  organizer?: string;
  registration_deadline?: string;
  sponsors?: string[];
  event_prices: EventPrice[];
}

interface RegistrationFormData {
  event_price_id: number;
  full_name: string;
  email: string;
  phone: string;
  days: number;
  pay_type: string;
}

const API_URL = import.meta.env.VITE_API_URL;

const EventInscriptionPage = () => {
  const { slug } = useParams();
  const location = useLocation();
  
  const [event, setEvent] = useState<Event | null>(null);
  const [selectedPrice, setSelectedPrice] = useState<EventPrice | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [step, setStep] = useState(1);
  const [submitting, setSubmitting] = useState(false);
  const [qrData, setQrData] = useState<string | null>(null);
  const [ticketData, setTicketData] = useState<any>(null);
  const [paymentMode, setPaymentMode] = useState<'online' | 'cash' | null>(null);

  const [formData, setFormData] = useState<RegistrationFormData>({
    event_price_id: 0,
    full_name: "",
    email: "",
    phone: "",
    days: 1,
    pay_type: "cash",
  });

  const totalSteps = 3; // 3 étapes: Tarif, Infos, Paiement
  const progress = (step / totalSteps) * 100;

  // Fonction pour télécharger le billet en PDF
  const downloadTicketPDF = async () => {
    const ticketElement = document.getElementById('ticket-to-download');
    if (!ticketElement) {
      alert('Élément du billet introuvable. Veuillez réessayer.');
      return;
    }

    try {
      // Attendre que tout soit bien rendu
      await new Promise(resolve => setTimeout(resolve, 500));

      const canvas = await html2canvas(ticketElement, {
        scale: 2,
        backgroundColor: '#ffffff',
        logging: false,
        useCORS: true,
        allowTaint: false,
        imageTimeout: 0,
        removeContainer: true,
      });

      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4',
      });

      const imgWidth = 190;
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      const x = (pdf.internal.pageSize.getWidth() - imgWidth) / 2;
      const y = 10;

      pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
      pdf.save(`billet-${ticketData?.reference || 'ticket'}.pdf`);
    } catch (error) {
      console.error('Erreur lors de la génération du PDF:', error);
      alert('Erreur lors de la génération du PDF. Veuillez utiliser l\'impression à la place.');
    }
  };

  // Fonction pour imprimer le billet (capture d'écran)
  const printTicket = async () => {
    const ticketElement = document.getElementById('ticket-to-download');
    if (!ticketElement) {
      alert('Élément du billet introuvable.');
      return;
    }

    try {
      await new Promise(resolve => setTimeout(resolve, 300));
      
      const canvas = await html2canvas(ticketElement, {
        scale: 3, // Haute qualité pour l'impression
        backgroundColor: '#ffffff',
        logging: false,
        useCORS: true,
      });

      // Télécharger directement l'image
      const imgData = canvas.toDataURL('image/png');
      const link = document.createElement('a');
      link.download = `billet-${ticketData?.reference || 'ticket'}.png`;
      link.href = imgData;
      link.click();
    } catch (error) {
      console.error('Erreur lors de la capture:', error);
      alert('Erreur lors de la génération de l\'image.');
    }
  };

  // Charger l'événement
  useEffect(() => {
    if (!slug) return;

    const fetchData = async () => {
      try {
        const eventRes = await axios.get(`${API_URL}/events/${slug}`);
        const eventData = eventRes.data;
        setEvent(eventData);

        const priceId = location.state?.priceId;
        if (priceId && eventData.event_prices) {
          const price = eventData.event_prices.find((p: EventPrice) => p.id === priceId);
          if (price) {
            setSelectedPrice(price);
            setFormData((prev) => ({ ...prev, event_price_id: price.id }));
          }
        }

        // Enregistrer le scan de l'événement si l'utilisateur vient d'un QR code
        const urlParams = new URLSearchParams(window.location.search);
        const fromQR = urlParams.get('qr') === 'true' || urlParams.get('from') === 'qr';
        
        if (fromQR) {
          try {
            await axios.post(`${API_URL}/events/${slug}/scan`);
            console.log('✅ Scan événement enregistré');
          } catch (scanErr) {
            console.error('Erreur lors de l\'enregistrement du scan:', scanErr);
            // Ne pas bloquer l'utilisateur si le scan échoue
          }
        }
      } catch (err) {
        console.error("Erreur lors du chargement:", err);
        setError("Impossible de charger les données");
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [slug, location.state]);

  const handleInputChange = (field: keyof RegistrationFormData, value: string | number) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const handlePriceChange = (priceId: number) => {
    const price = event?.event_prices.find((p) => p.id === priceId);
    if (price) {
      setSelectedPrice(price);
      setFormData((prev) => ({ ...prev, event_price_id: priceId }));
    }
  };

  const validateStep1 = () => formData.event_price_id > 0 && selectedPrice;
  const validateStep2 = () => formData.full_name && formData.email && formData.phone;
  const validateStep3 = () => !!formData.pay_type;

  const handleNextStep = () => {
    if (step === 1 && validateStep1()) {
      setStep(2);
      setTimeout(() => {
        const firstInput = document.getElementById('full_name');
        if (firstInput) firstInput.focus();
      }, 100);
    }
    else if (step === 2 && validateStep2()) setStep(3);
    else if (step === 3 && validateStep3()) {
      // Passer directement à la soumission
      handleSubmit();
    }
  };

  const handleSubmit = async () => {
    if (!validateStep2() || !event) return;

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

      const res = await axios.post(`${API_URL}/events/${event.id}/register`, payload);

      if (res.data.success) {
        if (res.data.payment_mode === 'cash') {
          // Paiement en caisse - afficher le billet avec QR code
          setPaymentMode('cash');
          setTicketData(res.data.ticket);
          
          // Créer le QR code avec les informations essentielles pour la validation
          const qrInfo = JSON.stringify({
            reference: res.data.ticket.reference,
            event_id: event.id,
            amount: res.data.ticket.amount,
            currency: res.data.ticket.currency,
            payment_mode: 'cash'
          });
          
          console.log('QR Data:', qrInfo); // Debug
          setQrData(qrInfo);
          setStep(5); // Étape 5 pour afficher le billet
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
      setError(errorMsg);
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <main className="min-h-screen flex items-center justify-center">
        <motion.div
          animate={{ rotate: 360 }}
          transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
          className="w-12 h-12 border-4 border-accent border-t-transparent rounded-full"
        />
      </main>
    );
  }

  if (error && !event) {
    return (
      <main className="min-h-screen">
        <Header />
        <p className="text-center text-red-500 mt-20">{error}</p>
        <Footer />
      </main>
    );
  }

  if (!event) return null;

  const categoryLabels: Record<string, string> = {
    medecin: "Médecin",
    etudiant: "Étudiant",
    parent: "Parent",
    enseignant: "Enseignant",
  };

  const durationLabels: Record<string, string> = {
    per_day: "Par jour",
    full_event: "Événement complet",
  };

  return (
    <main className="min-h-screen bg-gradient-to-br from-secondary via-background to-secondary">
      <Header />

      <section className="pt-28 pb-16">
        <div className="container-custom max-w-5xl">
          {/* Breadcrumb */}
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="mb-8"
          >
            <Link to={`/evenements/${event.slug}`} className="text-accent hover:underline flex items-center gap-2 group">
              <ArrowLeft className="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
              Retour à l'événement
            </Link>
          </motion.div>

          {/* Titre */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="mb-8 text-center"
          >
            <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-2">Inscription</h1>
            <p className="text-muted-foreground text-sm md:text-base">{event.title}</p>
          </motion.div>

          {/* Barre de progression */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="mb-12"
          >
            <div className="relative">
              <div className="h-2 bg-secondary rounded-full overflow-hidden">
                <motion.div
                  className="h-full bg-gradient-to-r from-accent via-accent/80 to-accent rounded-full"
                  initial={{ width: 0 }}
                  animate={{ width: `${progress}%` }}
                  transition={{ duration: 0.5, ease: "easeInOut" }}
                />
              </div>
              <div className="flex justify-between mt-4">
                {[1, 2, 3].map((s) => (
                  <motion.div
                    key={s}
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    transition={{ delay: s * 0.1 }}
                    className="flex flex-col items-center"
                  >
                    <div
                      className={`w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-xs md:text-sm font-bold transition-all ${
                        step >= s
                          ? "bg-accent text-white shadow-lg scale-110"
                          : "bg-secondary text-muted-foreground"
                      }`}
                    >
                      {step > s ? <CheckCircle className="w-4 h-4 md:w-5 md:h-5" /> : s}
                    </div>
                    <span className="text-[10px] md:text-xs mt-2 text-center font-medium hidden sm:block">
                      {s === 1 && "Tarif"}
                      {s === 2 && "Infos"}
                      {s === 3 && "Paiement"}
                    </span>
                  </motion.div>
                ))}
              </div>
            </div>
          </motion.div>

          {/* Contenu */}
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="bg-background/80 backdrop-blur-sm rounded-3xl p-6 md:p-10 shadow-2xl border"
          >
            <AnimatePresence mode="wait">

              {/* ÉTAPE 1: Sélection du tarif */}
              {step === 1 && (
                <motion.div
                  key="step1"
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: -20 }}
                  className="space-y-6"
                >
                  <h2 className="text-2xl md:text-3xl font-bold mb-2">Choisissez votre tarif</h2>
                  <p className="text-muted-foreground mb-6">Sélectionnez le tarif qui correspond à votre profil</p>
                  <div className="grid gap-4">
                    {event.event_prices.map((price, index) => (
                      <motion.div
                        key={price.id}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.1 }}
                        onClick={() => handlePriceChange(price.id)}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                        className={`relative p-6 md:p-7 rounded-2xl border-2 cursor-pointer transition-all group ${
                          formData.event_price_id === price.id
                            ? "border-accent bg-gradient-to-br from-accent/10 via-accent/5 to-transparent shadow-xl shadow-accent/20 ring-2 ring-accent/30"
                            : "border-gray-200 dark:border-gray-700 hover:border-accent/50 hover:shadow-lg hover:bg-accent/5"
                        }`}
                      >
                        {formData.event_price_id === price.id && (
                          <motion.div
                            initial={{ scale: 0 }}
                            animate={{ scale: 1 }}
                            className="absolute top-4 right-4 w-8 h-8 bg-accent rounded-full flex items-center justify-center shadow-lg"
                          >
                            <CheckCircle className="w-5 h-5 text-white" />
                          </motion.div>
                        )}
                        
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pr-12 sm:pr-0">
                          <div className="flex-1">
                            <h3 className={`font-bold text-lg md:text-xl mb-2 transition-colors ${
                              formData.event_price_id === price.id ? "text-accent" : "group-hover:text-accent"
                            }`}>
                              {price.label}
                            </h3>
                            <div className="flex items-center gap-2 mb-2">
                              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent/10 text-accent">
                                {categoryLabels[price.category]}
                              </span>
                              <span className="text-xs text-muted-foreground">
                                {durationLabels[price.duration_type]}
                              </span>
                            </div>
                            {price.description && (
                              <p className="text-sm text-muted-foreground mt-2 leading-relaxed">{price.description}</p>
                            )}
                          </div>
                          <div className="text-right sm:ml-4">
                            <div className={`text-3xl md:text-4xl font-bold transition-colors ${
                              formData.event_price_id === price.id ? "text-accent" : "text-foreground group-hover:text-accent"
                            }`}>
                              {price.amount}
                              <span className="text-lg md:text-xl ml-1">{price.currency}</span>
                            </div>
                          </div>
                        </div>
                      </motion.div>
                    ))}
                  </div>
                  <div className="flex justify-end pt-4">
                    <Button onClick={handleNextStep} disabled={!validateStep1()} size="lg" className="gap-2">
                      Suivant
                      <ArrowRight className="w-4 h-4" />
                    </Button>
                  </div>
                </motion.div>
              )}

              {/* ÉTAPE 2: Informations personnelles */}
              {step === 2 && (
                <motion.div
                  key="step2"
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: -20 }}
                  className="space-y-6"
                >
                  <h2 className="text-2xl md:text-3xl font-bold mb-2">Vos informations</h2>
                  <p className="text-muted-foreground mb-6">Remplissez vos coordonnées pour finaliser l'inscription</p>
                  <div className="space-y-6">
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }}>
                      <Label htmlFor="full_name" className="text-base font-semibold flex items-center gap-2">
                        <User className="w-4 h-4 text-accent" />
                        Nom complet *
                      </Label>
                      <Input
                        id="full_name"
                        type="text"
                        value={formData.full_name}
                        onChange={(e) => handleInputChange("full_name", e.target.value)}
                        placeholder="Ex: Jean Dupont"
                        className="mt-2 h-14 text-base border-2 focus:border-accent focus:ring-2 focus:ring-accent/20 transition-all"
                        required
                      />
                    </motion.div>
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}>
                      <Label htmlFor="email" className="text-base font-semibold flex items-center gap-2">
                        <Mail className="w-4 h-4 text-accent" />
                        Email *
                      </Label>
                      <Input
                        id="email"
                        type="email"
                        value={formData.email}
                        onChange={(e) => handleInputChange("email", e.target.value)}
                        placeholder="votre@email.com"
                        className="mt-2 h-14 text-base border-2 focus:border-accent focus:ring-2 focus:ring-accent/20 transition-all"
                        required
                      />
                    </motion.div>
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }}>
                      <Label htmlFor="phone" className="text-base font-semibold flex items-center gap-2">
                        <Phone className="w-4 h-4 text-accent" />
                        Téléphone *
                      </Label>
                      <Input
                        id="phone"
                        type="tel"
                        value={formData.phone}
                        onChange={(e) => handleInputChange("phone", e.target.value)}
                        placeholder="+243 XXX XXX XXX"
                        className="mt-2 h-14 text-base border-2 focus:border-accent focus:ring-2 focus:ring-accent/20 transition-all"
                        required
                      />
                    </motion.div>
                  </div>
                  {error && (
                    <motion.div
                      initial={{ opacity: 0, scale: 0.95 }}
                      animate={{ opacity: 1, scale: 1 }}
                      className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl"
                    >
                      {error}
                    </motion.div>
                  )}
                  <div className="flex justify-between pt-4">
                    <Button variant="outline" onClick={() => setStep(1)} size="lg" className="gap-2">
                      <ArrowLeft className="w-4 h-4" />
                      Retour
                    </Button>
                    <Button onClick={handleNextStep} disabled={!validateStep2()} size="lg" className="gap-2">
                      Suivant
                      <ArrowRight className="w-4 h-4" />
                    </Button>
                  </div>
                </motion.div>
              )}

              {/* ÉTAPE 3: Confirmation */}
              {step === 3 && (
                <motion.div
                  key="step3"
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: -20 }}
                  className="space-y-6"
                >
                  <h2 className="text-2xl md:text-3xl font-bold mb-2">Confirmation</h2>
                  <p className="text-muted-foreground mb-6">Vérifiez vos informations avant de générer votre billet</p>
                  
                  {/* Alerte date limite d'inscription */}
                  {event.registration_deadline && (
                    <motion.div
                      initial={{ opacity: 0, y: -10 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3"
                    >
                      <Calendar className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                      <div>
                        <p className="font-semibold text-amber-900 text-sm">Date limite d'inscription</p>
                        <p className="text-amber-700 text-sm mt-1">
                          {new Date(event.registration_deadline).toLocaleDateString('fr-FR', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric' 
                          })}
                        </p>
                      </div>
                    </motion.div>
                  )}
                  
                  <div className="grid gap-5">
                    <motion.div 
                      initial={{ opacity: 0, y: 10 }} 
                      animate={{ opacity: 1, y: 0 }} 
                      transition={{ delay: 0.1 }} 
                      className="bg-gradient-to-br from-secondary/80 to-secondary/40 p-6 rounded-2xl border-2 border-accent/20 shadow-lg"
                    >
                      <div className="flex items-center gap-3 mb-4">
                        <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                          <Calendar className="w-5 h-5 text-accent" />
                        </div>
                        <h3 className="font-bold text-lg">Événement</h3>
                      </div>
                      <p className="font-semibold text-lg">{event.title}</p>
                      <p className="text-sm text-muted-foreground mt-2 flex items-center gap-2">
                        <Calendar className="w-4 h-4" />
                        {event.date} {event.end_date && `- ${event.end_date}`}
                      </p>
                      <p className="text-sm text-muted-foreground flex items-center gap-2">
                        <MapPin className="w-4 h-4" />
                        {event.venue_details || event.location}
                      </p>
                      {event.organizer && (
                        <p className="text-sm text-muted-foreground flex items-center gap-2 mt-1">
                          <User className="w-4 h-4" />
                          Organisé par {event.organizer}
                        </p>
                      )}
                    </motion.div>
                    
                    <motion.div 
                      initial={{ opacity: 0, y: 10 }} 
                      animate={{ opacity: 1, y: 0 }} 
                      transition={{ delay: 0.2 }} 
                      className="bg-gradient-to-br from-accent/10 to-accent/5 p-6 rounded-2xl border-2 border-accent/30 shadow-lg"
                    >
                      <div className="flex items-center gap-3 mb-4">
                        <div className="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center">
                          <Ticket className="w-5 h-5 text-accent" />
                        </div>
                        <h3 className="font-bold text-lg">Tarif sélectionné</h3>
                      </div>
                      {selectedPrice && (
                        <>
                          <p className="font-semibold text-lg mb-3">{selectedPrice.label}</p>
                          <div className="flex items-baseline gap-2">
                            <span className="text-4xl font-bold text-accent">
                              {selectedPrice.amount}
                            </span>
                            <span className="text-xl font-semibold text-accent">
                              {selectedPrice.currency}
                            </span>
                          </div>
                        </>
                      )}
                    </motion.div>
                    
                    <motion.div 
                      initial={{ opacity: 0, y: 10 }} 
                      animate={{ opacity: 1, y: 0 }} 
                      transition={{ delay: 0.3 }} 
                      className="bg-gradient-to-br from-secondary/80 to-secondary/40 p-6 rounded-2xl border-2 border-accent/20 shadow-lg"
                    >
                      <div className="flex items-center gap-3 mb-4">
                        <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                          <User className="w-5 h-5 text-accent" />
                        </div>
                        <h3 className="font-bold text-lg">Vos informations</h3>
                      </div>
                      <div className="space-y-2">
                        <p className="font-semibold text-lg">{formData.full_name}</p>
                        <p className="text-sm text-muted-foreground flex items-center gap-2">
                          <Mail className="w-4 h-4" />
                          {formData.email}
                        </p>
                        <p className="text-sm text-muted-foreground flex items-center gap-2">
                          <Phone className="w-4 h-4" />
                          {formData.phone}
                        </p>
                      </div>
                    </motion.div>
                  </div>
                  {error && (
                    <motion.div
                      initial={{ opacity: 0, scale: 0.95 }}
                      animate={{ opacity: 1, scale: 1 }}
                      className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl"
                    >
                      <div className="font-semibold mb-2">⚠️ Erreur</div>
                      <div className="text-sm whitespace-pre-line">{error}</div>
                    </motion.div>
                  )}
                  <div className="flex justify-between pt-4">
                    <Button variant="outline" onClick={() => setStep(2)} disabled={submitting} size="lg" className="gap-2">
                      <ArrowLeft className="w-4 h-4" />
                      Retour
                    </Button>
                    <Button onClick={handleNextStep} disabled={!validateStep3() || submitting} size="lg" className="gap-2 min-w-[200px]">
                      {submitting ? (
                        <>
                          <motion.div
                            animate={{ rotate: 360 }}
                            transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                            className="w-4 h-4 border-2 border-white border-t-transparent rounded-full"
                          />
                          Traitement...
                        </>
                      ) : (
                        <>
                          Générer mon billet
                          <ArrowRight className="w-4 h-4" />
                        </>
                      )}
                    </Button>
                  </div>
                </motion.div>
              )}

              {/* ÉTAPE 5: Billet avec Instructions de Paiement */}
              {step === 5 && ticketData && (
                <motion.div
                  key="step4"
                  initial={{ opacity: 0, scale: 0.95 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.95 }}
                  className="space-y-8"
                >
                  <div className="text-center">
                    <motion.div
                      initial={{ scale: 0 }}
                      animate={{ scale: 1 }}
                      transition={{ type: "spring", duration: 0.6 }}
                      className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4"
                    >
                      <CheckCircle className="w-10 h-10 text-green-600" />
                    </motion.div>
                    <h2 className="text-2xl md:text-3xl font-bold mb-2">Inscription réussie !</h2>
                    <p className="text-muted-foreground text-sm md:text-base mb-2">
                      Votre référence : <span className="font-mono font-bold text-accent text-2xl">{ticketData.reference}</span>
                    </p>
                    <p className="text-muted-foreground text-sm">
                      Choisissez votre mode de paiement ci-dessous
                    </p>
                  </div>

                  {/* Cartes de paiement M-Pesa et Orange Money */}
                  <div className="grid md:grid-cols-2 gap-6">
                    {/* Carte M-Pesa */}
                    <motion.div
                      initial={{ opacity: 0, x: -20 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: 0.2 }}
                      className="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-3xl p-6 md:p-8 shadow-xl hover:shadow-2xl transition-shadow"
                    >
                      <div className="flex items-center gap-4 mb-6">
                        <div className="w-14 h-14 bg-green-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                          <Phone className="w-7 h-7 text-white" />
                        </div>
                        <div>
                          <h3 className="text-xl md:text-2xl font-bold text-green-900">M-Pesa</h3>
                          <p className="text-sm text-green-700">Paiement mobile</p>
                        </div>
                      </div>

                      <div className="bg-white rounded-2xl p-5 mb-5 space-y-3">
                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Composez</p>
                            <p className="text-xl md:text-2xl font-bold text-green-600 font-mono">*1122#</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Choisissez</p>
                            <p className="text-base font-semibold text-gray-800">5 - Mes paiements</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">3</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Entrez le numéro</p>
                            <p className="text-2xl md:text-3xl font-bold text-green-600 font-mono">097435</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">4</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Entrez le montant</p>
                            <p className="text-2xl md:text-3xl font-bold text-green-600">{ticketData.amount} {ticketData.currency}</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">5</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Validez avec votre PIN</p>
                          </div>
                        </div>
                      </div>

                      <div className="bg-green-600 text-white rounded-xl p-4 text-center">
                        <p className="font-semibold text-sm">📱 Ou via l'app M-Pesa RDC</p>
                      </div>
                    </motion.div>

                    {/* Carte Orange Money */}
                    <motion.div
                      initial={{ opacity: 0, x: 20 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: 0.3 }}
                      className="bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-300 rounded-3xl p-6 md:p-8 shadow-xl hover:shadow-2xl transition-shadow"
                    >
                      <div className="flex items-center gap-4 mb-6">
                        <div className="w-14 h-14 bg-orange-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                          <Phone className="w-7 h-7 text-white" />
                        </div>
                        <div>
                          <h3 className="text-xl md:text-2xl font-bold text-orange-900">Orange Money</h3>
                          <p className="text-sm text-orange-700">Paiement mobile</p>
                        </div>
                      </div>

                      <div className="bg-white rounded-2xl p-5 mb-5 space-y-3">
                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Composez</p>
                            <p className="text-xl md:text-2xl font-bold text-orange-600 font-mono">*144#</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Choisir</p>
                            <p className="text-base font-semibold text-gray-800">2 et valider</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">3</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Choisir</p>
                            <p className="text-base font-semibold text-gray-800">1 et valider</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">4</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Saisir numéro du bénéficiaire</p>
                            <p className="text-base md:text-lg font-bold text-orange-600 font-mono">
                              {event.contact_phone || '+243 844 338 747'}
                            </p>
                            <p className="text-xs text-gray-600 mt-1">{event.organizer || 'Never Limit Children'}</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">5</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Saisir le montant du transfert</p>
                            <p className="text-2xl md:text-3xl font-bold text-orange-600">{ticketData.amount} {ticketData.currency}</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">6</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Saisir le code PIN</p>
                            <p className="text-sm text-gray-600">(mot de passe)</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">7</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Confirmer la transaction</p>
                            <p className="text-sm text-gray-600">SMS de notification à confirmer</p>
                          </div>
                        </div>
                      </div>

                      <div className="bg-orange-600 text-white rounded-xl p-4 text-center">
                        <p className="font-semibold text-sm">📱 Ou via l'app Orange Money</p>
                      </div>
                    </motion.div>
                  </div>

                  {/* Billet avec QR Code */}
                  <motion.div
                    id="ticket-to-download"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.5 }}
                    style={{ 
                      width: '700px', 
                      maxWidth: '100%',
                      backgroundColor: '#ffffff',
                      borderRadius: '16px',
                      overflow: 'hidden',
                      boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
                      margin: '48px auto 0 auto',
                      border: '2px solid #e5e7eb'
                    }}
                  >
                    {/* En-tête simplifié */}
                    <div style={{ 
                      position: 'relative',
                      height: '128px',
                      backgroundColor: '#2563eb',
                      display: 'flex',
                      flexDirection: 'column',
                      alignItems: 'center',
                      justifyContent: 'center'
                    }}>
                      <Ticket style={{ 
                        width: '64px', 
                        height: '64px', 
                        color: '#ffffff',
                        position: 'relative',
                        zIndex: 10,
                        marginBottom: '8px'
                      }} strokeWidth={1.5} />
                      <div style={{ 
                        backgroundColor: 'rgba(255, 255, 255, 0.2)',
                        padding: '4px 16px',
                        borderRadius: '9999px'
                      }}>
                        <p style={{ 
                          color: '#ffffff',
                          fontFamily: 'monospace',
                          fontWeight: 'bold',
                          fontSize: '14px'
                        }}>Réf: {ticketData.reference}</p>
                      </div>
                    </div>

                    <div style={{ padding: '24px' }}>
                      <div style={{ marginBottom: '24px', textAlign: 'center' }}>
                        <h3 style={{ 
                          fontSize: '20px',
                          fontWeight: 'bold',
                          color: '#111827',
                          marginBottom: '4px'
                        }}>{event.title}</h3>
                        <p style={{ 
                          fontSize: '14px',
                          color: '#4b5563'
                        }}>
                          {event.date} {event.end_date && `- ${event.end_date}`} • {event.venue_details || event.location}
                        </p>
                        {event.time && event.end_time && (
                          <p style={{ 
                            fontSize: '12px',
                            color: '#6b7280',
                            marginTop: '4px'
                          }}>
                            {event.time} - {event.end_time}
                          </p>
                        )}
                        {event.organizer && (
                          <p style={{ 
                            fontSize: '12px',
                            color: '#6b7280',
                            marginTop: '4px'
                          }}>
                            Organisé par {event.organizer}
                          </p>
                        )}
                      </div>

                      {/* Informations en 2 colonnes - layout fixe */}
                      <div style={{ 
                        display: 'grid', 
                        gridTemplateColumns: '1fr 1fr', 
                        gap: '16px', 
                        marginBottom: '24px' 
                      }}>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                          <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                            <div style={{ 
                              width: '32px',
                              height: '32px',
                              borderRadius: '50%',
                              backgroundColor: '#eff6ff',
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              flexShrink: 0
                            }}>
                              <User style={{ width: '16px', height: '16px', color: '#2563eb' }} />
                            </div>
                            <div>
                              <p style={{ 
                                fontSize: '12px',
                                color: '#6b7280',
                                textTransform: 'uppercase',
                                fontWeight: '600'
                              }}>Participant</p>
                              <p style={{ 
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#111827'
                              }}>{ticketData.full_name}</p>
                            </div>
                          </div>

                          <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                            <div style={{ 
                              width: '32px',
                              height: '32px',
                              borderRadius: '50%',
                              backgroundColor: '#eff6ff',
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              flexShrink: 0
                            }}>
                              <Mail style={{ width: '16px', height: '16px', color: '#2563eb' }} />
                            </div>
                            <div>
                              <p style={{ 
                                fontSize: '12px',
                                color: '#6b7280',
                                textTransform: 'uppercase',
                                fontWeight: '600'
                              }}>Email</p>
                              <p style={{ 
                                fontSize: '12px',
                                fontWeight: '500',
                                color: '#111827',
                                wordBreak: 'break-all'
                              }}>{ticketData.email}</p>
                            </div>
                          </div>
                        </div>

                        <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                          <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                            <div style={{ 
                              width: '32px',
                              height: '32px',
                              borderRadius: '50%',
                              backgroundColor: '#eff6ff',
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              flexShrink: 0
                            }}>
                              <Phone style={{ width: '16px', height: '16px', color: '#2563eb' }} />
                            </div>
                            <div>
                              <p style={{ 
                                fontSize: '12px',
                                color: '#6b7280',
                                textTransform: 'uppercase',
                                fontWeight: '600'
                              }}>Téléphone</p>
                              <p style={{ 
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#111827'
                              }}>{ticketData.phone}</p>
                            </div>
                          </div>

                          <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                            <div style={{ 
                              width: '32px',
                              height: '32px',
                              borderRadius: '50%',
                              backgroundColor: '#eff6ff',
                              display: 'flex',
                              alignItems: 'center',
                              justifyContent: 'center',
                              flexShrink: 0
                            }}>
                              <Ticket style={{ width: '16px', height: '16px', color: '#2563eb' }} />
                            </div>
                            <div>
                              <p style={{ 
                                fontSize: '12px',
                                color: '#6b7280',
                                textTransform: 'uppercase',
                                fontWeight: '600'
                              }}>Catégorie</p>
                              <p style={{ 
                                fontSize: '14px',
                                fontWeight: '500',
                                color: '#111827'
                              }}>{ticketData.category}</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* QR Code centré avec position fixe */}
                      <div style={{ 
                        display: 'flex', 
                        justifyContent: 'center', 
                        marginBottom: '24px',
                        width: '100%'
                      }}>
                        <div style={{ 
                          backgroundColor: '#f9fafb',
                          padding: '16px',
                          borderRadius: '12px',
                          border: '2px solid #e5e7eb',
                          display: 'inline-block'
                        }}>
                          <div style={{ 
                            width: '150px',
                            height: '150px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                          }}>
                            <QRCodeSVG 
                              value={qrData || ''} 
                              size={150}
                              level="H"
                              style={{ display: 'block' }}
                            />
                          </div>
                          <p style={{ 
                            fontSize: '12px',
                            textAlign: 'center',
                            color: '#4b5563',
                            marginTop: '8px',
                            fontWeight: '500',
                            maxWidth: '150px'
                          }}>Présentez ce code pour valider votre billet</p>
                        </div>
                      </div>

                      {/* Montant */}
                      <div style={{ 
                        borderTop: '2px dashed #d1d5db',
                        paddingTop: '16px',
                        marginBottom: '16px'
                      }}>
                        <div style={{ 
                          display: 'flex', 
                          justifyContent: 'space-between', 
                          alignItems: 'center' 
                        }}>
                          <span style={{ 
                            fontSize: '16px',
                            fontWeight: '600',
                            color: '#111827'
                          }}>Montant à payer</span>
                          <span style={{ 
                            fontSize: '24px',
                            fontWeight: 'bold',
                            color: '#2563eb'
                          }}>
                            {ticketData.amount} {ticketData.currency}
                          </span>
                        </div>
                      </div>

                      {/* Informations de contact */}
                      {(event.contact_phone || event.contact_email) && (
                        <div style={{ 
                          backgroundColor: '#f9fafb',
                          padding: '12px',
                          borderRadius: '8px',
                          border: '1px solid #e5e7eb'
                        }}>
                          <p style={{ 
                            fontSize: '11px',
                            fontWeight: '600',
                            color: '#6b7280',
                            textTransform: 'uppercase',
                            marginBottom: '8px'
                          }}>Contact organisateur</p>
                          <div style={{ 
                            display: 'flex',
                            flexDirection: 'column',
                            gap: '4px'
                          }}>
                            {event.contact_phone && (
                              <p style={{ 
                                fontSize: '12px',
                                color: '#111827'
                              }}>📞 {event.contact_phone}</p>
                            )}
                            {event.contact_email && (
                              <p style={{ 
                                fontSize: '12px',
                                color: '#111827'
                              }}>✉️ {event.contact_email}</p>
                            )}
                          </div>
                        </div>
                      )}
                    </div>
                  </motion.div>

                  {/* Boutons d'action */}
                  <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.7 }}
                    className="grid sm:grid-cols-2 gap-4"
                  >
                    <Button 
                      onClick={printTicket} 
                      variant="outline" 
                      size="lg"
                      className="gap-2"
                    >
                      <Download className="w-5 h-5" />
                      Télécharger en Image (PNG)
                    </Button>
                    
                    <Button 
                      onClick={downloadTicketPDF}
                      size="lg"
                      className="gap-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                    >
                      <Download className="w-5 h-5" />
                      Télécharger en PDF
                    </Button>
                  </motion.div>

                  <div className="text-center pt-4">
                    <Link to="/evenements">
                      <Button variant="ghost" size="lg" className="gap-2">
                        <ArrowLeft className="w-4 h-4" />
                        Retour aux événements
                      </Button>
                    </Link>
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </motion.div>
        </div>
      </section>

      <Footer />
    </main>
  );
};

export default EventInscriptionPage;
