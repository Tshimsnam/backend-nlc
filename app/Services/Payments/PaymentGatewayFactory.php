<?php

namespace App\Services\Payments;

use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * Crée une instance du service de paiement approprié.
     *
     * @param string $gateway 'maxicash', 'mpesa', 'orange_money', ou 'cash'
     * @return MaxiCashService|MpesaService|OrangeMoneyService|null
     */
    public static function create(string $gateway)
    {
        return match (strtolower($gateway)) {
            'maxicash' => app(MaxiCashService::class),
            'mpesa', 'm-pesa' => app(MpesaService::class),
            'orange_money', 'orange' => app(OrangeMoneyService::class),
            'cash', 'caisse' => null, // Paiement en caisse, pas de service externe
            default => throw new InvalidArgumentException("Gateway de paiement non supporté: {$gateway}"),
        };
    }

    /**
     * Retourne la liste des gateways disponibles.
     */
    public static function availableGateways(): array
    {
        return [
            'cash' => 'Paiement en caisse',
            'maxicash' => 'MaxiCash',
            'mpesa' => 'M-Pesa',
            'orange_money' => 'Orange Money',
        ];
    }

    /**
     * Vérifie si un gateway nécessite un paiement en ligne.
     */
    public static function requiresOnlinePayment(string $gateway): bool
    {
        return !in_array(strtolower($gateway), ['cash', 'caisse'], true);
    }
}
