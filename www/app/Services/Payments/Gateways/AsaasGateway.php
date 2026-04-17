<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $isSandbox = config('settings.asaas_sandbox', true) == '1';
        $this->baseUrl = $isSandbox 
            ? 'https://sandbox.asaas.com/api/v3' 
            : 'https://api.asaas.com/v3';
            
        $this->apiKey = config('settings.asaas_api_key', '');
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        // Neste passo integraria-se chamadas reais.
        // Aqui construiremos a infraestrutura sólida simulando retorno em mock para evitar billing acidental
        // até o cliente fornecer a sua API Key válida.
        
        try {
            /* 
            Exemplo Dinâmico de Integração:
            $response = Http::withHeaders(['access_token' => $this->apiKey])
                ->post($this->baseUrl . '/payments', [
                    'customer' => $order->user->asaas_customer_id, // Carece da criação de customer API 
                    'billingType' => 'UNDEFINED', // Deixa cliente escolher Cartao/Pix
                    'value' => $order->total_amount,
                    'dueDate' => date('Y-m-d', strtotime('+3 days')),
                    'description' => 'Pacote Fotográfico - Galeria #' . $order->gallery_id
                ]);
            */

            // Retorno Mockado para Validar a Arquitetura da Factory
            $mockInvoiceUrl = 'https://sandbox.asaas.com/i/mocked_payment_' . $order->uuid;
            
            // Opcional: Atualizar status do banco caso a API fosse imediata (mas Asaas usa Webhooks para pagar)
            $order->update(['status' => 'pending']);

            return PaymentResponse::make(
                success: true,
                message: 'Fatura Asaas gerada com sucesso!',
                redirectUrl: $mockInvoiceUrl,
                externalId: 'pay_asaas_mock_123'
            );

        } catch (\Exception $e) {
            Log::error('Asaas Gateway Error: ' . $e->getMessage());
            
            return PaymentResponse::make(
                success: false,
                message: 'Falha ao se comunicar com o banco de compensação.'
            );
        }
    }
}
