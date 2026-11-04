<?php

namespace App\Jobs;

use App\Models\Consortium\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendConsortiumMail implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public int $uniqueFor = 3600;

    public function __construct(public int $notificationId) {}

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function uniqueId(): string
    {
        return 'consortium-mail-'.$this->notificationId;
    }

    public function handle(): void
    {
        $notification = Notification::find($this->notificationId);
        if (! $notification || $notification->email_status === 'sent') {
            return;
        }
        if ($notification->expires_at?->isPast()) {
            $notification->update(['email_status' => 'expired']);

            return;
        }
        Mail::send('emails.consortium', ['notification' => $notification], function ($mail) use ($notification) {
            $mail->to($notification->recipient)->subject('NLC Consortium — '.$notification->title);
        });
        $notification->update(['email_status' => 'sent', 'sent_at' => now()]);
    }

    public function failed(?\Throwable $exception): void
    {
        Notification::whereKey($this->notificationId)->update(['email_status' => 'failed']);
    }
}
