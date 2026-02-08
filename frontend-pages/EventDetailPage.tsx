import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import {
  CalendarDays,
  Clock,
  MapPin,
  Users,
} from "lucide-react";
import axios from "axios";

const getApiBase = () => {
  const base = import.meta.env.VITE_API_URL;
  if (!base) return "";
  return base.replace(/\/$/, "");
};

/** Backend: GET /api/events/{slug} retourne un event avec event_prices */
export interface EventPriceFromApi {
  id: number;
  event_id: number;
  category: string;
  duration_type?: string;
  amount: string;
  currency: string;
  label?: string;
  description?: string;
}

export interface EventWithPrices {
  id: number;
  title: string;
  slug: string;
  description: string;
  full_description?: string;
  date: string;
  end_date?: string;
  time?: string;
  end_time?: string;
  location?: string;
  type: string;
  status: string;
  image?: string;
  agenda?: { day?: string; time?: string; activities?: string; content?: string }[];
  capacity?: number;
  registered?: number;
  event_prices?: EventPriceFromApi[];
  prices?: EventPriceFromApi[];
}

const EventDetailPage = () => {
  const { slug } = useParams();
  const navigate = useNavigate();

  const [event, setEvent] = useState<EventWithPrices | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const apiBase = getApiBase();
    if (!apiBase || !slug) {
      setLoading(false);
      if (!slug) setError("URL invalide");
      return;
    }

    const fetchEvent = async () => {
      try {
        const res = await axios.get(`${apiBase}/events/${slug}`);
        const raw = res.data;
        const data = Array.isArray(raw) ? raw[0] : raw?.data ?? raw;
        if (!data) {
          setError("Événement introuvable");
          return;
        }
        setEvent({
          ...data,
          prices: data.event_prices ?? data.prices ?? [],
        });
      } catch {
        setError("Impossible de charger l'événement");
      } finally {
        setLoading(false);
      }
    };

    fetchEvent();
  }, [slug]);

  const goToInscription = (price: EventPriceFromApi) => {
    if (!event) return;
    navigate(`/evenements/${event.slug}/inscription`, {
      state: { priceId: price.id, event: { ...event, prices: event.prices ?? event.event_prices ?? [] } },
    });
  };

  if (loading) {
    return <p className="text-center mt-20">Chargement...</p>;
  }

  if (error) {
    return (
      <main className="min-h-screen">
        <Header />
        <p className="text-center text-red-500 mt-20">{error}</p>
        <Footer />
      </main>
    );
  }

  if (!event) {
    return null;
  }

  const prices = event.prices ?? event.event_prices ?? [];

  return (
    <main className="min-h-screen">
      <Header />

      <section className="pt-28 pb-16 bg-secondary">
        <div className="container-custom grid md:grid-cols-2 gap-10 items-center">
          <img
            src={event.image}
            alt={event.title}
            className="rounded-3xl shadow-card"
          />

          <div>
            <span className="inline-block mb-3 px-4 py-1 rounded-full bg-accent/20 text-accent text-sm">
              {event.type?.toUpperCase() ?? "EVENT"}
            </span>

            <h1 className="text-4xl font-bold mb-4">
              {event.title}
            </h1>

            <p className="text-muted-foreground mb-6">
              {event.description}
            </p>

            <div className="space-y-3 text-sm">
              <div className="flex gap-2 items-center">
                <CalendarDays className="w-4 h-4 text-accent" />
                {event.date}
                {event.end_date ? ` → ${event.end_date}` : ""}
              </div>

              {(event.time || event.end_time) && (
                <div className="flex gap-2 items-center">
                  <Clock className="w-4 h-4 text-accent" />
                  {event.time}
                  {event.end_time ? ` – ${event.end_time}` : ""}
                </div>
              )}

              {event.location && (
                <div className="flex gap-2 items-center">
                  <MapPin className="w-4 h-4 text-accent" />
                  {event.location}
                </div>
              )}

              {event.capacity != null && (
                <div className="flex gap-2 items-center">
                  <Users className="w-4 h-4 text-accent" />
                  {event.registered ?? 0}/{event.capacity} places
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      {event.full_description && (
        <section className="section-padding">
          <div className="container-custom max-w-3xl">
            <h2 className="text-2xl font-bold mb-4">
              À propos de l'événement
            </h2>
            <p className="text-muted-foreground leading-relaxed">
              {event.full_description}
            </p>
          </div>
        </section>
      )}

      {event.agenda && event.agenda.length > 0 && (
        <section className="section-padding bg-secondary">
          <div className="container-custom max-w-3xl">
            <h2 className="text-2xl font-bold mb-6">
              Programme
            </h2>

            <div className="space-y-4">
              {event.agenda.map((item, index) => (
                <div
                  key={index}
                  className="p-4 rounded-xl bg-background shadow-soft"
                >
                  <h4 className="font-semibold mb-1">
                    {item.day}
                  </h4>
                  {item.time && (
                    <p className="text-muted-foreground text-xs mb-1">
                      {item.time}
                    </p>
                  )}
                  <p className="text-muted-foreground text-sm">
                    {item.activities ?? item.content ?? ""}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {prices.length > 0 && (
        <section className="section-padding">
          <div className="container-custom">
            <h2 className="text-2xl font-bold mb-8 text-center">
              Tarifs
            </h2>

            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
              {prices.map((price) => (
                <div
                  key={price.id}
                  className="p-6 rounded-3xl border shadow-soft"
                >
                  <h3 className="font-bold text-lg mb-2">
                    {price.label || price.category}
                  </h3>

                  {price.description && (
                    <p className="text-muted-foreground text-sm mb-4">
                      {price.description}
                    </p>
                  )}

                  <div className="text-3xl font-bold mb-4">
                    {price.amount} {price.currency}
                  </div>

                  <Button
                    className="w-full"
                    onClick={() => goToInscription(price)}
                  >
                    S'inscrire
                  </Button>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      <Footer />
    </main>
  );
};

export default EventDetailPage;
