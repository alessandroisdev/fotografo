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
    private string $baseUrl;

    public function __construct()
    {
        $this->isSandbox = config('settings.stripe_environment', 'sandbox') !== 'production';
        $this->publishableKey = config('settings.stripe_publishable_key', '');
        $this->secretKey = config('settings.stripe_secret_key', '');
        
        $this->baseUrl = 'https://api.stripe.com/v1';
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            // Roteamento inteligente de Payment Method do Stripe
            $paymentMethodTypes = ['card'];
            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                $paymentMethodTypes = ['pix'];
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::BANK_SLIP->value) {
                $paymentMethodTypes = ['boleto'];
            }

            $payload = [
                'success_url' => route('client.dashboard', ['stripe_success' => '1']),
                'cancel_url' => route('client.dashboard'),
                'mode' => 'payment',
                'client_reference_id' => $order->uuid,
                'customer_email' => $order->user->email,
                'payment_intent_data' => [
                    'metadata' => [
                        'uuid' => $order->uuid
                    ]
                ],
                'line_items[0][price_data][currency]' => 'brl',
                'line_items[0][price_data][product_data][name]' => 'Ensaios e Fotografias: ' . $order->gallery->name,
                'line_items[0][price_data][unit_amount]' => round($order->total_amount, 2) * 100, // Stripe usa centavos
                'line_items[0][quantity]' => 1,
            ];

            // Injeta o array de métodos dinamicamente permitidos
            foreach ($paymentMethodTypes as $index => $type) {
                $payload["payment_method_types[{$index}]"] = $type;
            }

            $response = \Illuminate\Support\Facades\Http::withToken($this->secretKey)
                ->asForm()
                ->timeout(15)
                ->post("{$this->baseUrl}/checkout/sessions", $payload);

            if ($response->failed()) {
                Log::error('Stripe Payload Error', ['res' => $response->json()]);
                return PaymentResponse::make(false, 'Serviço global inoperante (Stripe). Tente mais tarde.');
            }

            $order->update(['status' => 'pending']);
            $stripeSession = $response->json();

            return PaymentResponse::make(
                success: true,
                message: 'Redirecionando de modo protegido.',
                redirectUrl: $stripeSession['url'] ?? null,
                externalId: $stripeSession['id'] ?? null // id da sessao (cs_test...) o Webhook altera pra PI
            );
        } catch (\Exception $e) {
            Log::error('Stripe Fallback: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Falha interna de comunicação HTTP.');
        }
    }

    public function refundCharge(Order $order, ?float $amount = null): bool
    {
        // Se a transação for 'cs_test_' ou 'cs_live_', é Checkout Session.
        // O webhook precisa ter substituído isso pelo Payment Intent (pi_...) pra podermos estornar direto no gateway.
        if (empty($order->gateway_transaction_id) || str_starts_with($order->gateway_transaction_id, 'cs_')) {
            Log::error('Stripe Refund Block: ID de sessão não resolvido pelo Webhook.');
            return false;
        }

        $payload = ['payment_intent' => $order->gateway_transaction_id];
        if (!is_null($amount)) {
             $payload['amount'] = round($amount, 2) * 100; // centavos
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($this->secretKey)
                ->asForm()
                ->timeout(15)
                ->post("{$this->baseUrl}/refunds", $payload);

            if ($response->failed()) {
                Log::error('Stripe Refund Execution Error', ['b' => $response->json()]);
                return false;
            }
            return true;
        } catch (\Exception $e) {
             Log::error('Stripe Refund Exc: ' . $e->getMessage());
             return false;
        }
    }
}
