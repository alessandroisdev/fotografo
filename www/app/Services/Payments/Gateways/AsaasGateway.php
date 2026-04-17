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

    public function generateCharge(Order $order, ?array $paymentData = null): PaymentResponse
    {
        try {
            // 1. Resgatar ou Criar o Cliente Externo (Wallet)
            $customerId = $this->resolveCustomer($order->user);

            // Condicional de Roteamento de Faturamento API V3
            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                $billingType = 'PIX';
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::CREDIT_CARD->value) {
                $billingType = 'CREDIT_CARD';
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::BOLETO->value) {
                $billingType = 'BOLETO';
            } else {
                $billingType = 'UNDEFINED'; 
            }

            // 2. Montar Requisição de Cobrança (Fatura)
            $payload = [
                'customer' => $customerId,
                'billingType' => $billingType,
                'value' => round($order->total_amount, 2),
                'dueDate' => date('Y-m-d', strtotime('+3 days')),
                'description' => 'Serviços Fotográficos - Galeria #' . $order->gallery->name,
                'externalReference' => $order->uuid // Ponte Crucial para Webhook
            ];

            // Roteamento Transparente Cartão de Crédito
            if ($billingType === 'CREDIT_CARD' && !empty($paymentData)) {
                $payload['creditCard'] = [
                    'holderName' => $paymentData['card_holder'],
                    'number' => preg_replace('/[^0-9]/', '', $paymentData['card_number']),
                    'expiryMonth' => explode('/', $paymentData['card_expiry'])[0] ?? '',
                    'expiryYear' => '20' . (explode('/', $paymentData['card_expiry'])[1] ?? ''),
                    'ccv' => $paymentData['card_cvv']
                ];
                $payload['creditCardHolderInfo'] = [
                    'name' => $paymentData['card_holder'],
                    'email' => $order->user->email,
                    'cpfCnpj' => preg_replace('/[^0-9]/', '', $order->user->document),
                    'postalCode' => '01001-000', // Mock exigido por Compliance em Transparentes
                    'addressNumber' => '100',
                    'phone' => '11999999999'
                ];
                $payload['remoteIp'] = request()->ip() ?? '127.0.0.1'; // Necessário no V3
            }

            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(15)->post($this->baseUrl . '/payments', $payload);

            if ($response->failed()) {
                Log::error('Asaas Payment Error', ['body' => $response->json()]);
                return PaymentResponse::make(
                    success: false,
                    message: 'A operadora de transações financeiras recusou o pedido no momento.'
                );
            }

            $responseData = $response->json();
            $paymentId = $responseData['id'];
            
            // 3. Registrar o ID financeiro como Pending
            $order->update(['status' => 'pending', 'gateway_transaction_id' => $paymentId]);

            // Se for PIX, nós fazemos a SEGUNDA chamada pra pegar o Payload Copy+Paste ou Base64 (Transparente)
            if ($billingType === 'PIX') {
                $pixResponse = Http::withHeaders([
                    'access_token' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])->timeout(10)->get($this->baseUrl . "/payments/{$paymentId}/pixQrCode");
                
                if ($pixResponse->successful()) {
                    // Nós podemos guardar a URL ou o Payload String. No caso, vamos retornar null pra URL de link,
                    // assumindo que no futuro o Gateway retorne Strings base64 se quisermos. O Frontend costuma usar redirect.
                    // Para fins de universalidade temporária, podemos mandar a InvoiceUrl que exibe o Pix do Asaas.
                    // Mas tentaremos retornar a URL crua do QR Code se suportado futuramente na view.
                    Log::info('PIX gerado', ['pix' => $pixResponse->json()]);
                }
            }

            return PaymentResponse::make(
                success: true,
                message: $billingType === 'PIX' ? 'Gerando chave de transferência central...' : 'Fatura em Faturamento Eletrônico Asaas Acionada',
                redirectUrl: $responseData['invoiceUrl'] ?? null,
                externalId: $paymentId
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

        // Mercado Pago / Asaas Sandbox/Production exige CPF Válido. Injeta Fake em testes se inválido.
        if (empty($document) || (strlen($document) !== 11 && strlen($document) !== 14)) {
            $document = \Illuminate\Support\Facades\App::environment('local') ? '19119119100' : '00000000000';
        }

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
            'phone' => preg_replace('/[^0-9]/', '', $user->phone ?? '11999999999'),
            'externalReference' => $user->uuid
        ]);

        if ($create->failed()) {
             Log::error('Asaas Customer Creation', ['body' => $create->json()]);
             throw new \Exception('Não foi possível matricular os documentos do cliente no Gateway recebedor.');
        }

        return $create->json()['id'];
    }

    public function refundCharge(Order $order, ?float $amount = null): bool
    {
        if (empty($order->gateway_transaction_id)) {
            Log::error('Asaas Gateway Error: Impossível estornar fatura sem ID de transação salvo.');
            return false;
        }

        $payload = [];
        if (!is_null($amount)) {
             $payload['value'] = $amount;
             $payload['description'] = 'Estorno parcial do pacote fotográfico.';
        }

        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(15)->post($this->baseUrl . '/payments/' . $order->gateway_transaction_id . '/refund', $payload);

            if ($response->failed()) {
                Log::error('Asaas Refund Failed', ['body' => $response->json()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Asaas Refund HTTP Exception: ' . $e->getMessage());
            return false;
        }
    }
}
