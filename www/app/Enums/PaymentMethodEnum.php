<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case PIX = 'pix';
    case CREDIT_CARD = 'credit_card';
    case BOLETO = 'boleto';
    case MANUAL_CASH = 'manual_cash';

    public function label(): string
    {
        return match($this) {
            self::PIX => 'PIX (Acesso Imediato)',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::BOLETO => 'Boleto Bancário',
            self::MANUAL_CASH => 'Combinar Pagamento Manualmente'
        };
    }
}
