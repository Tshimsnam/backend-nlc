<?php

namespace App\Jobs;

use App\Mail\PasswordSetMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable; // <-- AJOUTE BIEN CETTE LIGNE

class SendPasswordSetMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $url;

    public function __construct($user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    public function handle()
    {
        Mail::to($this->user->email)->send(new PasswordSetMail($this->url));
    }
}
