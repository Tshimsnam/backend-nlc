<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMaxiCashWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $payload
    ) {}

    public function handle(): void
    {
        $reference = $this->payload['Reference'] ?? $this->payload['reference'] ?? null;
        $status = $this->payload['Status'] ?? $this->payload['status'] ?? null;
        $success = in_array(strtolower((string) $status), ['completed', 'success', 'paid', '1'], true);

        if (! empty($reference)) {
            $ticket = Ticket::where('reference', $reference)->first();
            if ($ticket) {
                $this->handleTicketNotification($ticket, $success);
                return;
            }
        }

        $this->handlePaymentNotification($success);
    }

    private function handleTicketNotification(Ticket $ticket, bool $success): void
    {
        if ($ticket->payment_status === 'completed') {
            return;
        }

        DB::transaction(function () use ($ticket, $success) {
            $ticket->update([
                'payment_status' => $success ? 'completed' : 'failed',
            ]);

            if ($success && $ticket->event) {
                $ticket->event->increment('registered');
            }
        });

        Log::info('MaxiCash webhook: ticket updated', [
            'reference' => $ticket->reference,
            'payment_status' => $ticket->fresh()->payment_status,
        ]);
    }

    private function handlePaymentNotification(bool $success): void
    {
        $orderId = $this->payload['OrderID'] ?? $this->payload['order_id'] ?? null;
        $transactionId = $this->payload['TransactionID'] ?? $this->payload['transaction_id'] ?? null;

        $payment = Payment::where('id', $orderId)
            ->orWhere('gateway_reference', $transactionId)
            ->first();

        if (! $payment) {
            Log::warning('ProcessMaxiCashWebhook: payment or ticket not found', $this->payload);
            return;
        }

        if ($payment->status === PaymentStatus::Completed) {
            return;
        }

        DB::transaction(function () use ($payment, $success) {
            $payment->update([
                'status' => $success ? PaymentStatus::Completed : PaymentStatus::Failed,
                'gateway_reference' => $payment->gateway_reference ?? $this->payload['TransactionID'] ?? null,
                'paid_at' => $success ? now() : null,
                'metadata' => array_merge($payment->metadata ?? [], ['webhook' => $this->payload]),
            ]);

            if ($success && $payment->participant && $payment->participant->event) {
                $payment->participant->event->increment('registered');
            }
        });
    }
}
