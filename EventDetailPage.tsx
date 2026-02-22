import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { CalendarDays, Clock, MapPin, Users, ArrowLeft, Check } from "lucide-react";
import axios from "axios";
import { motion } from "framer-motion";
import type { EventWithPrices } from "@/types/event";

const getEventsApiUrl = () => {
  const base = import.meta.env.VITE_API_URL;
  if (!base) return "";
  return `${base.replace(/\/$/, "")}/events`;
};

const EventDetailPage = () => {
  const { slug } = useParams();
  const navigate = useNavigate();
  const [event, setEvent] = useState<EventWithPrices | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const apiUrl = getEventsApiUrl();
    if (!apiUrl || !slug) {
      setLoading(false);
      if (!slug) setError("URL invalide");
      return;
    }

    const fetchEvent = async () => {
      try {
        // Backend route: GET /events/{event:slug}
        const res = await axios.get(`${apiUrl}/${slug}`);
        // Backend renvoie directement l'événement avec event_prices
        const data = res.data;

        if (!data) {
          setError("Événement introuvable");
          return;
        }

        setEvent({
          ...data,
          prices: data.event_prices ?? [],
        });

        // Enregistrer le scan du QR code seulement si l'utilisateur vient d'un QR code
        const urlParams = new URLSearchParams(window.location.search);
        const fromQR = urlParams.get('qr') === 'true' || urlParams.get('from') === 'qr';
        
        if (fromQR) {
          try {
            await axios.post(`${apiUrl}/${slug}/scan`);
            console.log('✅ Scan QR événement enregistré');
          } catch (scanError) {
            console.error('❌ Erreur lors de l\'enregistrement du scan:', scanError);
            // Ne pas bloquer l'utilisateur si le scan échoue
          }
        }
      } catch {
        setError("Impossible de charger l'événement");
      } finally {
        setLoading(false);
      }
    };

    fetchEvent();
  }, [slug]);

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

  if (error) {
    return (
      <main className="min-h-screen">
        <Header />
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="container-custom text-center py-20"
        >
          <p className="text-red-500 text-lg mb-4">{error}</p>
          <Button onClick={() => navigate("/evenements")}>
            <ArrowLeft className="w-4 h-4 mr-2" />
            Retour aux événements
          </Button>
        </motion.div>
        <Footer />
      </main>
    );
  }

  if (!event) return null;

  return (
    <main className="min-h-screen">
      <Header />

      {/* HERO avec animation */}
      <section className="pt-28 pb-12 md:pb-16 bg-gradient-to-br from-secondary via-secondary/80 to-secondary">
        <div className="container-custom">
          <motion.button
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            onClick={() => navigate("/evenements")}
            className="flex items-center gap-2 text-accent hover:underline mb-6 group"
          >
            <ArrowLeft className="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
            Retour aux événements
          </motion.button>

          <div className="grid md:grid-cols-2 gap-8 md:gap-10 items-center">
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.5 }}
              className="relative rounded-3xl overflow-hidden shadow-2xl"
            >
              <img src={event.image} alt={event.title} className="w-full h-auto" />
              <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
            </motion.div>

            <motion.div
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.5, delay: 0.2 }}
            >
              <motion.span
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.3 }}
                className="inline-block mb-3 px-4 py-1 rounded-full bg-accent/20 text-accent text-xs md:text-sm font-medium"
              >
                {event.type.toUpperCase()}
              </motion.span>
              <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 md:mb-6">
                {event.title}
              </h1>
              <p className="text-muted-foreground mb-6 md:mb-8 text-sm md:text-base leading-relaxed">
                {event.description}
              </p>
              <div className="space-y-3 md:space-y-4 text-sm md:text-base">
                <motion.div
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.4 }}
                  className="flex gap-3 items-start"
                >
                  <CalendarDays className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                  <div>
                    <p className="font-medium">
                      {event.date}
                      {event.end_date ? ` → ${event.end_date}` : ""}
                    </p>
                  </div>
                </motion.div>
                <motion.div
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.5 }}
                  className="flex gap-3 items-start"
                >
                  <Clock className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                  <div>
                    <p className="font-medium">
                      {event.time}
                      {event.end_time ? ` – ${event.end_time}` : ""}
                    </p>
                  </div>
                </motion.div>
                <motion.div
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.6 }}
                  className="flex gap-3 items-start"
                >
                  <MapPin className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                  <div>
                    <p className="font-medium">{event.venue_details || event.location}</p>
                    {event.venue_details && event.location !== event.venue_details && (
                      <p className="text-sm text-muted-foreground">{event.location}</p>
                    )}
                  </div>
                </motion.div>
                {event.organizer && (
                  <motion.div
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.65 }}
                    className="flex gap-3 items-start"
                  >
                    <Users className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                    <div>
                      <p className="text-sm text-muted-foreground">Organisé par</p>
                      <p className="font-medium">{event.organizer}</p>
                    </div>
                  </motion.div>
                )}
                {event.capacity && (
                  <motion.div
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.7 }}
                    className="flex gap-3 items-start"
                  >
                    <Users className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                    <div>
                      <p className="font-medium">
                        {event.registered}/{event.capacity} places
                      </p>
                      <div className="w-full bg-secondary rounded-full h-2 mt-2">
                        <div
                          className="bg-accent h-2 rounded-full transition-all"
                          style={{
                            width: `${(event.registered / event.capacity) * 100}%`,
                          }}
                        />
                      </div>
                    </div>
                  </motion.div>
                )}
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* DESCRIPTION */}
      <section className="section-padding">
        <div className="container-custom max-w-4xl">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5 }}
          >
            <h2 className="text-2xl md:text-3xl font-bold mb-6">À propos de l'événement</h2>
            <p className="text-muted-foreground leading-relaxed text-sm md:text-base whitespace-pre-line">
              {event.full_description || event.description}
            </p>
          </motion.div>

          {/* Informations de contact */}
          {(event.contact_phone || event.contact_email) && (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.2 }}
              className="mt-8 p-6 rounded-2xl bg-gradient-to-br from-accent/5 to-accent/10 border border-accent/20"
            >
              <h3 className="text-lg font-semibold mb-4 flex items-center gap-2">
                <Users className="w-5 h-5 text-accent" />
                Contactez l'organisateur
              </h3>
              <div className="space-y-2">
                {event.contact_phone && (
                  <p className="flex items-center gap-2 text-sm md:text-base">
                    <span className="text-accent">📞</span>
                    <a href={`tel:${event.contact_phone}`} className="hover:underline">
                      {event.contact_phone}
                    </a>
                  </p>
                )}
                {event.contact_email && (
                  <p className="flex items-center gap-2 text-sm md:text-base">
                    <span className="text-accent">✉️</span>
                    <a href={`mailto:${event.contact_email}`} className="hover:underline">
                      {event.contact_email}
                    </a>
                  </p>
                )}
              </div>
            </motion.div>
          )}

          {/* Date limite d'inscription */}
          {event.registration_deadline && (
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.3 }}
              className="mt-6 p-4 rounded-xl bg-amber-50 border-2 border-amber-200 flex items-start gap-3"
            >
              <CalendarDays className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
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
        </div>
      </section>

      {/* AGENDA */}
      {event.agenda && (
        <section className="section-padding bg-secondary">
          <div className="container-custom max-w-4xl">
            <motion.h2
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              className="text-2xl md:text-3xl font-bold mb-8"
            >
              Programme
            </motion.h2>
            <div className="space-y-4">
              {event.agenda.map((item, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, x: -20 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: index * 0.1 }}
                  className="p-5 md:p-6 rounded-2xl bg-background shadow-soft border hover:shadow-lg transition-shadow"
                >
                  <div className="flex items-start gap-4">
                    <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center flex-shrink-0">
                      <Check className="w-5 h-5 text-accent" />
                    </div>
                    <div className="flex-1">
                      <h4 className="font-semibold text-base md:text-lg mb-1">{item.day}</h4>
                      {item.time && (
                        <p className="text-accent text-xs md:text-sm mb-2">{item.time}</p>
                      )}
                      <p className="text-muted-foreground text-sm md:text-base">
                        {item.activities ?? item.content ?? ""}
                      </p>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* SPONSORS */}
      {event.sponsors && event.sponsors.length > 0 && (
        <section className="section-padding">
          <div className="container-custom max-w-4xl">
            <motion.h2
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              className="text-2xl md:text-3xl font-bold mb-8 text-center"
            >
              Nos partenaires
            </motion.h2>
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.2 }}
              className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6"
            >
              {event.sponsors.map((sponsor, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, scale: 0.9 }}
                  whileInView={{ opacity: 1, scale: 1 }}
                  viewport={{ once: true }}
                  transition={{ delay: index * 0.05 }}
                  className="flex items-center justify-center p-4 rounded-xl bg-background border hover:border-accent/50 hover:shadow-lg transition-all"
                >
                  <p className="text-sm md:text-base font-medium text-center text-muted-foreground">
                    {sponsor}
                  </p>
                </motion.div>
              ))}
            </motion.div>
          </div>
        </section>
      )}

      {/* BOUTON D'INSCRIPTION */}
      <section className="section-padding bg-gradient-to-br from-secondary to-secondary/50">
        <div className="container-custom text-center max-w-2xl mx-auto">
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-2xl md:text-3xl lg:text-4xl font-bold mb-6"
          >
            Prêt à participer ?
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.1 }}
            className="text-muted-foreground mb-8 text-sm md:text-base"
          >
            Inscrivez-vous maintenant et choisissez le tarif qui vous convient
          </motion.p>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.2 }}
          >
            <Button
              size="xl"
              className="gap-2 text-lg px-8 py-6"
              onClick={() => navigate(`/evenements/${event.slug}/inscription`)}
            >
              S'inscrire maintenant
              <ArrowLeft className="w-5 h-5 rotate-180" />
            </Button>
          </motion.div>
        </div>
      </section>

      <Footer />
    </main>
  );
};

export default EventDetailPage;
