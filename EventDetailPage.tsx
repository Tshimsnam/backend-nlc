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

const getApiBase = () => (import.meta.env.VITE_API_URL ?? "").replace(/\/$/, "");

type EventConfig = {
  quiz_enabled: boolean;
  evaluation_enabled: boolean;
  certificate_enabled: boolean;
};

const EventDetailPage = () => {
  const { slug } = useParams();
  const navigate = useNavigate();
  const [event, setEvent] = useState<EventWithPrices | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [config, setConfig] = useState<EventConfig | null>(null);
  const [configLoading, setConfigLoading] = useState(true);

  // Afficher la section quiz/évaluation seulement si ?test=true dans l'URL
  const showActivities = new URLSearchParams(window.location.search).get("test") === "true";

  useEffect(() => {
    const apiUrl = getEventsApiUrl();
    if (!apiUrl || !slug) {
      setLoading(false);
      setConfigLoading(false);
      if (!slug) setError("URL invalide");
      return;
    }

    const fetchEvent = async () => {
      try {
        const res = await axios.get(`${apiUrl}/${slug}`);
        const data = res.data;

        if (!data) { setError("Événement introuvable"); return; }

        setEvent({ ...data, prices: data.event_prices ?? [] });

        // Charger la config quiz/évaluation
        if (data.id) {
          axios.get(`${getApiBase()}/events/${data.id}/config`)
            .then((r) => setConfig(r.data))
            .catch(() => setConfig({ quiz_enabled: false, evaluation_enabled: false, certificate_enabled: true }))
            .finally(() => setConfigLoading(false));
        } else {
          setConfigLoading(false);
        }

        // Scan QR
        const urlParams = new URLSearchParams(window.location.search);
        const fromQR = urlParams.get("qr") === "true" || urlParams.get("from") === "qr";
        if (fromQR) {
          axios.post(`${apiUrl}/${slug}/scan`).catch(() => {});
        }
      } catch {
        setError("Impossible de charger l'événement");
        setConfigLoading(false);
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

      {/* HERO */}
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
              <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 md:mb-6">{event.title}</h1>
              <p className="text-muted-foreground mb-6 md:mb-8 text-sm md:text-base leading-relaxed">
                {event.description}
              </p>
              <div className="space-y-3 md:space-y-4 text-sm md:text-base">
                <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.4 }} className="flex gap-3 items-start">
                  <CalendarDays className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                  <p className="font-medium">{event.date}{event.end_date ? ` → ${event.end_date}` : ""}</p>
                </motion.div>
                <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.5 }} className="flex gap-3 items-start">
                  <Clock className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                  <p className="font-medium">{event.time}{event.end_time ? ` – ${event.end_time}` : ""}</p>
                </motion.div>
                <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.6 }} className="flex gap-3 items-start">
                  <MapPin className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                  <div>
                    <p className="font-medium">{event.venue_details || event.location}</p>
                    {event.venue_details && event.location !== event.venue_details && (
                      <p className="text-sm text-muted-foreground">{event.location}</p>
                    )}
                  </div>
                </motion.div>
                {event.organizer && (
                  <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.65 }} className="flex gap-3 items-start">
                    <Users className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                    <div>
                      <p className="text-sm text-muted-foreground">Organisé par</p>
                      <p className="font-medium">{event.organizer}</p>
                    </div>
                  </motion.div>
                )}
                {event.capacity && (
                  <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: 0.7 }} className="flex gap-3 items-start">
                    <Users className="w-5 h-5 text-accent flex-shrink-0 mt-0.5" />
                    <div>
                      <p className="font-medium">{event.registered}/{event.capacity} places</p>
                      <div className="w-full bg-secondary rounded-full h-2 mt-2">
                        <div className="bg-accent h-2 rounded-full transition-all" style={{ width: `${(event.registered / event.capacity) * 100}%` }} />
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
          <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ duration: 0.5 }}>
            <h2 className="text-2xl md:text-3xl font-bold mb-6">À propos de l'événement</h2>
            <p className="text-muted-foreground leading-relaxed text-sm md:text-base whitespace-pre-line">
              {event.full_description || event.description}
            </p>
          </motion.div>

          {(event.contact_phone || event.contact_email) && (
            <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ duration: 0.5, delay: 0.2 }}
              className="mt-8 p-6 rounded-2xl bg-gradient-to-br from-accent/5 to-accent/10 border border-accent/20">
              <h3 className="text-lg font-semibold mb-4 flex items-center gap-2">
                <Users className="w-5 h-5 text-accent" />Contactez l'organisateur
              </h3>
              <div className="space-y-2">
                {event.contact_phone && (
                  <p className="flex items-center gap-2 text-sm md:text-base">
                    <span className="text-accent">📞</span>
                    <a href={`tel:${event.contact_phone}`} className="hover:underline">{event.contact_phone}</a>
                  </p>
                )}
                {event.contact_email && (
                  <p className="flex items-center gap-2 text-sm md:text-base">
                    <span className="text-accent">✉️</span>
                    <a href={`mailto:${event.contact_email}`} className="hover:underline">{event.contact_email}</a>
                  </p>
                )}
              </div>
            </motion.div>
          )}

          {event.registration_deadline && (
            <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ duration: 0.5, delay: 0.3 }}
              className="mt-6 p-4 rounded-xl bg-amber-50 border-2 border-amber-200 flex items-start gap-3">
              <CalendarDays className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
              <div>
                <p className="font-semibold text-amber-900 text-sm">Date limite d'inscription</p>
                <p className="text-amber-700 text-sm mt-1">
                  {new Date(event.registration_deadline).toLocaleDateString("fr-FR", { day: "numeric", month: "long", year: "numeric" })}
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
            <motion.h2 initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-2xl md:text-3xl font-bold mb-8">
              Programme
            </motion.h2>
            <div className="space-y-4">
              {event.agenda.map((item, index) => (
                <motion.div key={index} initial={{ opacity: 0, x: -20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }} transition={{ delay: index * 0.1 }}
                  className="p-5 md:p-6 rounded-2xl bg-background shadow-soft border hover:shadow-lg transition-shadow">
                  <div className="flex items-start gap-4">
                    <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center flex-shrink-0">
                      <Check className="w-5 h-5 text-accent" />
                    </div>
                    <div className="flex-1">
                      <h4 className="font-semibold text-base md:text-lg mb-1">{item.day}</h4>
                      {item.time && <p className="text-accent text-xs md:text-sm mb-2">{item.time}</p>}
                      <p className="text-muted-foreground text-sm md:text-base">{item.activities ?? item.content ?? ""}</p>
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
            <motion.h2 initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-2xl md:text-3xl font-bold mb-8 text-center">
              Nos partenaires
            </motion.h2>
            <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: 0.2 }}
              className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
              {event.sponsors.map((sponsor, index) => (
                <motion.div key={index} initial={{ opacity: 0, scale: 0.9 }} whileInView={{ opacity: 1, scale: 1 }} viewport={{ once: true }} transition={{ delay: index * 0.05 }}
                  className="flex items-center justify-center p-4 rounded-xl bg-background border hover:border-accent/50 hover:shadow-lg transition-all">
                  <p className="text-sm md:text-base font-medium text-center text-muted-foreground">{sponsor}</p>
                </motion.div>
              ))}
            </motion.div>
          </div>
        </section>
      )}

      {/* ── QUIZ & ÉVALUATION — visible uniquement avec ?test=true ── */}
      {showActivities && (
        <div className="container-custom max-w-4xl">
          <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-8">
            <h2 className="text-2xl md:text-3xl font-bold mb-2">Activités de l'événement</h2>
            <p className="text-muted-foreground text-sm">Testez vos connaissances et partagez votre expérience</p>
          </motion.div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

            {/* Card Quiz */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.1 }}
              className={`relative rounded-2xl p-6 flex flex-col gap-4 transition-all ${
                configLoading
                  ? "bg-background animate-pulse"
                  : config?.quiz_enabled
                  ? "bg-background hover:shadow-lg"
                  : "bg-gray-100 dark:bg-gray-800/50 cursor-not-allowed"
              }`}
              style={{
                border: "2px solid",
                borderColor: configLoading
                  ? "rgb(229, 231, 235)"
                  : config?.quiz_enabled
                  ? "rgb(216, 180, 254)"
                  : "rgb(156, 163, 175)",
              }}
            >
              {/* Badge statut */}
              {!configLoading && (
                <span className={`absolute top-4 right-4 text-xs font-bold px-2 py-0.5 rounded-full ${
                  config?.quiz_enabled
                    ? "bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300"
                    : "bg-gray-200 text-gray-400 dark:bg-gray-700 dark:text-gray-500"
                }`}>
                  {config?.quiz_enabled ? "Disponible" : "Bientôt"}
                </span>
              )}

              {/* Icône */}
              <div className={`w-12 h-12 rounded-2xl flex items-center justify-center ${
                config?.quiz_enabled ? "bg-purple-100 dark:bg-purple-900/30" : "bg-gray-200 dark:bg-gray-700"
              }`}>
                <svg className={`w-6 h-6 ${config?.quiz_enabled ? "text-purple-600 dark:text-purple-400" : "text-gray-400 dark:text-gray-500"}`}
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>

              <div className="flex-1">
                <h3 className={`text-lg font-bold mb-1 ${config?.quiz_enabled ? "text-gray-900 dark:text-white" : "text-gray-400 dark:text-gray-600"}`}>
                  Quiz sur l'autisme
                </h3>
                <p className={`text-sm leading-relaxed ${config?.quiz_enabled ? "text-muted-foreground" : "text-gray-400 dark:text-gray-600"}`}>
                  Testez vos connaissances sur les Troubles du Spectre de l'Autisme. Répondez aux questions et découvrez vos résultats.
                </p>
              </div>

              <button
                disabled={!config?.quiz_enabled || configLoading}
                onClick={() => config?.quiz_enabled && navigate(`/evenements/${event.slug}/quiz`)}
                className={`w-full py-3 rounded-xl font-bold text-sm transition-all ${
                  config?.quiz_enabled && !configLoading
                    ? "bg-purple-600 hover:bg-purple-700 text-white shadow-md hover:shadow-lg"
                    : "bg-gray-200 text-gray-400 cursor-not-allowed"
                }`}
              >
                {configLoading ? "Chargement…" : config?.quiz_enabled ? "Commencer le quiz →" : "Bientôt disponible"}
              </button>
            </motion.div>

            {/* Card Évaluation */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.2 }}
              className={`relative rounded-2xl p-6 flex flex-col gap-4 transition-all ${
                configLoading
                  ? "bg-background animate-pulse"
                  : config?.evaluation_enabled
                  ? "bg-background hover:shadow-lg"
                  : "bg-gray-100 dark:bg-gray-800/50 cursor-not-allowed"
              }`}
              style={{
                border: "2px solid",
                borderColor: configLoading
                  ? "rgb(229, 231, 235)"
                  : config?.evaluation_enabled
                  ? "rgb(134, 239, 172)"
                  : "rgb(156, 163, 175)",
              }}
            >
              {/* Badge statut */}
              {!configLoading && (
                <span className={`absolute top-4 right-4 text-xs font-bold px-2 py-0.5 rounded-full ${
                  config?.evaluation_enabled
                    ? "bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300"
                    : "bg-gray-200 text-gray-400 dark:bg-gray-700 dark:text-gray-500"
                }`}>
                  {config?.evaluation_enabled ? "Disponible" : "Bientôt"}
                </span>
              )}

              {/* Icône */}
              <div className={`w-12 h-12 rounded-2xl flex items-center justify-center ${
                config?.evaluation_enabled ? "bg-green-100 dark:bg-green-900/30" : "bg-gray-200 dark:bg-gray-700"
              }`}>
                <svg className={`w-6 h-6 ${config?.evaluation_enabled ? "text-green-600 dark:text-green-400" : "text-gray-400 dark:text-gray-500"}`}
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
              </div>

              <div className="flex-1">
                <h3 className={`text-lg font-bold mb-1 ${config?.evaluation_enabled ? "text-gray-900 dark:text-white" : "text-gray-400 dark:text-gray-600"}`}>
                  Évaluation du colloque
                </h3>
                <p className={`text-sm leading-relaxed ${config?.evaluation_enabled ? "text-muted-foreground" : "text-gray-400 dark:text-gray-600"}`}>
                  Partagez votre expérience et aidez-nous à améliorer nos futurs événements. Votre avis compte.
                </p>
              </div>

              <button
                disabled={!config?.evaluation_enabled || configLoading}
                onClick={() => config?.evaluation_enabled && navigate(`/evenements/${event.slug}/evaluation`)}
                className={`w-full py-3 rounded-xl font-bold text-sm transition-all ${
                  config?.evaluation_enabled && !configLoading
                    ? "bg-green-600 hover:bg-green-700 text-white shadow-md hover:shadow-lg"
                    : "bg-gray-200 text-gray-400 cursor-not-allowed"
                }`}
              >
                {configLoading ? "Chargement…" : config?.evaluation_enabled ? "Évaluer l'événement →" : "Bientôt disponible"}
              </button>
            </motion.div>

          </div>
        </div>
      </section>
      )}

      {/* BOUTON D'INSCRIPTION */}
      <section className="section-padding bg-gradient-to-br from-secondary to-secondary/50">
        <div className="container-custom text-center max-w-2xl mx-auto">
          <motion.h2 initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}
            className="text-2xl md:text-3xl lg:text-4xl font-bold mb-6">
            Prêt à participer ?
          </motion.h2>
          <motion.p initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: 0.1 }}
            className="text-muted-foreground mb-8 text-sm md:text-base">
            Inscrivez-vous maintenant et choisissez le tarif qui vous convient
          </motion.p>
          <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: 0.2 }}>
            <Button size="xl" className="gap-2 text-lg px-8 py-6" onClick={() => navigate(`/evenements/${event.slug}/inscription`)}>
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
