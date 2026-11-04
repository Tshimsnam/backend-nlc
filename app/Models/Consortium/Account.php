<?php

namespace App\Models\Consortium;

use App\Services\Consortium\Notifications;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Account extends Authenticatable
{
    protected $table = 'consortium_accounts';

    protected $fillable = ['structure_id', 'name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token', 'verification_token', 'verification_expires_at'];

    protected $casts = ['password' => 'hashed', 'email_verified_at' => 'datetime', 'verification_expires_at' => 'datetime'];

    public function structure()
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }

    public function sendVerification(): void
    {
        $token = Str::random(64);
        $this->forceFill(['verification_token' => hash('sha256', $token), 'verification_expires_at' => now()->addHour()])->save();
        $url = rtrim(config('consortium.frontend_url'), '/').'/espace-partenaire/verifier?'.http_build_query(['email' => $this->email, 'token' => $token]);
        app(Notifications::class)->account($this, 'Confirmez votre adresse e-mail', 'Bienvenue dans l’espace consortium NLC. Confirmez votre adresse pour compléter votre dossier. Ce lien est valable une heure.', $url, $this->verification_expires_at);
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = rtrim(config('consortium.frontend_url'), '/').'/espace-partenaire/reinitialiser?'.http_build_query(['email' => $this->email, 'token' => $token]);
        app(Notifications::class)->account($this, 'Réinitialisation de votre mot de passe', 'Vous avez demandé un nouveau mot de passe pour votre espace partenaire NLC. Ce lien est valable une heure. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.', $url, now()->addHour());
    }
}
