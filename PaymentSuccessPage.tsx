import { useEffect, useState, useRef } from "react";
import { useSearchParams, Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { CheckCircle, Download, Calendar, MapPin, User, Mail, Phone, Ticket as TicketIcon } from "lucide-react";
import axios from "axios";
import QRCode from "qrcode";
import html2canvas from "html2canvas";
import jsPDF from "jspdf";

interface TicketData {
  id: number;
  reference: string;
  full_name: string;
  email: string;
  phone: string;
  category: string;
  amount: string;
  currency: string;
  payment_status: string;
  event: {
    title: string;
    date: string;
    time: string;
    location: string;
    image: string;
  };
  price: {
    label: string;
    duration_type: string;
  };
}

const API_URL = import.meta.env.VITE_API_URL;

const PaymentSuccessPage = () => {
  const [searchParams] = useSearchParams();
  const [ticket, setTicket] = useState<TicketData | null>(null);
  const [qrCode, setQrCode] = useState<string>("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const ticketRef = useRef<HTMLDivElement>(null);

  // MaxiCash peut envoyer: reference, Reference, ref, logid, LogID
  const reference = searchParams.get("reference") || 
                    searchParams.get("Reference") || 
                    searchParams.get("ref");
  const logId = searchParams.get("logid") || searchParams.get("LogID");

  useEffect(() => {
    if (!reference && !logId) {
      setError("Référence de ticket manquante");
      setLoading(false);
      return;
    }

    const fetchTicket = async () => {
      try {
        let ticketData;
        
        if (reference) {
          // Recherche par référence directe
          const res = await axios.get(`${API_URL}/tickets/${reference}`);
          ticketData = res.data;
        } else if (logId) {
          // Recherche par LogID MaxiCash (gateway_log_id)
          const res = await axios.get(`${API_URL}/tickets/${logId}?gateway_log_id=${logId}`);
          ticketData = res.data;
        }
        
        if (!ticketData) {
          setError("Billet introuvable");
          setLoading(false);
          return;
        }

        setTicket(ticketData);

        // Générer le QR code
        const qrData = await QRCode.toDataURL(
          `${window.location.origin}/ticket/${reference}`,
          { width: 200, margin: 1 }
        );
        setQrCode(qrData);
      } catch {
        setError("Impossible de charger le billet");
      } finally {
        setLoading(false);
      }
    };

    fetchTicket();
  }, [reference]);

  const downloadTicket = async () => {
    if (!ticketRef.current) return;

    try {
      const canvas = await html2canvas(ticketRef.current, {
        scale: 2,
        backgroundColor: "#ffffff",
      });

      const imgData = canvas.toDataURL("image/png");
      const pdf = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4",
      });

      const imgWidth = 210;
      const imgHeight = (canvas.height * imgWidth) / canvas.width;

      pdf.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
      pdf.save(`billet-${ticket?.reference}.pdf`);
    } catch (error) {
      console.error("Erreur lors du téléchargement:", error);
    }
  };

  if (loading) {
    return (
      <main className="min-h-screen">
        <Header />
        <div className="flex items-center justify-center min-h-[60vh]">
          <p className="text-lg">Chargement de votre billet...</p>
        </div>
        <Footer />
      </main>
    );
  }

  if (error || !ticket) {
    return (
      <main className="min-h-screen">
        <Header />
        <div className="flex flex-col items-center justify-center min-h-[60vh] px-4">
          <p className="text-red-500 text-lg mb-4">{error || "Billet introuvable"}</p>
          <Link to="/evenements">
            <Button>Retour aux événements</Button>
          </Link>
        </div>
        <Footer />
      </main>
    );
  }

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
          {/* Message de succès */}
          <div className="text-center mb-12">
            <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-6">
              <CheckCircle className="w-12 h-12 text-green-600" />
            </div>
            <h1 className="text-4xl font-bold mb-4">Paiement réussi !</h1>
            <p className="text-muted-foreground text-lg">
              Votre inscription a été confirmée. Voici votre billet électronique.
            </p>
          </div>

          {/* Billet */}
          <div ref={ticketRef} className="bg-white rounded-3xl shadow-card overflow-hidden mb-8">
            {/* En-tête avec image */}
            <div className="relative h-48 bg-gradient-to-r from-accent to-accent/80">
              {ticket.event.image && (
                <img
                  src={ticket.event.image}
                  alt={ticket.event.title}
                  className="w-full h-full object-cover opacity-30"
                />
              )}
              <div className="absolute inset-0 flex items-center justify-center">
                <TicketIcon className="w-16 h-16 text-white" />
              </div>
            </div>

            {/* Contenu du billet */}
            <div className="p-8">
              <div className="grid md:grid-cols-2 gap-8">
                {/* Informations de l'événement */}
                <div className="space-y-6">
                  <div>
                    <h2 className="text-2xl font-bold mb-2">{ticket.event.title}</h2>
                    <p className="text-sm text-muted-foreground">
                      Référence: <span className="font-mono font-semibold">{ticket.reference}</span>
                    </p>
                  </div>

                  <div className="space-y-3">
                    <div className="flex items-start gap-3">
                      <Calendar className="w-5 h-5 text-accent mt-0.5" />
                      <div>
                        <p className="font-semibold">Date</p>
                        <p className="text-sm text-muted-foreground">{ticket.event.date}</p>
                      </div>
                    </div>

                    <div className="flex items-start gap-3">
                      <MapPin className="w-5 h-5 text-accent mt-0.5" />
                      <div>
                        <p className="font-semibold">Lieu</p>
                        <p className="text-sm text-muted-foreground">{ticket.event.location}</p>
                      </div>
                    </div>

                    <div className="flex items-start gap-3">
                      <TicketIcon className="w-5 h-5 text-accent mt-0.5" />
                      <div>
                        <p className="font-semibold">Tarif</p>
                        <p className="text-sm text-muted-foreground">
                          {ticket.price.label} - {durationLabels[ticket.price.duration_type]}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Informations du participant et QR Code */}
                <div className="space-y-6">
                  <div className="space-y-3">
                    <div className="flex items-start gap-3">
                      <User className="w-5 h-5 text-accent mt-0.5" />
                      <div>
                        <p className="font-semibold">Participant</p>
                        <p className="text-sm text-muted-foreground">{ticket.full_name}</p>
                        <p className="text-xs text-muted-foreground">
                          {categoryLabels[ticket.category]}
                        </p>
                      </div>
                    </div>

                    <div className="flex items-start gap-3">
                      <Mail className="w-5 h-5 text-accent mt-0.5" />
                      <div>
                        <p className="font-semibold">Email</p>
                        <p className="text-sm text-muted-foreground">{ticket.email}</p>
                      </div>
                    </div>

                    <div className="flex items-start gap-3">
                      <Phone className="w-5 h-5 text-accent mt-0.5" />
                      <div>
                        <p className="font-semibold">Téléphone</p>
                        <p className="text-sm text-muted-foreground">{ticket.phone}</p>
                      </div>
                    </div>
                  </div>

                  {/* QR Code */}
                  <div className="flex flex-col items-center p-4 bg-secondary rounded-xl">
                    {qrCode && (
                      <img src={qrCode} alt="QR Code" className="w-40 h-40 mb-2" />
                    )}
                    <p className="text-xs text-center text-muted-foreground">
                      Présentez ce QR code à l'entrée
                    </p>
                  </div>
                </div>
              </div>

              {/* Montant payé */}
              <div className="mt-8 pt-6 border-t">
                <div className="flex justify-between items-center">
                  <span className="text-lg font-semibold">Montant payé</span>
                  <span className="text-2xl font-bold text-accent">
                    {ticket.amount} {ticket.currency}
                  </span>
                </div>
              </div>
            </div>
          </div>

          {/* Actions */}
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button onClick={downloadTicket} size="lg" className="gap-2">
              <Download className="w-5 h-5" />
              Télécharger le billet (PDF)
            </Button>
            <Link to="/evenements">
              <Button variant="outline" size="lg" className="w-full sm:w-auto">
                Retour aux événements
              </Button>
            </Link>
          </div>

          {/* Note importante */}
          <div className="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-xl">
            <h3 className="font-semibold mb-2 text-blue-900">Important</h3>
            <ul className="text-sm text-blue-800 space-y-1">
              <li>• Conservez ce billet, il vous sera demandé à l'entrée</li>
              <li>• Un email de confirmation a été envoyé à {ticket.email}</li>
              <li>• Présentez-vous 15 minutes avant le début de l'événement</li>
            </ul>
          </div>
        </div>
      </section>

      <Footer />
    </main>
  );
};

export default PaymentSuccessPage;
