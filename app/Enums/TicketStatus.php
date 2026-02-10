<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Issued = 'issued';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Issued => 'Émis',
            self::Cancelled => 'Annulé',
        };
    }
}
