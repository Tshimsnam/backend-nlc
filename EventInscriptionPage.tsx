import { useEffect, useState } from "react";
import { useParams, useLocation } from "react-router-dom";
import { Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { ArrowLeft, ArrowRight } from "lucide-react";
import axios from "axios";

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
  pay_sub_type: string;
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

  const [formData, setFormData] = useState<RegistrationFormData>({
    event_price_id: 0,
    full_name: "",
    email: "",
    phone: "",
    days: 1,
    pay_type: "",
    pay_sub_type: "",
  });

  // Charger l'événement et les modes de paiement
  useEffect(() => {
    if (!slug) return;

    const fetchData = async () => {
      try {
        // Charger l'événement
        const eventRes = await axios.get(`${API_URL}/events/${slug}`);
        const eventData = eventRes.data;
        setEvent(eventData);

        // Charger les modes de paiement
        const modesRes = await axios.get(`${API_URL}/payment-modes`);
        setPaymentModes(modesRes.data);

        // Si un prix est passé via location.state
        const priceId = location.state?.priceId;
        if (priceId && eventData.event_prices) {
          const price = eventData.event_prices.find((p: EventPrice) => p.id === priceId);
          if (price) {
            setSelectedPrice(price);
            setFormData((prev) => ({
              ...prev,
              event_price_id: price.id,
            }));
          }
        }
      } catch {
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
    setFormData((prev) => ({ ...prev, pay_type: payType, pay_sub_type: "" }));
  };

  const handlePaymentSubTypeChange = (subType: string) => {
    setFormData((prev) => ({ ...prev, pay_sub_type: subType }));
  };

  const validateStep1 = () => {
    return formData.event_price_id > 0 && selectedPrice;
  };

  const validateStep2 = () => {
    return formData.full_name && formData.email && formData.phone;
  };

  const validateStep3 = () => {
    const selectedMode = paymentModes.find((m) => m.id === formData.pay_type);
    if (!selectedMode) return false;
    
    // Si le mode a des sous-modes, vérifier qu'un sous-mode est sélectionné
    if (selectedMode.sub_modes && selectedMode.sub_modes.length > 0) {
      return !!formData.pay_sub_type;
    }
    
    return true;
  };

  const handleNextStep = () => {
    if (step === 1 && validateStep1()) {
      setStep(2);
    } else if (step === 2 && validateStep2()) {
      setStep(3);
    } else if (step === 3 && validateStep3()) {
      setStep(4);
    }
  };

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

      if (res.data.success && res.data.redirect_url) {
        // Rediriger vers MaxiCash
        window.location.href = res.data.redirect_url;
      } else {
        // Afficher le message d'erreur avec les détails du ticket si disponibles
        const errorMsg = res.data.message || "Erreur lors de l'initialisation du paiement";
        const ticketRef = res.data.ticket?.reference;
        
        if (ticketRef) {
          setError(`${errorMsg}\n\nRéférence du ticket: ${ticketRef}\n\nVeuillez contacter le support si le problème persiste.`);
        } else {
          setError(errorMsg);
        }
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

  if (loading) {
    return <p className="text-center mt-20">Chargement...</p>;
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
    <main className="min-h-screen bg-secondary">
      <Header />

      <section className="pt-28 pb-16">
        <div className="container-custom max-w-4xl">
          {/* Breadcrumb */}
          <div className="mb-8">
            <Link to={`/evenements/${event.slug}`} className="text-accent hover:underline flex items-center gap-2">
              <ArrowLeft className="w-4 h-4" />
              Retour à l'événement
            </Link>
          </div>

          {/* Titre */}
          <div className="mb-8">
            <h1 className="text-4xl font-bold mb-2">Inscription</h1>
            <p className="text-muted-foreground">{event.title}</p>
          </div>

          {/* Indicateur d'étapes */}
          <div className="flex items-center justify-center gap-2 mb-12 overflow-x-auto">
            <div className={`flex items-center gap-2 ${step >= 1 ? "text-accent" : "text-muted-foreground"}`}>
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${step >= 1 ? "bg-accent text-white" : "bg-muted"}`}>
                1
              </div>
              <span className="text-xs md:text-sm font-medium whitespace-nowrap">Tarif</span>
            </div>
            <div className="w-8 md:w-12 h-0.5 bg-muted"></div>
            <div className={`flex items-center gap-2 ${step >= 2 ? "text-accent" : "text-muted-foreground"}`}>
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${step >= 2 ? "bg-accent text-white" : "bg-muted"}`}>
                2
              </div>
              <span className="text-xs md:text-sm font-medium whitespace-nowrap">Informations</span>
            </div>
            <div className="w-8 md:w-12 h-0.5 bg-muted"></div>
            <div className={`flex items-center gap-2 ${step >= 3 ? "text-accent" : "text-muted-foreground"}`}>
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${step >= 3 ? "bg-accent text-white" : "bg-muted"}`}>
                3
              </div>
              <span className="text-xs md:text-sm font-medium whitespace-nowrap">Paiement</span>
            </div>
            <div className="w-8 md:w-12 h-0.5 bg-muted"></div>
            <div className={`flex items-center gap-2 ${step >= 4 ? "text-accent" : "text-muted-foreground"}`}>
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${step >= 4 ? "bg-accent text-white" : "bg-muted"}`}>
                4
              </div>
              <span className="text-xs md:text-sm font-medium whitespace-nowrap">Confirmation</span>
            </div>
          </div>

          {/* Contenu */}
          <div className="bg-background rounded-3xl p-6 md:p-8 shadow-card">
            {/* ÉTAPE 1: Sélection du tarif */}
            {step === 1 && (
              <div className="space-y-6">
                <h2 className="text-2xl font-bold">Choisissez votre tarif</h2>

                <div className="space-y-4">
                  {event.event_prices.map((price) => (
                    <div
                      key={price.id}
                      onClick={() => handlePriceChange(price.id)}
                      className={`p-4 rounded-xl border-2 cursor-pointer transition ${
                        formData.event_price_id === price.id
                          ? "border-accent bg-accent/5"
                          : "border-gray-200 hover:border-accent/50"
                      }`}
                    >
                      <div className="flex justify-between items-start">
                        <div>
                          <h3 className="font-bold text-lg">{price.label}</h3>
                          <p className="text-sm text-muted-foreground">
                            {categoryLabels[price.category]} • {durationLabels[price.duration_type]}
                          </p>
                          {price.description && (
                            <p className="text-sm text-muted-foreground mt-2">{price.description}</p>
                          )}
                        </div>
                        <div className="text-right">
                          <div className="text-2xl font-bold text-accent">
                            {price.amount} {price.currency}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                <div className="flex justify-end">
                  <Button onClick={handleNextStep} disabled={!validateStep1()} className="gap-2">
                    Suivant
                    <ArrowRight className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            )}

            {/* ÉTAPE 2: Informations personnelles */}
            {step === 2 && (
              <div className="space-y-6">
                <h2 className="text-2xl font-bold">Vos informations</h2>

                <div className="space-y-4">
                  <div>
                    <Label htmlFor="full_name">Nom complet *</Label>
                    <Input
                      id="full_name"
                      type="text"
                      value={formData.full_name}
                      onChange={(e) => handleInputChange("full_name", e.target.value)}
                      placeholder="Votre nom complet"
                      required
                    />
                  </div>

                  <div>
                    <Label htmlFor="email">Email *</Label>
                    <Input
                      id="email"
                      type="email"
                      value={formData.email}
                      onChange={(e) => handleInputChange("email", e.target.value)}
                      placeholder="votre@email.com"
                      required
                    />
                  </div>

                  <div>
                    <Label htmlFor="phone">Téléphone *</Label>
                    <Input
                      id="phone"
                      type="tel"
                      value={formData.phone}
                      onChange={(e) => handleInputChange("phone", e.target.value)}
                      placeholder="+243 XXX XXX XXX"
                      required
                    />
                  </div>
                </div>

                {error && (
                  <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {error}
                  </div>
                )}

                <div className="flex justify-between">
                  <Button variant="outline" onClick={() => setStep(1)} className="gap-2">
                    <ArrowLeft className="w-4 h-4" />
                    Retour
                  </Button>
                  <Button onClick={handleNextStep} disabled={!validateStep2()} className="gap-2">
                    Suivant
                    <ArrowRight className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            )}

            {/* ÉTAPE 3: Mode de paiement */}
            {step === 3 && (
              <div className="space-y-6">
                <h2 className="text-2xl font-bold">Mode de paiement</h2>

                <RadioGroup value={formData.pay_type} onValueChange={handlePaymentTypeChange}>
                  <div className="space-y-3">
                    {paymentModes.map((mode) => (
                      <div key={mode.id}>
                        <div className="flex items-start space-x-3 p-4 rounded-xl border-2 hover:border-accent/50 transition">
                          <RadioGroupItem value={mode.id} id={mode.id} />
                          <div className="flex-1">
                            <Label htmlFor={mode.id} className="font-semibold cursor-pointer">
                              {mode.label}
                            </Label>
                            <p className="text-sm text-muted-foreground mt-1">{mode.description}</p>
                          </div>
                        </div>

                        {/* Sous-modes */}
                        {formData.pay_type === mode.id && mode.sub_modes && mode.sub_modes.length > 0 && (
                          <div className="ml-10 mt-3 space-y-2">
                            <Label className="text-sm">Choisissez votre opérateur :</Label>
                            <RadioGroup value={formData.pay_sub_type} onValueChange={handlePaymentSubTypeChange}>
                              {mode.sub_modes.map((sub) => (
                                <div key={sub.id} className="flex items-center space-x-2">
                                  <RadioGroupItem value={sub.id} id={sub.id} />
                                  <Label htmlFor={sub.id} className="cursor-pointer">
                                    {sub.label}
                                  </Label>
                                </div>
                              ))}
                            </RadioGroup>
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                </RadioGroup>

                {error && (
                  <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {error}
                  </div>
                )}

                <div className="flex justify-between">
                  <Button variant="outline" onClick={() => setStep(2)} className="gap-2">
                    <ArrowLeft className="w-4 h-4" />
                    Retour
                  </Button>
                  <Button onClick={handleNextStep} disabled={!validateStep3()} className="gap-2">
                    Suivant
                    <ArrowRight className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            )}

            {/* ÉTAPE 4: Confirmation */}
            {step === 4 && (
              <div className="space-y-6">
                <h2 className="text-2xl font-bold">Confirmation</h2>

                <div className="space-y-4">
                  <div className="bg-secondary p-4 rounded-xl">
                    <h3 className="font-semibold mb-2">Événement</h3>
                    <p className="text-muted-foreground">{event.title}</p>
                    <p className="text-sm text-muted-foreground">{event.date} • {event.location}</p>
                  </div>

                  <div className="bg-secondary p-4 rounded-xl">
                    <h3 className="font-semibold mb-2">Tarif sélectionné</h3>
                    {selectedPrice && (
                      <>
                        <p className="text-muted-foreground">{selectedPrice.label}</p>
                        <p className="text-2xl font-bold text-accent mt-2">
                          {selectedPrice.amount} {selectedPrice.currency}
                        </p>
                      </>
                    )}
                  </div>

                  <div className="bg-secondary p-4 rounded-xl">
                    <h3 className="font-semibold mb-2">Vos informations</h3>
                    <p className="text-muted-foreground">{formData.full_name}</p>
                    <p className="text-sm text-muted-foreground">{formData.email}</p>
                    <p className="text-sm text-muted-foreground">{formData.phone}</p>
                  </div>

                  <div className="bg-secondary p-4 rounded-xl">
                    <h3 className="font-semibold mb-2">Mode de paiement</h3>
                    <p className="text-muted-foreground">
                      {paymentModes.find((m) => m.id === formData.pay_type)?.label}
                      {formData.pay_sub_type && ` - ${paymentModes.find((m) => m.id === formData.pay_type)?.sub_modes?.find((s) => s.id === formData.pay_sub_type)?.label}`}
                    </p>
                  </div>
                </div>

                {error && (
                  <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div className="font-semibold mb-2">⚠️ Erreur de paiement</div>
                    <div className="text-sm whitespace-pre-line">{error}</div>
                  </div>
                )}

                <div className="flex justify-between">
                  <Button variant="outline" onClick={() => setStep(3)} disabled={submitting} className="gap-2">
                    <ArrowLeft className="w-4 h-4" />
                    Retour
                  </Button>
                  <Button onClick={handleSubmit} disabled={submitting} size="lg" className="gap-2">
                    {submitting ? "Redirection vers le paiement..." : "Procéder au paiement"}
                    <ArrowRight className="w-4 h-4" />
                  </Button>
                </div>
              </div>
            )}
          </div>
        </div>
      </section>

      <Footer />
    </main>
  );
};

export default EventInscriptionPage;
