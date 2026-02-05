<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Tickets\TicketService;
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

    public function handle(TicketService $ticketService): void
    {
        $orderId = $this->payload['OrderID'] ?? $this->payload['order_id'] ?? null;
        $transactionId = $this->payload['TransactionID'] ?? $this->payload['transaction_id'] ?? null;
        $status = $this->payload['Status'] ?? $this->payload['status'] ?? null;

        $payment = Payment::where('id', $orderId)
            ->orWhere('gateway_reference', $transactionId)
            ->first();

        if (! $payment) {
            Log::warning('ProcessMaxiCashWebhook: payment not found', $this->payload);
            return;
        }

        if ($payment->status === PaymentStatus::Completed) {
            return;
        }

        $success = in_array(strtolower((string) $status), ['completed', 'success', 'paid', '1'], true);

        DB::transaction(function () use ($payment, $success, $ticketService) {
            if ($success) {
                $payment->update([
                    'status' => PaymentStatus::Completed,
                    'gateway_reference' => $payment->gateway_reference ?? $this->payload['TransactionID'] ?? null,
                    'paid_at' => now(),
                    'metadata' => array_merge($payment->metadata ?? [], ['webhook' => $this->payload]),
                ]);

                $ticket = $payment->ticket;
                if ($ticket) {
                    $ticketService->issueTicket($ticket);
                }

                $event = $payment->participant->event;
                $event->increment('registered');
            } else {
                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'metadata' => array_merge($payment->metadata ?? [], ['webhook' => $this->payload]),
                ]);
            }
        });
    }
}
