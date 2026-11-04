<?php

namespace App\Jobs;

use App\Models\Orientation\Payment;
use App\Services\Orientation\Payments;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyOrientationPayment implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 35;

    public int $uniqueFor = 180;

    public function __construct(public int $paymentId) {}

    public function uniqueId(): string
    {
        return 'orientation-payment-'.$this->paymentId;
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(Payments $payments): void
    {
        $p = Payment::find($this->paymentId);
        if ($p) {
            $payments->verify($p);
        }
    }
}
