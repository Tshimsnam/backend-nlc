<?php

\Illuminate\Support\Facades\Artisan::command('orientation:maintain', function () {
    app(\App\Services\Orientation\Maintenance::class)->run();
    $this->info('Échéances, rappels, alertes 72h et reprises de notifications traités.');
})->purpose('Traiter le suivi des demandes d’orientation');
\Illuminate\Support\Facades\Schedule::command('orientation:maintain')->everyFiveMinutes()->withoutOverlapping();

\Illuminate\Support\Facades\Artisan::command('consortium:retry-mail {--limit=100}', function () {
    $limit = min(1000, max(1, (int) $this->option('limit')));
    $count = 0;
    \App\Models\Consortium\Notification::whereIn('email_status', ['pending', 'failed'])
        ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
        ->where('updated_at', '<', now()->subHour())->orderBy('id')->limit($limit)->get()
        ->each(function ($notification) use (&$count) {
            \App\Jobs\SendConsortiumMail::dispatch($notification->id);
            $count++;
        });
    $this->info("{$count} notification(s) remise(s) en file.");
})->purpose('Relancer les e-mails consortium en attente ou en échec depuis plus d’une heure');

\Illuminate\Support\Facades\Artisan::command('consortium:remind', function () {
    $count = 0;
    \App\Models\Consortium\InformationRequest::whereNull('responded_at')->whereNull('reminded_at')
        ->whereNotNull('due_on')->whereDate('due_on', '<=', now()->addDays(2))
        ->whereHas('application', fn ($query) => $query->where('status', 'needs_information'))
        ->limit(200)->get()->each(function ($request) use (&$count) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, &$count) {
                $request = $request->newQuery()->whereKey($request->id)->lockForUpdate()->first();
                if ($request->reminded_at || $request->responded_at) { return; }
                app(\App\Services\Consortium\Notifications::class)->partners($request->application, 'Rappel : compléments attendus', 'Des éléments sont attendus pour votre dossier '.$request->application->reference.'. Échéance : '.$request->due_on->format('d/m/Y').'. Consultez votre espace pour préparer votre réponse.');
                $request->update(['reminded_at' => now()]);
                $count++;
            });
        });
    $this->info("{$count} rappel(s) créé(s).");
})->purpose('Rappeler une fois les demandes de compléments dont l’échéance approche');

\Illuminate\Support\Facades\Schedule::command('consortium:remind')->dailyAt('08:00')->timezone('Africa/Kinshasa');
\Illuminate\Support\Facades\Schedule::command('consortium:retry-mail')->hourly();

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
