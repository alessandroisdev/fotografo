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
            // Core v5 uses Base64 encoded apiKey:password(empty) - Laravel Http BasicAuth handles it.
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->apiKey, '')
                ->timeout(15)
                ->post('https://api.pagar.me/core/v5/orders', [
                    'code' => $order->uuid,
                    'items' => [
                        [
                            'amount' => round($order->total_amount, 2) * 100, // Centavos
                            'description' => 'Fotografias - Galeria ' . $order->gallery->name,
                            'quantity' => 1,
                            'code' => 'PHOTOPKG_' . $order->package_id
                        ]
                    ],
                    'customer' => [
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                        'document' => preg_replace('/[^0-9]/', '', $order->user->document),
                        'type' => 'individual'
                    ],
                    'closed' => false // Permite que a URL expire caso não feche
                ]);

            if ($response->failed()) {
                Log::error('PagarMe API Error', ['body' => $response->json()]);
                return PaymentResponse::make(false, 'Servidor global Pagar.me não responde.' . $response->body());
            }

            $order->update(['status' => 'pending']);
            $pgmOrder = $response->json();
            
            return PaymentResponse::make(
                success: true,
                message: 'Encaminhando ao processo interno do Pagar.me',
                redirectUrl: null, // PagarMe geralmente é transparente frontend, ou depende de UI do hub client.
                externalId: $pgmOrder['id'] ?? null // id do Order Pagar.me
            );
        } catch (\Exception $e) {
            Log::error('PagarMe Fallback: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Falha interna na API Pagar.Me.');
        }
    }

    public function refundCharge(Order $order, ?float $amount = null): bool
    {
        if (empty($order->gateway_transaction_id)) {
            Log::error('PagarMe Refund: No charge ID on file.');
            return false;
        }

        try {
            $payload = [];
            if (!is_null($amount)) {
                $payload['amount'] = round($amount, 2) * 100;
            }

            // O webhook substituiu gateway_transaction_id pra o "charge_id" (ch_...)
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->apiKey, '')
                ->timeout(15)
                ->delete('https://api.pagar.me/core/v5/charges/' . $order->gateway_transaction_id, $payload);

            if ($response->failed()) {
                Log::error('PagarMe Refund Error', ['b' => $response->json()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('PagarMe Refund Exc: ' . $e->getMessage());
            return false;
        }
    }
}
