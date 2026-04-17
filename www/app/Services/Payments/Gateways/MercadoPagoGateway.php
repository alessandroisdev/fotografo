<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    private bool $isSandbox;
    private string $publicKey;
    private string $accessToken;

    public function __construct()
    {
        $this->isSandbox = config('settings.mercadopago_environment', 'sandbox') !== 'production';
        $this->publicKey = config('settings.mercadopago_public_key', '');
        $this->accessToken = config('settings.mercadopago_access_token', '');
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json'
            ])->timeout(15)->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [
                    [
                        'title' => 'Fotografias Finais - ' . $order->gallery->name,
                        'description' => 'Acesso definitivo ao Acervo Fotográfico.',
                        'quantity' => 1,
                        'currency_id' => 'BRL',
                        'unit_price' => round($order->total_amount, 2),
                    ]
                ],
                'payer' => [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'external_reference' => $order->uuid,
                'back_urls' => [
                    'success' => route('client.dashboard'),
                    'pending' => route('client.dashboard'),
                    'failure' => route('client.dashboard')
                ],
                'auto_return' => 'approved' // Roteamento limpo do hub MP pra cá
            ]);

            if ($response->failed()) {
                Log::error('MercadoPago Preference Error', ['res' => $response->json()]);
                return PaymentResponse::make(false, 'Integração indisponível com a malha do Mercado Pago no momento.');
            }

            $order->update(['status' => 'pending']);

            $payload = $response->json();
            $redirectKey = $this->isSandbox ? 'sandbox_init_point' : 'init_point';

            return PaymentResponse::make(
                success: true,
                message: 'Encaminhando ao Cofre Seguro do Mercado Pago',
                redirectUrl: $payload[$redirectKey] ?? null,
                externalId: $payload['id'] ?? null // Id da preferencia (Pode ser atualizada para Pay_Id no webhook)
            );

        } catch (\Exception $e) {
            Log::error('MercadoPago Gateway Falha Rede: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Houve colisão sistêmica temporária. Evite duas vias e tente amanhã.');
        }
    }

    public function refundCharge(Order $order, ?float $amount = null): bool
    {
        // Se a transação for preference_id não dá pra estornar. Só dá se o webhook tiver atualizado com 'payment_id' numérico do MP.
        // O webhook MP deve atualizar o "gateway_transaction_id" pra o Numeric ID na aprovação.
        if (empty($order->gateway_transaction_id) || str_starts_with($order->gateway_transaction_id, 'pref_')) {
            Log::error('MercadoPago Error: Impossível realizar estorno. Payload ausente do Webhook do Pagamento ou Misto.');
            return false;
        }

        try {
            $payload = [];
            if (!is_null($amount)) {
                $payload['amount'] = round($amount, 2);
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-Idempotency-Key' => $order->uuid . rand(),
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->timeout(15)->post('https://api.mercadopago.com/v1/payments/' . $order->gateway_transaction_id . '/refunds', $payload);

            if ($response->failed()) {
                Log::error('MercadoPago Refund Fallback', ['b' => $response->json()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('MP Refund Exc: ' . $e->getMessage());
            return false;
        }
    }
}
