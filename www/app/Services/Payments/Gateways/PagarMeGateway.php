<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class PagarMeGateway implements PaymentGatewayInterface
{
    private bool $isSandbox;
    private string $apiKey;

    public function __construct()
    {
        $this->isSandbox = config('settings.pagarme_environment', 'sandbox') !== 'production';
        $this->apiKey = config('settings.pagarme_api_key', '');
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            $order->update(['status' => 'pending']);

            return PaymentResponse::make(
                success: true,
                message: 'Faturamento registrado via Pagar.Me!',
                redirectUrl: 'https://sandbox.pagar.me/checkout/mock_' . $order->uuid,
                externalId: 'pagarme_mock_XYZ'
            );
        } catch (\Exception $e) {
            Log::error('PagarMe Error: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Servidor temporariamente inacessível na operadora Pagar.Me.');
        }
    }
}
