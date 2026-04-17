<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Gateways\AsaasGateway;
use App\Services\Payments\Gateways\ManualGateway;
use App\Enums\PaymentGatewayEnum;

class PaymentGatewayFactory
{
    public static function resolve(): PaymentGatewayInterface
    {
        $activeConfig = config('settings.active_gateway', PaymentGatewayEnum::MANUAL_CASH->value);
        $gatewayName = PaymentGatewayEnum::tryFrom($activeConfig) ?? PaymentGatewayEnum::MANUAL_CASH;

        return match($gatewayName) {
            PaymentGatewayEnum::ASAAS_PROD => new AsaasGateway(isSandbox: false),
            PaymentGatewayEnum::ASAAS_SANDBOX => new AsaasGateway(isSandbox: true),
            PaymentGatewayEnum::MANUAL_CASH => new ManualGateway(isFree: false),
            PaymentGatewayEnum::MANUAL_FREE => new ManualGateway(isFree: true),
        };
    }
}
