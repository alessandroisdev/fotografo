<?php

namespace App\Enums;

enum PaymentGatewayEnum: string
{
    case ASAAS = 'asaas';
    case MERCADO_PAGO = 'mercadopago';
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case PAGAR_ME = 'pagarme';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match($this) {
            self::ASAAS => 'Asaas',
            self::MERCADO_PAGO => 'Mercado Pago',
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
            self::PAGAR_ME => 'Pagar.Me',
            self::MANUAL => 'Fechamento Manual (Admin)'
        };
    }
}
