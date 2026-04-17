<?php

namespace App\Enums;

enum PaymentGatewayEnum: string
{
    case ASAAS_PROD = 'asaas_prod';
    case ASAAS_SANDBOX = 'asaas_sandbox';
    case MANUAL_CASH = 'manual_cash';
    case MANUAL_FREE = 'manual_free';

    public function label(): string
    {
        return match($this) {
            self::ASAAS_PROD => 'Asaas (Produção)',
            self::ASAAS_SANDBOX => 'Asaas (Homologação)',
            self::MANUAL_CASH => 'Manual (Dinheiro/PIX Físico)',
            self::MANUAL_FREE => 'Isento (Cortesia Profissional)'
        };
    }
}
