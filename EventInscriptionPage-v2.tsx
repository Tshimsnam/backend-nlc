import { useEffect, useState } from "react";
import { useParams, useLocation, Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ArrowLeft, ArrowRight, CheckCircle, Printer, Download, Calendar, MapPin, Ticket, User, Mail, Phone } from "lucide-react";
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
  date: string;
  time: string;
  location: string;
  image: string;
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

  const [formData, setFormData] = useState<RegistrationFormData>({
    event_price_id: 0,
    full_name: "",
    email: "",
    phone: "",
    days: 1,
    pay_type: "cash",
  });

  const totalSteps = 4;
  const progress = (step / totalSteps) * 100;

  // Fonction pour télécharger le billet en PDF
  const downloadTicketPDF = async () => {
    const ticketElement = document.getElementById('ticket-to-download');
    if (!ticketElement) return;

    try {
      const canvas = await html2canvas(ticketElement, {
        scale: 2,
        backgroundColor: '#ffffff',
        logging: false,
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
    }
  };

  // Fonction pour imprimer le billet
  const printTicket = () => {
    window.print();
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

  const handleNextStep = () => {
    if (step === 1 && validateStep1()) {
      setStep(2);
      setTimeout(() => {
        const firstInput = document.getElementById('full_name');
        if (firstInput) firstInput.focus();
      }, 100);
    }
    else if (step === 2 && validateStep2()) setStep(3);
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
        setTicketData(res.data.ticket);
        
        const qrInfo = JSON.stringify({
          reference: res.data.ticket.reference,
          event: event.title,
          participant: formData.full_name,
          email: formData.email,
          phone: formData.phone,
          amount: res.data.ticket.amount,
          currency: res.data.ticket.currency,
          category: res.data.ticket.category,
          date: event.date,
          location: event.location,
        });
        setQrData(qrInfo);
        setStep(4);
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
                {[1, 2, 3, 4].map((s) => (
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
                      {s === 3 && "Confirmation"}
                      {s === 4 && "Paiement"}
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
                        {event.date}
                      </p>
                      <p className="text-sm text-muted-foreground flex items-center gap-2">
                        <MapPin className="w-4 h-4" />
                        {event.location}
                      </p>
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
                    <Button onClick={handleSubmit} disabled={submitting} size="lg" className="gap-2 min-w-[200px]">
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

              {/* ÉTAPE 4: Billet avec Instructions de Paiement */}
              {step === 4 && ticketData && (
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
                            <p className="text-xl md:text-2xl font-bold text-orange-600 font-mono">#144#</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Sélectionnez</p>
                            <p className="text-base font-semibold text-gray-800">Paiement marchand</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">3</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Entrez le numéro marchand</p>
                            <p className="text-lg md:text-xl font-bold text-orange-600 font-mono">[À VENIR]</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">4</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Entrez le montant</p>
                            <p className="text-2xl md:text-3xl font-bold text-orange-600">{ticketData.amount} {ticketData.currency}</p>
                          </div>
                        </div>

                        <div className="flex items-start gap-3">
                          <div className="w-7 h-7 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">5</div>
                          <div>
                            <p className="font-semibold text-gray-900 text-sm">Validez avec votre PIN</p>
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
                    className="bg-white rounded-2xl overflow-hidden shadow-2xl max-w-2xl mx-auto"
                  >
                    <div className="relative h-32 bg-gradient-to-br from-blue-600 via-blue-700 to-purple-700 flex flex-col items-center justify-center">
                      <Ticket className="w-16 h-16 text-white relative z-10 mb-2" strokeWidth={1.5} />
                      <div className="bg-white/20 backdrop-blur-sm px-4 py-1 rounded-full">
                        <p className="text-white font-mono font-bold text-sm">Réf: {ticketData.reference}</p>
                      </div>
                    </div>

                    <div className="p-6">
                      <div className="mb-6 text-center">
                        <h3 className="text-xl font-bold text-gray-900 mb-1">{event.title}</h3>
                        <p className="text-sm text-gray-600">{event.date} • {event.location}</p>
                      </div>

                      <div className="grid grid-cols-2 gap-4 mb-6">
                        <div className="space-y-3">
                          <div className="flex items-start gap-2">
                            <div className="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <User className="w-4 h-4 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase font-semibold">Participant</p>
                              <p className="text-sm font-medium text-gray-900">{ticketData.full_name}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-2">
                            <div className="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <Mail className="w-4 h-4 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase font-semibold">Email</p>
                              <p className="text-xs font-medium text-gray-900 break-all">{ticketData.email}</p>
                            </div>
                          </div>
                        </div>

                        <div className="space-y-3">
                          <div className="flex items-start gap-2">
                            <div className="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <Phone className="w-4 h-4 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase font-semibold">Téléphone</p>
                              <p className="text-sm font-medium text-gray-900">{ticketData.phone}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-2">
                            <div className="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <Ticket className="w-4 h-4 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase font-semibold">Catégorie</p>
                              <p className="text-sm font-medium text-gray-900">{ticketData.category}</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div className="flex justify-center mb-6">
                        <div className="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-xl border-2 border-gray-200">
                          <QRCodeSVG 
                            value={qrData || ''} 
                            size={150}
                            level="H"
                            className="mx-auto"
                          />
                          <p className="text-xs text-center text-gray-600 mt-2 font-medium">Présentez ce code pour valider votre billet</p>
                        </div>
                      </div>

                      <div className="border-t-2 border-dashed border-gray-300 pt-4">
                        <div className="flex justify-between items-center">
                          <span className="text-base font-semibold text-gray-900">Montant à payer</span>
                          <span className="text-2xl font-bold text-blue-600">
                            {ticketData.amount} {ticketData.currency}
                          </span>
                        </div>
                      </div>
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
                      <Printer className="w-5 h-5" />
                      Imprimer le billet
                    </Button>
                    
                    <Button 
                      onClick={downloadTicketPDF}
                      size="lg"
                      className="gap-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                    >
                      <Download className="w-5 h-5" />
                      Télécharger le Billet
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
