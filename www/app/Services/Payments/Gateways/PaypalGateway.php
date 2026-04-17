<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class PaypalGateway implements PaymentGatewayInterface
{
    private bool $isSandbox;
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->isSandbox = config('settings.paypal_environment', 'sandbox') !== 'production';
        $this->clientId = config('settings.paypal_client_id', '');
        $this->clientSecret = config('settings.paypal_secret', '');
        
        $this->baseUrl = $this->isSandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            $order->update(['status' => 'pending']);

            return PaymentResponse::make(
                success: true,
                message: 'PayPal Order Invoice provisioned!',
                redirectUrl: 'https://sandbox.paypal.com/checkoutnow?token=mock_' . $order->uuid,
                externalId: 'paypal_mock_ABC'
            );
        } catch (\Exception $e) {
            Log::error('PayPal Error: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Houve um erro conectando ao Paypal.');
        }
    }
}
