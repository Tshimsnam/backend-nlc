<?php

namespace App\Services\Orientation;

use App\Models\Orientation\Appointment;
use App\Models\Orientation\Payment;
use App\Models\Orientation\Professional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Payments
{
    public function start(Appointment $appointment, string $gateway): array
    {
        $payment = DB::transaction(function () use ($appointment, $gateway) {
            Professional::whereKey($appointment->professional_id)->lockForUpdate()->firstOrFail();
            $a = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            if ($a->status !== 'awaiting_payment' || ! $a->accepted_at || $a->expires_at->isPast()) {
                throw ValidationException::withMessages(['payment' => 'Cette proposition ne peut plus être payée.']);
            }
            if ($gateway === 'maxicash' && ! config('orientation.maxicash_enabled')) {
                throw ValidationException::withMessages(['payment' => 'Le paiement MaxiCash n’est pas activé.']);
            }
            if ($gateway === 'manual' && ! filled(Settings::get('manual_payment_instructions'))) {
                throw ValidationException::withMessages(['payment' => 'Aucun paiement électronique à vérifier manuellement n’est configuré.']);
            }
            // Une seule tentative ouverte, même en cas de double clic ou changement de moyen.
            $existing = $a->payments()->whereIn('status', ['starting', 'pending', 'verification_required', 'paid'])->latest('id')->first();
            if ($existing) {
                return $existing;
            }

            return Payment::create(['appointment_id' => $a->id, 'reference' => 'ORP-'.strtoupper(Str::random(20)), 'gateway' => $gateway, 'amount_cents' => $a->fee_cents, 'status' => $gateway === 'manual' ? 'pending' : 'starting']);
        });
        if ($payment->gateway === 'maxicash' && $payment->status === 'starting' && ! $payment->gateway_log_id) {
            // Le verrou d'unicité protège également contre deux appels HTTP concurrents.
            $claimed = Payment::whereKey($payment->id)->where('status', 'starting')->update(['status' => 'verification_required']);
            if ($claimed) {
                $this->initiate($payment);
            }
            $payment->refresh();
        }

        return ['payment_id' => $payment->id, 'reference' => $payment->reference, 'status' => $payment->status, 'gateway' => $payment->gateway, 'redirect_url' => $payment->redirect_url,
            'instructions' => $payment->gateway === 'manual' ? Settings::get('manual_payment_instructions') : null];
    }

    private function credentials(): array
    {
        $id = config('services.maxicash.merchant_id');
        $password = config('services.maxicash.merchant_password');
        if (! $id || ! $password) {
            throw ValidationException::withMessages(['payment' => 'Le paiement n’est pas encore configuré. Contactez le secrétariat.']);
        }

        return ['MerchantID' => $id, 'MerchantPassword' => $password];
    }

    private function initiate(Payment $p): void
    {
        try {
            $credentials = $this->credentials();
            $return = Settings::url('/espace-famille?dossier='.$p->appointment->consultation_request_id.'&paiement=retour');
            $response = Http::connectTimeout(10)->timeout(25)->acceptJson()->asJson()->post(rtrim(config('services.maxicash.api_url'), '/').'/Integration/PayEntryWeb', $credentials + [
                'PayType' => 'MaxiCash', 'Amount' => (string) $p->amount_cents, 'Currency' => 'maxiDollar', 'Language' => 'fr', 'Reference' => $p->reference,
                'SuccessURL' => $return, 'FailureURL' => $return, 'CancelURL' => $return, 'NotifyURL' => route('orientation.payment.callback'),
                'Email' => $p->appointment->consultation->email,
            ]);
            $log = $response->json('LogID') ?: $response->json('ResponseData');
            if (! $response->successful() || strtolower((string) $response->json('ResponseStatus')) !== 'success' || ! is_scalar($log) || ! preg_match('/^[a-zA-Z0-9_-]{1,150}$/', (string) $log)) {
                // Une réponse ambiguë reste à vérifier ; pas de seconde tentative qui pourrait doubler le débit.
                $p->update(['review_note' => 'Réponse d’initialisation à vérifier auprès du prestataire.']);

                return;
            }
            $p->update(['gateway_log_id' => (string) $log, 'status' => 'pending', 'redirect_url' => rtrim(config('services.maxicash.redirect_base'), '/').'/payentryweb?logid='.rawurlencode((string) $log)]);
        } catch (ValidationException $e) {
            $p->update(['status' => 'failed', 'review_note' => 'Configuration du prestataire incomplète.']);
            throw $e;
        } catch (\Throwable $e) {
            $p->update(['review_note' => 'Délai dépassé ou erreur prestataire. Vérification nécessaire avant nouvelle tentative.']);
        }
    }

    public function verify(Payment $p): void
    {
        if ($p->gateway !== 'maxicash' || $p->status === 'paid' || ! config('orientation.maxicash_enabled')) {
            return;
        }
        $response = Http::connectTimeout(10)->timeout(25)->acceptJson()->asJson()->post(rtrim(config('services.maxicash.api_url'), '/').'/Integration/CheckPaymentStatusByReference', $this->credentials() + ['Reference' => $p->reference]);
        if (! $response->successful()) {
            return;
        }
        $body = $response->json();
        $data = $body['ResponseData'] ?? null;
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        // ResponseStatus=success signifie seulement que la requête a réussi.
        // Seule une preuve complète du règlement issue de l'API authentifiée est acceptée.
        if (! is_array($data) || ($data['Reference'] ?? null) !== $p->reference || ! in_array(strtolower((string) ($data['Status'] ?? '')), ['paid', 'completed'], true)
            || ! isset($data['Amount']) || ! preg_match('/^\d+$/', (string) $data['Amount']) || (int) $data['Amount'] !== $p->amount_cents
            || strtolower((string) ($data['Currency'] ?? '')) !== 'maxidollar' || empty($data['TransactionID'])) {
            $p->update(['status' => 'verification_required', 'review_note' => 'Le prestataire n’a pas retourné une preuve complète de règlement (référence, statut payé, montant en cents, devise, transaction). Vérifier sur le compte marchand.']);

            return;
        }
        app(Workflow::class)->paid($p, null, (string) $data['TransactionID']);
    }
}
