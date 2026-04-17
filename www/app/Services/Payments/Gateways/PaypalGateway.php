<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class PaypalGateway implements PaymentGatewayInterface
{
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
