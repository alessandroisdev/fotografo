<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Gateways\AsaasGateway;
use App\Services\Payments\Gateways\ManualGateway;
use App\Services\Payments\Gateways\MercadoPagoGateway;
use App\Services\Payments\Gateways\StripeGateway;
use App\Services\Payments\Gateways\PaypalGateway;
use App\Services\Payments\Gateways\PagarMeGateway;
use App\Enums\PaymentGatewayEnum;
use App\Enums\PaymentMethodEnum;

class PaymentGatewayFactory
{
    public static function resolve(PaymentMethodEnum $method): PaymentGatewayInterface
    {
        // Se for dinheiro em maos, force Manual (mesmo que haja lixo no BD)
        if ($method === PaymentMethodEnum::MANUAL_CASH) {
            return new ManualGateway();
        }

        // Recuperar qual Gateway configurado pra esse método de pagamento via Tabela Dinamica
        $activeConfig = config('settings.gateway_' . $method->value, PaymentGatewayEnum::MANUAL->value);
        $gatewayEnum = PaymentGatewayEnum::tryFrom($activeConfig) ?? PaymentGatewayEnum::MANUAL;

        return match($gatewayEnum) {
            PaymentGatewayEnum::ASAAS => new AsaasGateway(),
            PaymentGatewayEnum::MERCADO_PAGO => new MercadoPagoGateway(),
            PaymentGatewayEnum::STRIPE => new StripeGateway(),
            PaymentGatewayEnum::PAYPAL => new PaypalGateway(),
            PaymentGatewayEnum::PAGAR_ME => new PagarMeGateway(),
            PaymentGatewayEnum::MANUAL => new ManualGateway(),
        };
    }
}
