<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case PendingCash = 'pending_cash';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::PendingCash => 'En attente (Caisse)',
            self::Completed => 'Payé',
            self::Failed => 'Échoué',
            self::Refunded => 'Remboursé',
        };
    }
}
