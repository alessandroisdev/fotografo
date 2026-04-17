<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;

class ManualGateway implements PaymentGatewayInterface
{
    private bool $isFree;

    public function __construct(bool $isFree = false)
    {
        $this->isFree = $isFree;
    }

    public function generateCharge(Order $order, ?array $paymentData = null): PaymentResponse
    {
        if ($this->isFree) {
            // Cortesia Integral
            $order->update(['status' => 'paid', 'total_amount' => 0]);
            
            return PaymentResponse::make(
                success: true,
                message: 'Pedido Cortesia finalizado com sucesso! Liberado automaticamente.',
                redirectUrl: null // Nao redireciona pra Gateway
            );
        }

        // Pagamento Físico ou PIX por fora da Plataforma API
        $order->update(['status' => 'pending']); // Fica vermelho no painel ate o fotografo dar baixa manualmente

        return PaymentResponse::make(
            success: true,
            message: 'Pedido gerado! O Pagamento deve ser alinhado diretamente com o fotógrafo.',
            redirectUrl: null
        );
    }
}
