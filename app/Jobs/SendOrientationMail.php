<?php

namespace App\Jobs;

use App\Models\Orientation\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrientationMail implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public int $uniqueFor = 3600;

    public function __construct(public int $noticeId) {}

    public function uniqueId(): string
    {
        return 'orientation-mail-'.$this->noticeId;
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        $notice = Notice::find($this->noticeId);
        if (! $notice || $notice->email_status === 'sent') {
            return;
        }
        if ($notice->appointment_id && \App\Models\Orientation\Appointment::whereKey($notice->appointment_id)->value('status') !== $notice->expected_status) {
            $notice->update(['email_status' => 'obsolete']);

            return;
        }
        if ($notice->expires_at?->isPast()) {
            $notice->update(['email_status' => 'expired']);

            return;
        }
        Mail::send(['html' => 'emails.orientation', 'text' => 'emails.orientation-text'], ['notice' => $notice], fn ($mail) => $mail->to($notice->recipient)->subject('NLC — '.$notice->title));
        $notice->update(['email_status' => 'sent', 'sent_at' => now()]);
    }

    public function failed(?\Throwable $e): void
    {
        Notice::whereKey($this->noticeId)->update(['email_status' => 'failed']);
    }
}
