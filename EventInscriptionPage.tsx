import { useEffect, useState } from "react";
import { useParams, useLocation, Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
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

interface PaymentMode {
  id: string;
  label: string;
  description: string;
  requires_phone: boolean;
  sub_modes?: Array<{ id: string; label: string }>;
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
  const [paymentModes, setPaymentModes] = useState<PaymentMode[]>([]);
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
    pay_type: "cash", // Par défaut cash pour générer la référence
  });

  const totalSteps = 4; // Seulement 4 étapes maintenant
  const progress = (step / totalSteps) * 100;

  // Fonction pour télécharger le billet en PDF
  const downloadTicketPDF = async () => {
    const ticketElement = document.getElementById('ticket-to-download');
    if (!ticketElement) {
      alert('Élément du billet introuvable. Veuillez réessayer.');
      return;
    }

    try {
      // Attendre un peu pour s'assurer que tout est rendu
      await new Promise(resolve => setTimeout(resolve, 300));

      const canvas = await html2canvas(ticketElement, {
        scale: 3, // Augmenté pour meilleure qualité
        backgroundColor: '#ffffff',
        logging: true, // Activé pour debug
        useCORS: true, // Permet de capturer les images cross-origin
        allowTaint: true, // Permet de capturer les SVG
        foreignObjectRendering: true, // Améliore le rendu des SVG
        windowWidth: ticketElement.scrollWidth,
        windowHeight: ticketElement.scrollHeight,
      });

      // Vérifier que le canvas n'est pas vide
      if (canvas.width === 0 || canvas.height === 0) {
        throw new Error('Le canvas généré est vide');
      }

      const imgData = canvas.toDataURL('image/png');
      
      // Vérifier que l'image n'est pas vide
      if (!imgData || imgData === 'data:,') {
        throw new Error('Image générée vide');
      }

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
      alert(`Erreur lors de la génération du PDF: ${error instanceof Error ? error.message : 'Erreur inconnue'}. Veuillez réessayer.`);
    }
  };

  // Fonction pour imprimer uniquement le billet
  const printTicket = () => {
    const ticketElement = document.getElementById('ticket-to-download');
    if (!ticketElement) return;

    // Créer une nouvelle fenêtre pour l'impression
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    // Copier le contenu du billet
    const ticketHTML = ticketElement.outerHTML;

    // Créer le document HTML pour l'impression
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
        <head>
          <title>Billet - ${ticketData?.reference || 'Ticket'}</title>
          <style>
            * {
              margin: 0;
              padding: 0;
              box-sizing: border-box;
            }
            body {
              font-family: system-ui, -apple-system, sans-serif;
              background: white;
              display: flex;
              align-items: center;
              justify-content: center;
              min-height: 100vh;
              padding: 10mm;
            }
            @media print {
              body {
                padding: 0;
                margin: 0;
              }
              @page {
                size: A4 portrait;
                margin: 10mm;
              }
              #ticket-to-download {
                page-break-inside: avoid;
                max-width: 100% !important;
                transform: scale(0.95);
                transform-origin: top center;
              }
            }
            /* Copier les styles du billet */
            .bg-white { background-color: white; }
            .rounded-3xl { border-radius: 1.5rem; }
            .overflow-hidden { overflow: hidden; }
            .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
            .max-w-3xl { max-width: 48rem; margin: 0 auto; width: 100%; }
            .relative { position: relative; }
            .h-40 { height: 8rem; }
            .h-48 { height: 10rem; }
            .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
            .from-blue-600 { --tw-gradient-from: #2563eb; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(37, 99, 235, 0)); }
            .via-blue-700 { --tw-gradient-stops: var(--tw-gradient-from), #1d4ed8, var(--tw-gradient-to, rgba(29, 78, 216, 0)); }
            .to-purple-700 { --tw-gradient-to: #7e22ce; }
            .flex { display: flex; }
            .items-center { align-items: center; }
            .justify-center { justify-content: center; }
            .absolute { position: absolute; }
            .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
            .opacity-20 { opacity: 0.2; }
            .text-white { color: white; }
            .z-10 { z-index: 10; }
            .p-6 { padding: 1.2rem; }
            .p-10 { padding: 1.5rem; }
            .mb-8 { margin-bottom: 1.5rem; }
            .text-center { text-align: center; }
            .text-2xl { font-size: 1.3rem; line-height: 1.8rem; }
            .text-3xl { font-size: 1.6rem; line-height: 2rem; }
            .font-bold { font-weight: 700; }
            .text-gray-900 { color: #111827; }
            .mb-2 { margin-bottom: 0.4rem; }
            .inline-block { display: inline-block; }
            .px-4 { padding-left: 0.8rem; padding-right: 0.8rem; }
            .py-1 { padding-top: 0.2rem; padding-bottom: 0.2rem; }
            .bg-blue-50 { background-color: #eff6ff; }
            .rounded-full { border-radius: 9999px; }
            .text-sm { font-size: 0.75rem; line-height: 1rem; }
            .font-mono { font-family: ui-monospace, monospace; }
            .font-semibold { font-weight: 600; }
            .text-blue-700 { color: #1d4ed8; }
            .grid { display: grid; }
            .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .gap-6 { gap: 1rem; }
            .space-y-5 > * + * { margin-top: 1rem; }
            .gap-4 { gap: 0.8rem; }
            .w-10 { width: 2rem; }
            .h-10 { height: 2rem; }
            .bg-blue-50 { background-color: #eff6ff; }
            .flex-shrink-0 { flex-shrink: 0; }
            .w-5 { width: 1rem; }
            .h-5 { height: 1rem; }
            .text-blue-600 { color: #2563eb; }
            .text-xs { font-size: 0.7rem; line-height: 0.9rem; }
            .text-gray-500 { color: #6b7280; }
            .uppercase { text-transform: uppercase; }
            .tracking-wide { letter-spacing: 0.025em; }
            .text-base { font-size: 0.85rem; line-height: 1.2rem; }
            .font-medium { font-weight: 500; }
            .mt-1 { margin-top: 0.2rem; }
            .break-all { word-break: break-all; }
            .mb-6 { margin-bottom: 1rem; }
            .from-gray-50 { --tw-gradient-from: #f9fafb; }
            .to-gray-100 { --tw-gradient-to: #f3f4f6; }
            .p-8 { padding: 1.2rem; }
            .rounded-2xl { border-radius: 0.8rem; }
            .border-2 { border-width: 2px; }
            .border-gray-200 { border-color: #e5e7eb; }
            .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06); }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .text-gray-600 { color: #4b5563; }
            .mt-4 { margin-top: 0.6rem; }
            .border-t-2 { border-top-width: 2px; }
            .border-dashed { border-style: dashed; }
            .border-gray-300 { border-color: #d1d5db; }
            .pt-6 { padding-top: 1rem; }
            .justify-between { justify-content: space-between; }
            .text-lg { font-size: 0.95rem; line-height: 1.4rem; }
            .text-xl { font-size: 1.1rem; line-height: 1.5rem; }
            .text-4xl { font-size: 1.8rem; line-height: 2rem; }
            .w-20 { width: 4rem; }
            .h-20 { height: 4rem; }
            .w-24 { width: 5rem; }
            .h-24 { height: 5rem; }
            svg { max-width: 150px !important; max-height: 150px !important; }
            @media (min-width: 768px) {
              .md\\:h-48 { height: 10rem; }
              .md\\:text-3xl { font-size: 1.6rem; line-height: 2rem; }
              .md\\:p-10 { padding: 1.5rem; }
              .md\\:p-8 { padding: 1.2rem; }
              .md\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
              .md\\:text-xl { font-size: 1.1rem; line-height: 1.5rem; }
              .md\\:text-4xl { font-size: 1.8rem; line-height: 2rem; }
              .md\\:w-24 { width: 5rem; }
              .md\\:h-24 { height: 5rem; }
            }
          </style>
        </head>
        <body>
          ${ticketHTML}
        </body>
      </html>
    `);

    printWindow.document.close();
    
    // Attendre que le contenu soit chargé avant d'imprimer
    printWindow.onload = () => {
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    };
  };

  // Charger l'événement et les modes de paiement
  useEffect(() => {
    if (!slug) return;

    const fetchData = async () => {
      try {
        const eventRes = await axios.get(`${API_URL}/events/${slug}`);
        const eventData = eventRes.data;
        setEvent(eventData);

        // Récupérer les modes de paiement pour cet événement
        const modesRes = await axios.get(`${API_URL}/events/${eventData.id}/tickets/payment-modes`);
        setPaymentModes(modesRes.data);

        const priceId = location.state?.priceId;
        if (priceId && eventData.event_prices) {
          const price = eventData.event_prices.find((p: EventPrice) => p.id === priceId);
          if (price) {
            setSelectedPrice(price);
            setFormData((prev) => ({ ...prev, event_price_id: price.id }));
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

  const handlePaymentTypeChange = (payType: string) => {
    setFormData((prev) => ({ ...prev, pay_type: payType }));
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
        // Toujours afficher le billet avec les instructions
        setTicketData(res.data.ticket);
        
        // Utiliser le qr_data retourné par l'API (contient la structure correcte pour le scan)
        setQrData(res.data.ticket.qr_data);
        setStep(4); // Aller à l'étape 4 (affichage du billet)
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

          {/* Barre de progression fluide */}
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
                      {s === 4 && "Billet"}
                    </span>
                  </motion.div>
                ))}
              </div>
            </div>
          </motion.div>

          {/* Contenu avec AnimatePresence */}
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
                        {/* Indicateur de sélection */}
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
                            <p className="text-xs text-muted-foreground mt-1">par {price.duration_type === 'per_day' ? 'jour' : 'événement'}</p>
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
                  key="step4"
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: -20 }}
                  className="space-y-6"
                >
                  <h2 className="text-2xl md:text-3xl font-bold mb-2">Confirmation</h2>
                  <p className="text-muted-foreground mb-6">Vérifiez vos informations avant de procéder au paiement</p>
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
                  key="step5"
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
                    <p className="text-muted-foreground text-sm md:text-base">
                      Votre référence : <span className="font-mono font-bold text-accent">{ticketData.reference}</span>
                    </p>
                  </div>

                  {/* Instructions de paiement selon le mode */}
                  {paymentMode === 'mpesa' && (
                    <motion.div
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-3xl p-8 shadow-xl"
                    >
                      <div className="flex items-center gap-4 mb-6">
                        <div className="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center">
                          <Phone className="w-8 h-8 text-white" />
                        </div>
                        <div>
                          <h3 className="text-2xl font-bold text-green-900">Paiement M-Pesa</h3>
                          <p className="text-green-700">Suivez ces étapes pour payer</p>
                        </div>
                      </div>

                      <div className="bg-white rounded-2xl p-6 mb-6">
                        <div className="space-y-4">
                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
                            <div>
                              <p className="font-semibold text-gray-900">Composez</p>
                              <p className="text-2xl font-bold text-green-600 font-mono">*1122#</p>
                              <p className="text-sm text-gray-600 mt-1">Sur votre compte Dollar ou Franc</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                            <div>
                              <p className="font-semibold text-gray-900">Choisissez</p>
                              <p className="text-lg font-semibold text-gray-800">5 - Mes paiements</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
                            <div>
                              <p className="font-semibold text-gray-900">Entrez le numéro</p>
                              <p className="text-3xl font-bold text-green-600 font-mono">097435</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">4</div>
                            <div>
                              <p className="font-semibold text-gray-900">Sélectionnez la raison</p>
                              <p className="text-lg font-semibold text-gray-800">La raison de transaction</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">5</div>
                            <div>
                              <p className="font-semibold text-gray-900">Indiquez le numéro de caisse</p>
                              <p className="text-lg font-semibold text-gray-800">Indiquez le numéro de caisse</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">6</div>
                            <div>
                              <p className="font-semibold text-gray-900">Entrez le montant</p>
                              <p className="text-3xl font-bold text-green-600">{ticketData.amount} {ticketData.currency}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">7</div>
                            <div>
                              <p className="font-semibold text-gray-900">Validez avec votre</p>
                              <p className="text-lg font-semibold text-gray-800">PIN M-Pesa</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div className="bg-green-600 text-white rounded-2xl p-6">
                        <p className="font-semibold mb-2">📱 Ou utilisez l'application M-Pesa RDC</p>
                        <p className="text-sm text-green-100">Téléchargez l'app sur Google Play ou App Store</p>
                      </div>
                    </motion.div>
                  )}

                  {paymentMode === 'orange_money' && (
                    <motion.div
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-300 rounded-3xl p-8 shadow-xl"
                    >
                      <div className="flex items-center gap-4 mb-6">
                        <div className="w-16 h-16 bg-orange-600 rounded-2xl flex items-center justify-center">
                          <Phone className="w-8 h-8 text-white" />
                        </div>
                        <div>
                          <h3 className="text-2xl font-bold text-orange-900">Paiement Orange Money</h3>
                          <p className="text-orange-700">Suivez ces étapes pour payer</p>
                        </div>
                      </div>

                      <div className="bg-white rounded-2xl p-6 mb-6">
                        <div className="space-y-4">
                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
                            <div>
                              <p className="font-semibold text-gray-900">Composez</p>
                              <p className="text-2xl font-bold text-orange-600 font-mono">#144#</p>
                              <p className="text-sm text-gray-600 mt-1">Sur votre téléphone Orange</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                            <div>
                              <p className="font-semibold text-gray-900">Sélectionnez</p>
                              <p className="text-lg font-semibold text-gray-800">Paiement marchand</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
                            <div>
                              <p className="font-semibold text-gray-900">Entrez le numéro marchand</p>
                              <p className="text-3xl font-bold text-orange-600 font-mono">[NUMERO_MARCHAND]</p>
                              <p className="text-sm text-gray-600 mt-1">Contactez-nous pour obtenir ce numéro</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">4</div>
                            <div>
                              <p className="font-semibold text-gray-900">Entrez le montant</p>
                              <p className="text-3xl font-bold text-orange-600">{ticketData.amount} {ticketData.currency}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">5</div>
                            <div>
                              <p className="font-semibold text-gray-900">Entrez votre référence</p>
                              <p className="text-2xl font-bold text-orange-600 font-mono">{ticketData.reference}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-8 h-8 bg-orange-600 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">6</div>
                            <div>
                              <p className="font-semibold text-gray-900">Validez avec votre</p>
                              <p className="text-lg font-semibold text-gray-800">Code PIN Orange Money</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div className="bg-orange-600 text-white rounded-2xl p-6">
                        <p className="font-semibold mb-2">📱 Ou utilisez l'application Orange Money</p>
                        <p className="text-sm text-orange-100">Téléchargez l'app sur Google Play ou App Store</p>
                      </div>
                    </motion.div>
                  )}

                  {paymentMode === 'cash' && (
                    <motion.div
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="bg-blue-50 border-2 border-blue-200 text-blue-900 px-6 py-5 rounded-2xl"
                    >
                      <h4 className="font-bold text-lg mb-3 flex items-center gap-2">
                        <CheckCircle className="w-5 h-5" />
                        Instructions pour le paiement en caisse
                      </h4>
                      <ol className="text-sm space-y-2 list-decimal list-inside">
                        <li>Présentez-vous à la caisse avec ce billet</li>
                        <li>Effectuez le paiement de {ticketData.amount} {ticketData.currency}</li>
                        <li>Votre billet sera validé et vous recevrez une confirmation par email</li>
                      </ol>
                    </motion.div>
                  )}

                  {/* Billet moderne */}
                  <motion.div
                    id="ticket-to-download"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.3 }}
                    className="bg-white rounded-3xl overflow-hidden shadow-2xl max-w-3xl mx-auto"
                  >
                    {/* En-tête avec dégradé */}
                    <div className={`relative h-40 md:h-48 bg-gradient-to-br ${
                      paymentMode === 'mpesa' ? 'from-green-600 via-green-700 to-green-800' :
                      paymentMode === 'orange_money' ? 'from-orange-600 via-orange-700 to-orange-800' :
                      'from-blue-600 via-blue-700 to-purple-700'
                    } flex items-center justify-center overflow-hidden`}>
                      <div className="absolute inset-0 opacity-20">
                        <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')]"></div>
                      </div>
                      <Ticket className="w-20 h-20 md:w-24 md:h-24 text-white relative z-10" strokeWidth={1.5} />
                    </div>

                    {/* Contenu du billet */}
                    <div className="p-6 md:p-10">
                      {/* Titre */}
                      <div className="mb-8 text-center">
                        <h3 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{event.title}</h3>
                        <div className={`inline-block px-4 py-1 rounded-full ${
                          paymentMode === 'mpesa' ? 'bg-green-50' :
                          paymentMode === 'orange_money' ? 'bg-orange-50' :
                          'bg-blue-50'
                        }`}>
                          <p className={`text-sm font-mono font-semibold ${
                            paymentMode === 'mpesa' ? 'text-green-700' :
                            paymentMode === 'orange_money' ? 'text-orange-700' :
                            'text-blue-700'
                          }`}>Réf: {ticketData.reference}</p>
                        </div>
                      </div>

                      {/* Grille d'informations */}
                      <div className="grid md:grid-cols-2 gap-6 mb-8">
                        {/* Colonne gauche */}
                        <div className="space-y-5">
                          <div className="flex items-start gap-4">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${
                              paymentMode === 'mpesa' ? 'bg-green-50' :
                              paymentMode === 'orange_money' ? 'bg-orange-50' :
                              'bg-blue-50'
                            }`}>
                              <Calendar className={`w-5 h-5 ${
                                paymentMode === 'mpesa' ? 'text-green-600' :
                                paymentMode === 'orange_money' ? 'text-orange-600' :
                                'text-blue-600'
                              }`} />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Date</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{event.date}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${
                              paymentMode === 'mpesa' ? 'bg-green-50' :
                              paymentMode === 'orange_money' ? 'bg-orange-50' :
                              'bg-blue-50'
                            }`}>
                              <MapPin className={`w-5 h-5 ${
                                paymentMode === 'mpesa' ? 'text-green-600' :
                                paymentMode === 'orange_money' ? 'text-orange-600' :
                                'text-blue-600'
                              }`} />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Lieu</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{event.location}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${
                              paymentMode === 'mpesa' ? 'bg-green-50' :
                              paymentMode === 'orange_money' ? 'bg-orange-50' :
                              'bg-blue-50'
                            }`}>
                              <Ticket className={`w-5 h-5 ${
                                paymentMode === 'mpesa' ? 'text-green-600' :
                                paymentMode === 'orange_money' ? 'text-orange-600' :
                                'text-blue-600'
                              }`} />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Catégorie</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{ticketData.category}</p>
                            </div>
                          </div>
                        </div>

                        {/* Colonne droite */}
                        <div className="space-y-5">
                          <div className="flex items-start gap-4">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${
                              paymentMode === 'mpesa' ? 'bg-green-50' :
                              paymentMode === 'orange_money' ? 'bg-orange-50' :
                              'bg-blue-50'
                            }`}>
                              <User className={`w-5 h-5 ${
                                paymentMode === 'mpesa' ? 'text-green-600' :
                                paymentMode === 'orange_money' ? 'text-orange-600' :
                                'text-blue-600'
                              }`} />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Participant</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{ticketData.full_name}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${
                              paymentMode === 'mpesa' ? 'bg-green-50' :
                              paymentMode === 'orange_money' ? 'bg-orange-50' :
                              'bg-blue-50'
                            }`}>
                              <Mail className={`w-5 h-5 ${
                                paymentMode === 'mpesa' ? 'text-green-600' :
                                paymentMode === 'orange_money' ? 'text-orange-600' :
                                'text-blue-600'
                              }`} />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Email</p>
                              <p className="text-sm font-medium text-gray-900 mt-1 break-all">{ticketData.email}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${
                              paymentMode === 'mpesa' ? 'bg-green-50' :
                              paymentMode === 'orange_money' ? 'bg-orange-50' :
                              'bg-blue-50'
                            }`}>
                              <Phone className={`w-5 h-5 ${
                                paymentMode === 'mpesa' ? 'text-green-600' :
                                paymentMode === 'orange_money' ? 'text-orange-600' :
                                'text-blue-600'
                              }`} />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Téléphone</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{ticketData.phone}</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* QR Code */}
                      <div className="flex justify-center mb-8">
                        <div className="bg-gradient-to-br from-gray-50 to-gray-100 p-6 md:p-8 rounded-2xl border-2 border-gray-200 shadow-inner">
                          <QRCodeSVG 
                            value={qrData || ''} 
                            size={200}
                            level="H"
                            className="mx-auto"
                          />
                          <p className="text-xs text-center text-gray-600 mt-4 font-medium">Scannez ce code à l'entrée</p>
                        </div>
                      </div>

                      {/* Montant */}
                      <div className="border-t-2 border-dashed border-gray-300 pt-6">
                        <div className="flex justify-between items-center">
                          <span className="text-lg md:text-xl font-semibold text-gray-900">Montant à payer</span>
                          <span className={`text-3xl md:text-4xl font-bold ${
                            paymentMode === 'mpesa' ? 'text-green-600' :
                            paymentMode === 'orange_money' ? 'text-orange-600' :
                            'text-blue-600'
                          }`}>
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
                    transition={{ delay: 0.6 }}
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
                      className={`gap-2 bg-gradient-to-r ${
                        paymentMode === 'mpesa' ? 'from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' :
                        paymentMode === 'orange_money' ? 'from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800' :
                        'from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700'
                      }`}
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
                <motion.div
                  key="step5"
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
                    <p className="text-muted-foreground text-sm md:text-base">
                      Présentez ce billet à la caisse pour finaliser votre paiement
                    </p>
                  </div>

                  {/* Billet moderne */}
                  <motion.div
                    id="ticket-to-download"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.3 }}
                    className="bg-white rounded-3xl overflow-hidden shadow-2xl max-w-3xl mx-auto"
                  >
                    {/* En-tête avec dégradé */}
                    <div className="relative h-40 md:h-48 bg-gradient-to-br from-blue-600 via-blue-700 to-purple-700 flex items-center justify-center overflow-hidden">
                      <div className="absolute inset-0 opacity-20">
                        <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')]"></div>
                      </div>
                      <Ticket className="w-20 h-20 md:w-24 md:h-24 text-white relative z-10" strokeWidth={1.5} />
                    </div>

                    {/* Contenu du billet */}
                    <div className="p-6 md:p-10">
                      {/* Titre */}
                      <div className="mb-8 text-center">
                        <h3 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{event.title}</h3>
                        <div className="inline-block px-4 py-1 bg-blue-50 rounded-full">
                          <p className="text-sm font-mono font-semibold text-blue-700">Réf: {ticketData.reference}</p>
                        </div>
                      </div>

                      {/* Grille d'informations */}
                      <div className="grid md:grid-cols-2 gap-6 mb-8">
                        {/* Colonne gauche */}
                        <div className="space-y-5">
                          <div className="flex items-start gap-4">
                            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <Calendar className="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Date</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{event.date}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <MapPin className="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Lieu</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{event.location}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <Ticket className="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Catégorie</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{ticketData.category}</p>
                            </div>
                          </div>
                        </div>

                        {/* Colonne droite */}
                        <div className="space-y-5">
                          <div className="flex items-start gap-4">
                            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <User className="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Participant</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{ticketData.full_name}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <Mail className="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Email</p>
                              <p className="text-sm font-medium text-gray-900 mt-1 break-all">{ticketData.email}</p>
                            </div>
                          </div>

                          <div className="flex items-start gap-4">
                            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                              <Phone className="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                              <p className="text-xs text-gray-500 uppercase tracking-wide font-semibold">Téléphone</p>
                              <p className="text-base font-medium text-gray-900 mt-1">{ticketData.phone}</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* QR Code */}
                      <div className="flex justify-center mb-8">
                        <div className="bg-gradient-to-br from-gray-50 to-gray-100 p-6 md:p-8 rounded-2xl border-2 border-gray-200 shadow-inner">
                          <QRCodeSVG 
                            value={qrData || ''} 
                            size={200}
                            level="H"
                            className="mx-auto"
                          />
                          <p className="text-xs text-center text-gray-600 mt-4 font-medium">Scannez ce code à l'entrée</p>
                        </div>
                      </div>

                      {/* Montant */}
                      <div className="border-t-2 border-dashed border-gray-300 pt-6">
                        <div className="flex justify-between items-center">
                          <span className="text-lg md:text-xl font-semibold text-gray-900">Montant à payer</span>
                          <span className="text-3xl md:text-4xl font-bold text-blue-600">
                            {ticketData.amount} {ticketData.currency}
                          </span>
                        </div>
                      </div>
                    </div>
                  </motion.div>

                  {/* Instructions */}
                  <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.5 }}
                    className="bg-blue-50 border-2 border-blue-200 text-blue-900 px-6 py-5 rounded-2xl"
                  >
                    <h4 className="font-bold text-lg mb-3 flex items-center gap-2">
                      <CheckCircle className="w-5 h-5" />
                      Instructions
                    </h4>
                    <ol className="text-sm space-y-2 list-decimal list-inside">
                      <li>Téléchargez ou imprimez votre billet</li>
                      <li>Présentez-vous à la caisse avec ce billet</li>
                      <li>Effectuez le paiement de {ticketData.amount} {ticketData.currency}</li>
                      <li>Votre billet sera validé et vous recevrez une confirmation par email</li>
                    </ol>
                  </motion.div>

                  {/* Boutons d'action */}
                  <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.6 }}
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
