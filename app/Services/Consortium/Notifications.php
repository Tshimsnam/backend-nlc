<?php

namespace App\Services\Consortium;

use App\Jobs\SendConsortiumMail;
use App\Models\Consortium\Account;
use App\Models\Consortium\Application;
use App\Models\Consortium\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Notifications
{
    public function account(Account $account, string $title, string $body, ?string $url = null, ?\DateTimeInterface $expiresAt = null): void
    {
        $this->create($account->id, $account->email, $title, $body, $url ?? rtrim(config('consortium.frontend_url'), '/').'/espace-partenaire', $expiresAt);
    }

    public function partners(Application $application, string $title, string $body): void
    {
        foreach ($application->structure->accounts as $account) {
            $this->account($account, $title, $body);
        }
    }

    public function admin(Application $application, string $title, string $body): void
    {
        $this->create(null, config('consortium.secretariat_email'), $title, $body, route('admin.consortium.show', $application));
    }

    private function create(?int $accountId, string $recipient, string $title, string $body, string $url, ?\DateTimeInterface $expiresAt = null): void
    {
        $notification = Notification::create(['account_id' => $accountId, 'recipient' => $recipient, 'title' => $title, 'body' => $body, 'action_url' => $url, 'expires_at' => $expiresAt]);
        DB::afterCommit(function () use ($notification) {
            try {
                SendConsortiumMail::dispatch($notification->id);
            } catch (\Throwable $e) {
                // Le dossier et l'alerte restent enregistrés ; la commande de reprise relancera l'envoi.
                Log::error('Consortium: mise en file du mail impossible', ['notification_id' => $notification->id]);
            }
        });
    }
}
