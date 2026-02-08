import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { CalendarDays, Clock, MapPin, Users } from "lucide-react";
import axios from "axios";
import type { EventWithPrices } from "@/types/event";
import type { EventPrice } from "@/types/eventPrice";

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
      } catch {
        setError("Impossible de charger l'événement");
      } finally {
        setLoading(false);
      }
    };

    fetchEvent();
  }, [slug]);

  const goToInscription = (price: EventPrice) => {
    if (!event) return;
    navigate(`/evenements/${event.slug}/inscription`, {
      state: { priceId: price.id, event },
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

  return (
    <main className="min-h-screen">
      <Header />

      {/* HERO */}
      <section className="pt-28 pb-16 bg-secondary">
        <div className="container-custom grid md:grid-cols-2 gap-10 items-center">
          <img src={event.image} alt={event.title} className="rounded-3xl shadow-card" />
          <div>
            <span className="inline-block mb-3 px-4 py-1 rounded-full bg-accent/20 text-accent text-sm">
              {event.type.toUpperCase()}
            </span>
            <h1 className="text-4xl font-bold mb-4">{event.title}</h1>
            <p className="text-muted-foreground mb-6">{event.description}</p>
            <div className="space-y-3 text-sm">
              <div className="flex gap-2 items-center">
                <CalendarDays className="w-4 h-4 text-accent" />
                {event.date}
                {event.end_date ? ` → ${event.end_date}` : ""}
              </div>
              <div className="flex gap-2 items-center">
                <Clock className="w-4 h-4 text-accent" />
                {event.time}
                {event.end_time ? ` – ${event.end_time}` : ""}
              </div>
              <div className="flex gap-2 items-center">
                <MapPin className="w-4 h-4 text-accent" />
                {event.location}
              </div>
              {event.capacity && (
                <div className="flex gap-2 items-center">
                  <Users className="w-4 h-4 text-accent" />
                  {event.registered}/{event.capacity} places
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* DESCRIPTION */}
      <section className="section-padding">
        <div className="container-custom max-w-3xl">
          <h2 className="text-2xl font-bold mb-4">À propos de l'événement</h2>
          <p className="text-muted-foreground leading-relaxed">{event.full_description}</p>
        </div>
      </section>

      {/* AGENDA */}
      {event.agenda && (
        <section className="section-padding bg-secondary">
          <div className="container-custom max-w-3xl">
            <h2 className="text-2xl font-bold mb-6">Programme</h2>
            <div className="space-y-4">
              {event.agenda.map((item, index) => (
                <div key={index} className="p-4 rounded-xl bg-background shadow-soft">
                  <h4 className="font-semibold mb-1">{item.day}</h4>
                  {item.time && <p className="text-muted-foreground text-xs mb-1">{item.time}</p>}
                  <p className="text-muted-foreground text-sm">{item.activities ?? item.content ?? ""}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* TARIFS */}
      {event.prices && event.prices.length > 0 && (
        <section className="section-padding">
          <div className="container-custom">
            <h2 className="text-2xl font-bold mb-8 text-center">Tarifs</h2>
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
              {event.prices.map((price) => (
                <div key={price.id} className="p-6 rounded-3xl border shadow-soft">
                  <h3 className="font-bold text-lg mb-2">{price.label || price.category}</h3>
                  {price.description && <p className="text-muted-foreground text-sm mb-4">{price.description}</p>}
                  <div className="text-3xl font-bold mb-4">
                    {price.amount} {price.currency}
                  </div>
                  <Button className="w-full" onClick={() => goToInscription(price)}>
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
