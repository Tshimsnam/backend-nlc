import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import {
  Calendar,
  MapPin,
  Clock,
  ArrowRight,
  Filter,
  CalendarDays,
} from "lucide-react";
import { useI18n } from "@/lib/i18n";
import axios from "axios";

const getApiBase = () => {
  const base = import.meta.env.VITE_API_URL;
  if (!base) return "";
  return base.replace(/\/$/, "");
};

const EVENTS_URL = () => `${getApiBase()}/events`;

/** Réponse backend: GET /api/events retourne un tableau d'events (pas de wrapper data) */
interface EventItem {
  id: number;
  title: string;
  slug: string;
  description: string;
  date: string;
  end_date?: string;
  time?: string;
  end_time?: string;
  location?: string;
  type: string;
  status: string;
  image?: string;
  event_prices?: unknown[];
}

const EvenementsPage = () => {
  const { t } = useI18n();

  const [events, setEvents] = useState<EventItem[]>([]);
  const [activeFilter, setActiveFilter] = useState("all");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchEvents = async () => {
      try {
        const res = await axios.get(EVENTS_URL());
        const raw = res.data;
        // Backend retourne un tableau directement
        const list = Array.isArray(raw) ? raw : raw?.data ?? [];
        setEvents(list);
      } catch {
        setError("Erreur lors du chargement des événements");
      } finally {
        setLoading(false);
      }
    };

    fetchEvents();
  }, []);

  const eventTypes = [...new Set(events.map((e) => e.type).filter(Boolean))];

  const filters = [
    { id: "all", label: t("evenementsPage.filter.all") },
    { id: "upcoming", label: t("evenementsPage.filter.upcoming") },
    { id: "past", label: t("evenementsPage.filter.past") },
    ...eventTypes.map((type) => ({
      id: type,
      label: t(`evenementsPage.filter.${type}`) || type,
    })),
  ];

  const filteredEvents =
    activeFilter === "all"
      ? events
      : activeFilter === "upcoming"
        ? events.filter((e) => e.status === "upcoming")
        : activeFilter === "past"
          ? events.filter((e) => e.status === "past")
          : events.filter((e) => e.type === activeFilter);

  return (
    <main className="min-h-screen">
      <Header />

      <section className="pt-32 pb-20 bg-[hsl(220,63%,16%)] relative overflow-hidden">
        <div className="container-custom relative z-10 max-w-3xl">
          <div className="inline-flex items-center gap-2 bg-accent/20 text-accent px-4 py-2 rounded-full mb-6">
            <Calendar className="w-4 h-4" />
            <span className="text-sm font-medium">
              {t("evenementsPage.hero.badge")}
            </span>
          </div>

          <h1 className="font-heading text-5xl font-bold text-white mb-6">
            {t("evenementsPage.hero.title")}
          </h1>

          <p className="text-white/80 text-lg">
            {t("evenementsPage.hero.subtitle")}
          </p>
        </div>
      </section>

      <section className="py-8 bg-background border-b sticky top-0 z-40">
        <div className="container-custom flex flex-wrap items-center gap-3">
          <Filter className="w-5 h-5 text-muted-foreground" />
          {filters.map((filter) => (
            <button
              key={filter.id}
              onClick={() => setActiveFilter(filter.id)}
              className={`px-4 py-2 rounded-full text-sm font-medium transition ${
                activeFilter === filter.id
                  ? "bg-accent text-accent-foreground"
                  : "bg-secondary text-muted-foreground hover:bg-accent/20"
              }`}
            >
              {filter.label}
            </button>
          ))}
        </div>
      </section>

      <section className="section-padding">
        <div className="container-custom">
          {loading && (
            <p className="text-center text-muted-foreground">
              Chargement des événements...
            </p>
          )}

          {error && (
            <p className="text-center text-red-500">{error}</p>
          )}

          {!loading && !error && (
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
              {filteredEvents.map((event) => (
                <div
                  key={event.id}
                  className="group bg-card rounded-3xl overflow-hidden shadow-soft border hover:-translate-y-2 transition"
                >
                  <div className="relative h-48 overflow-hidden">
                    <img
                      src={event.image}
                      alt={event.title}
                      className="w-full h-full object-cover transition-transform group-hover:scale-110"
                    />
                    <div className="absolute top-4 right-4">
                      <span
                        className={`px-3 py-1 rounded-full text-xs font-medium ${
                          event.status === "upcoming"
                            ? "bg-green-500/20 text-green-600"
                            : "bg-gray-500/20 text-gray-600"
                        }`}
                      >
                        {event.status}
                      </span>
                    </div>
                  </div>

                  <div className="p-6">
                    <h3 className="text-xl font-bold mb-3">
                      {event.title}
                    </h3>

                    <p className="text-sm text-muted-foreground mb-4 line-clamp-3">
                      {event.description}
                    </p>

                    <div className="space-y-2 mb-6 text-sm">
                      <div className="flex items-center gap-2">
                        <CalendarDays className="w-4 h-4 text-accent" />
                        {event.date}
                      </div>
                      {event.time && (
                        <div className="flex items-center gap-2">
                          <Clock className="w-4 h-4 text-accent" />
                          {event.time}
                        </div>
                      )}
                      {event.location && (
                        <div className="flex items-center gap-2">
                          <MapPin className="w-4 h-4 text-accent" />
                          {event.location}
                        </div>
                      )}
                    </div>

                    <Button variant="accent" size="sm" className="w-full" asChild>
                      <Link to={`/evenements/${event.slug}`}>
                        {event.status === "past"
                          ? t("evenementsPage.cta.viewDetails")
                          : t("evenementsPage.cta.register")}
                        <ArrowRight className="w-4 h-4 ml-1" />
                      </Link>
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}

          {!loading && filteredEvents.length === 0 && (
            <p className="text-center text-muted-foreground mt-20">
              Aucun événement trouvé
            </p>
          )}
        </div>
      </section>

      <section className="py-20 bg-secondary">
        <div className="container-custom text-center">
          <h2 className="text-4xl font-bold mb-6">
            {t("evenementsPage.cta.title")}
          </h2>
          <p className="text-muted-foreground max-w-2xl mx-auto mb-8">
            {t("evenementsPage.cta.subtitle")}
          </p>
          <Button variant="accent" size="xl" asChild>
            <a href="/contact">
              {t("evenementsPage.cta.button")}
            </a>
          </Button>
        </div>
      </section>

      <Footer />
    </main>
  );
};

export default EvenementsPage;
