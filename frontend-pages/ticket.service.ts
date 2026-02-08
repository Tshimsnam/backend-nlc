/**
 * Service aligné sur le backend Laravel:
 * - GET /api/payment-modes → tableau de modes
 * - POST /api/events/{eventId}/register → { eventId = id numérique de l'event }
 */

const getApiBase = (): string => {
  const base = import.meta.env.VITE_API_URL;
  if (!base) return "";
  return base.replace(/\/$/, "");
};

export interface PaymentModeOption {
  id: string;
  label: string;
  description?: string;
  requires_phone?: boolean;
  sub_modes?: { id: string; label: string }[];
}

export interface RegisterPayload {
  event_price_id: number;
  full_name: string;
  email: string;
  phone: string;
  days?: number;
  pay_type: string;
  pay_sub_type?: string | null;
  success_url?: string;
  cancel_url?: string;
  failure_url?: string;
}

export interface RegisterSuccess {
  success: true;
  reference: string;
  redirect_url: string;
  log_id?: string;
  message?: string;
}

export interface RegisterError {
  success: false;
  message: string;
  ticket?: { reference: string; amount: string; currency: string };
}

export type RegisterResponse = RegisterSuccess | RegisterError;

export async function getPaymentModes(): Promise<PaymentModeOption[]> {
  const base = getApiBase();
  if (!base) return [];
  const res = await fetch(`${base}/payment-modes`);
  const data = await res.json();
  return Array.isArray(data) ? data : data?.data ?? [];
}

/**
 * Inscription à un événement + initiation paiement MaxiCash.
 * Backend attend l'ID numérique de l'event (pas le slug).
 */
export async function registerToEvent(
  eventId: number,
  payload: RegisterPayload
): Promise<RegisterResponse> {
  const base = getApiBase();
  if (!base) {
    return { success: false, message: "API non configurée" };
  }

  const origin = typeof window !== "undefined" ? window.location.origin : "";
  const body = {
    event_price_id: payload.event_price_id,
    full_name: payload.full_name,
    email: payload.email,
    phone: payload.phone,
    days: payload.days ?? 1,
    pay_type: payload.pay_type,
    pay_sub_type: payload.pay_sub_type ?? null,
    success_url: payload.success_url ?? `${origin}/payment/success`,
    cancel_url: payload.cancel_url ?? `${origin}/payment/cancel`,
    failure_url: payload.failure_url ?? `${origin}/payment/failure`,
  };

  const res = await fetch(`${base}/events/${eventId}/register`, {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify(body),
  });

  const data = await res.json();

  if (!res.ok) {
    return {
      success: false,
      message: data?.message ?? "Impossible d'initier le paiement.",
      ticket: data?.ticket,
    };
  }

  return data as RegisterSuccess;
}
