<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class CloseFinishedEvents extends Command
{
    protected $signature = 'events:close-finished {--dry-run : Afficher les clôtures sans modifier la base}';

    protected $description = 'Passer les événements terminés au statut past (heure de Kinshasa)';

    public function handle(): int
    {
        $count = 0;
        Event::without('event_prices')->where('status', 'upcoming')->chunkById(200, function ($events) use (&$count) {
            foreach ($events as $event) {
                if (!$event->endsAt()) {
                    $this->warn("Événement #{$event->id} ignoré : date ou heure de fin invalide.");
                    continue;
                }
                if (!$event->hasEnded()) {
                    continue;
                }
                if ($this->option('dry-run')) {
                    $this->line("Événement #{$event->id} à clôturer : {$event->title}");
                    $count++;
                } else {
                    // Do not overwrite an event that was rescheduled after it was read.
                    $count += Event::whereKey($event->id)->where('status', 'upcoming')
                        ->where('updated_at', $event->getRawOriginal('updated_at'))
                        ->update(['status' => 'past']);
                }
            }
        });

        $this->info($this->option('dry-run') ? "{$count} événement(s) à clôturer (simulation)." : "{$count} événement(s) clôturé(s).");

        return self::SUCCESS;
    }
}
