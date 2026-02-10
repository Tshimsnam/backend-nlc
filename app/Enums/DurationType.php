<?php

namespace App\Enums;

enum DurationType: string
{
    case PerDay = 'per_day';
    case FullEvent = 'full_event';

    public function label(): string
    {
        return match ($this) {
            self::PerDay => 'Par jour',
            self::FullEvent => 'Événement complet',
        };
    }
}
