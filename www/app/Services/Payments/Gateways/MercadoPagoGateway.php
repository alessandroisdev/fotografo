<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            // MOCK Simulando Mercado Pago
            $order->update(['status' => 'pending']);

            return PaymentResponse::make(
                success: true,
                message: 'Fatura Mercado Pago gerada com sucesso!',
                redirectUrl: 'https://sandbox.mercadopago.com.br/checkout/mock_' . $order->uuid,
                externalId: 'mp_mock_999'
            );
        } catch (\Exception $e) {
            Log::error('MercadoPago Gateway Error: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Falha de comunicação com o Mercado Pago.');
        }
    }
}
