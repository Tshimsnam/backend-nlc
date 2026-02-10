// PARTIE 2: États du composant (à ajouter après const API_URL)

const [event, setEvent] = useState<Event | null>(null);
const [selectedPrice, setSelectedPrice] = useState<EventPrice | null>(null);
const [paymentModes, setPaymentModes] = useState<PaymentMode[]>([]);
const [loading, setLoading] = useState(true);
const [error, setError] = useState<string | null>(null);
const [step, setStep] = useState(1);
const [submitting, setSubmitting] = useState(false);

// NOUVEAUX ÉTATS pour paiement en caisse
const [qrData, setQrData] = useState<string | null>(null);
const [ticketData, setTicketData] = useState<any>(null);
const [paymentMode, setPaymentMode] = useState<'online' | 'cash' | null>(null);

const [formData, setFormData] = useState<RegistrationFormData>({
  event_price_id: 0,
  full_name: "",
  email: "",
  phone: "",
  days: 1,
  pay_type: "",
  pay_sub_type: "",
});
