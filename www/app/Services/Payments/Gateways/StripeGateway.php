<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    private bool $isSandbox;
    private string $publishableKey;
    private string $secretKey;

    public function __construct()
    {
        $this->isSandbox = config('settings.stripe_environment', 'sandbox') !== 'production';
        $this->publishableKey = config('settings.stripe_publishable_key', '');
        $this->secretKey = config('settings.stripe_secret_key', '');
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            // MOCK Simulando Stripe
            $order->update(['status' => 'pending']);

            return PaymentResponse::make(
                success: true,
                message: 'Stripe Checkout Session created!',
                redirectUrl: 'https://checkout.stripe.com/c/pay/cs_test_mock_' . $order->uuid,
                externalId: 'stripe_mock_000'
            );
        } catch (\Exception $e) {
            Log::error('Stripe Error: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Falha de integração global com Stripe.');
        }
    }
}
