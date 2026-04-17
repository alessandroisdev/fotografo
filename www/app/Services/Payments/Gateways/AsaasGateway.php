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
        $isSandbox = config('settings.asaas_environment', 'sandbox') !== 'production';
        $this->baseUrl = $isSandbox 
            ? 'https://sandbox.asaas.com/api/v3' 
            : 'https://api.asaas.com/v3';
            
        $this->apiKey = config('settings.asaas_api_key', '');
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            // 1. Resgatar ou Criar o Cliente Externo (Wallet)
            $customerId = $this->resolveCustomer($order->user);

            // 2. Montar Requisição de Cobrança (Fatura)
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(15)->post($this->baseUrl . '/payments', [
                'customer' => $customerId,
                'billingType' => 'UNDEFINED', // Permite o cliente decidir Cartão, Boleto ou PIX pela página do Asaas
                'value' => round($order->total_amount, 2),
                'dueDate' => date('Y-m-d', strtotime('+3 days')),
                'description' => 'Serviços Fotográficos - Galeria #' . $order->gallery->name,
                'externalReference' => $order->uuid // Ponte Crucial para Webhook
            ]);

            if ($response->failed()) {
                Log::error('Asaas Payment Error', ['body' => $response->json()]);
                return PaymentResponse::make(
                    success: false,
                    message: 'A operadora de transações financeiras recusou o pedido no momento.'
                );
            }

            $responseData = $response->json();
            
            // 3. Registrar o ID financeiro como Pending e devolver URL
            $order->update(['status' => 'pending']);

            return PaymentResponse::make(
                success: true,
                message: 'Fatura gerada em ambiente Asaas com sucesso!',
                redirectUrl: $responseData['invoiceUrl'] ?? null,
                externalId: $responseData['id'] ?? null
            );

        } catch (\Exception $e) {
            Log::error('Asaas Gateway HTTP Exception: ' . $e->getMessage());
            
            return PaymentResponse::make(
                success: false,
                message: 'Incomunicabilidade temporária na malha bancária externa.'
            );
        }
    }

    /**
     * Resolve Customer Externo no Banco Asaas
     */
    private function resolveCustomer($user): string
    {
        // Limpa formatações para comparar na base
        $document = preg_replace('/[^0-9]/', '', $user->document);

        // Busca o indivíduo por Cpf (Geralmente 1 para cada CPF na conta do fotógrafo)
        $search = Http::withHeaders(['access_token' => $this->apiKey])
                      ->timeout(10)
                      ->get($this->baseUrl . '/customers', ['cpfCnpj' => $document]);

        if ($search->successful() && isset($search->json()['data'][0]['id'])) {
             return $search->json()['data'][0]['id'];
        }

        // Se não achou, cadastra novo nó de cliente na API Asaas do lojista
        $create = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(12)->post($this->baseUrl . '/customers', [
            'name' => $user->name,
            'email' => $user->email,
            'cpfCnpj' => $document,
            'phone' => preg_replace('/[^0-9]/', '', $user->phone),
            'externalReference' => $user->uuid
        ]);

        if ($create->failed()) {
             Log::error('Asaas Customer Creation', ['body' => $create->json()]);
             throw new \Exception('Não foi possível matricular os documentos do cliente no Gateway recebedor.');
        }

        return $create->json()['id'];
    }
}
