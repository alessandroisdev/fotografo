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

    private function getAccessToken(): ?string
    {
        $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->timeout(10)
            ->asForm()
            ->post($this->baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);
        return $response->json()['access_token'] ?? null;
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                 return PaymentResponse::make(false, 'Credenciais de OAuth do PayPal inválidas no servidor.');
            }

            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(15)
                ->post($this->baseUrl . '/v2/checkout/orders', [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $order->uuid,
                        'custom_id' => $order->uuid, // CRUCIAL: Retornado com 100% de fiabilidade nos webhooks asíncronos da V2
                        'description' => 'Fotografias - ' . $order->gallery->name,
                        'amount' => [
                            'currency_code' => 'BRL',
                            'value' => number_format($order->total_amount, 2, '.', '')
                        ]
                    ]],
                    'payment_source' => [
                        'paypal' => [
                            'experience_context' => [
                                'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                                'brand_name' => config('settings.site_title', 'Estúdio Oculto'),
                                'user_action' => 'PAY_NOW',
                                'return_url' => route('client.dashboard'),
                                'cancel_url' => route('client.dashboard')
                            ]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                Log::error('PayPal Order Error', ['body' => $response->json()]);
                return PaymentResponse::make(false, 'Conexão rejeitada pela PayPal Checkout API.');
            }

            $order->update(['status' => 'pending']);
            $paypalData = $response->json();
            
            $approveLink = null;
            if (isset($paypalData['links'])) {
                foreach ($paypalData['links'] as $link) {
                    if ($link['rel'] === 'payer-action') {
                        $approveLink = $link['href'];
                    }
                }
            }

            return PaymentResponse::make(
                success: true,
                message: 'Encaminhando ao PayPal Seguro.',
                redirectUrl: $approveLink,
                externalId: $paypalData['id'] ?? null // PayPal Order_id
            );
        } catch (\Exception $e) {
            Log::error('PayPal Fallback: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Falha interna na API do PayPal.');
        }
    }

    public function refundCharge(Order $order, ?float $amount = null): bool
    {
        if (empty($order->gateway_transaction_id)) {
            Log::error('PayPal Refund Block: Fallback de captura_id inexistente.');
            return false;
        }

        try {
            $token = $this->getAccessToken();
            // PayPal exige o Capture ID para estornar, que o webhook precisa trocar no ato da cobrança!
            $payload = [];
            if (!is_null($amount)) {
                $payload['amount'] = [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => 'BRL'
                ];
            }

            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(15)
                ->post($this->baseUrl . '/v2/payments/captures/' . $order->gateway_transaction_id . '/refund', $payload);

            if ($response->failed()) {
                Log::error('PayPal Refund Error', ['b' => $response->json()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('PayPal Refund Exc: ' . $e->getMessage());
            return false;
        }
    }
}
