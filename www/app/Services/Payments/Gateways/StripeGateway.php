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

    public function generateCharge(Order $order, ?array $paymentData = null): PaymentResponse
    {
        try {
            // Montagem Bruta e Limpa do PaymentIntent Global (Transparente para Todos)
            $payload = [
                'amount' => round($order->total_amount, 2) * 100, // centavos
                'currency' => 'brl',
                'confirm' => 'true', // Exige confirmação server-side imediata
                'receipt_email' => $order->user->email,
                'metadata' => ['uuid' => $order->uuid],
            ];

            if ($order->gateway === \App\Enums\PaymentMethodEnum::CREDIT_CARD->value && !empty($paymentData)) {
                $payload['payment_method_types[0]'] = 'card';
                $payload['payment_method_data[type]'] = 'card';
                $payload['payment_method_data[card][number]'] = preg_replace('/[^0-9]/', '', $paymentData['card_number']);
                $payload['payment_method_data[card][exp_month]'] = explode('/', $paymentData['card_expiry'])[0];
                $payload['payment_method_data[card][exp_year]'] = '20' . (explode('/', $paymentData['card_expiry'])[1] ?? '');
                $payload['payment_method_data[card][cvc]'] = $paymentData['card_cvv'];
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                $payload['payment_method_types[0]'] = 'pix';
                $payload['payment_method_options[pix][expires_after_seconds]'] = 3600;
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::BOLETO->value) {
                // Stripe BOLETO exige Tax ID do cliente (CPF) no objeto para autorizar a confecção
                $document = preg_replace('/[^0-9]/', '', $order->user->document ?? '00000000000');
                if (empty($document)) $document = '00000000000';
                $payload['payment_method_types[0]'] = 'boleto';
                $payload['payment_method_data[type]'] = 'boleto';
                $payload['payment_method_data[boleto][tax_id]'] = $document;
            } else {
                 return PaymentResponse::make(false, 'Modo desabilitado no Stripe.');
            }

            // Disparo Server-to-Server
            $response = \Illuminate\Support\Facades\Http::withToken($this->secretKey)
                ->asForm()
                ->timeout(15)
                ->post("{$this->baseUrl}/payment_intents", $payload);

            if ($response->failed()) {
                Log::error('Stripe Payload Error (PaymentIntents)', ['res' => $response->json()]);
                return PaymentResponse::make(false, 'Serviço global inoperante (Stripe) ou falha na composição dos documentos.');
            }

            $intent = $response->json();
            $order->update(['status' => 'pending', 'gateway_transaction_id' => $intent['id']]);
            $gatewayPayload = null;

            // Transição e Extração de Respostas Assíncronas Transparentes
            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                 if (isset($intent['next_action']['pix_display_qr_code'])) {
                     $gatewayPayload = [
                         'type' => 'pix',
                         'qr_code' => $intent['next_action']['pix_display_qr_code']['data'] ?? null,
                         'qr_code_base64' => $intent['next_action']['pix_display_qr_code']['image_url_png'] ?? null,
                     ];
                 }
                 $msg = 'Código Copia e Cola via Stripe Pix liberado.';
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::BOLETO->value) {
                 if (isset($intent['next_action']['boleto_display_details'])) {
                     $details = $intent['next_action']['boleto_display_details'];
                     $gatewayPayload = [
                         'type' => 'boleto',
                         'barcode' => $details['number'] ?? null,
                         'pdf_url' => $details['hosted_voucher_url'] ?? null,
                     ];
                 }
                 $msg = 'Boleto Eletrônico Stripe liberado em anexo.';
            } else {
                 if (($intent['status'] ?? '') === 'succeeded') {
                     $order->update(['status' => \App\Enums\OrderStatusEnum::PAID, 'paid_at' => now()]);
                     $msg = 'Transação por Cartão processada com sucesso no Stripe!';
                 } else {
                     $msg = 'Transação pendente/em análise no Stripe.';
                 }
            }

            return PaymentResponse::make(
                success: true,
                message: $msg,
                redirectUrl: null, // Transparent Lock - Escapes Denied
                externalId: $intent['id'],
                payload: $gatewayPayload
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
