<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class PaymentWebhookController extends Controller
{
    /**
     * Dispatcher Dinâmico de Webhooks
     */
    public function handle(Request $request, string $gateway)
    {
        $gatewayMethod = strtolower($gateway);

        // Dispara o método correspondente ex: 'asaas', 'stripe', se existir.
        if (method_exists($this, $gatewayMethod)) {
            return $this->$gatewayMethod($request);
        }

        Log::error("Webhook Gateway Inválido Invocado: {$gateway}");
        return response()->json(['status' => 'error', 'message' => 'Gateway not supported'], 400);
    }

    /**
     * Endpoint receiver for Asaas Events
     */
    public function asaas(Request $request)
    {
        // 1. Logar payload bruto para auditoria Cloud-to-Cloud
        Log::info('Webhook Asaas Recebido:', $request->all());

        // 2. Extrair Evento (e.g., PAYMENT_RECEIVED, PAYMENT_CONFIRMED)
        $event = $request->input('event');
        $paymentData = $request->input('payment');

        if (!$paymentData || !isset($paymentData['externalReference'])) {
            return response()->json(['status' => 'ignored', 'reason' => 'No externalReference'], 200);
        }

        // 3. Match Order UUID via externalReference sent during Checkout
        $orderUuid = $paymentData['externalReference'];
        $order = Order::where('uuid', $orderUuid)->first();

        if (!$order) {
            Log::warning('Webhook Asaas: Pedido não encontrado para UUID: ' . $orderUuid);
            return response()->json(['status' => 'ignored', 'reason' => 'Order not found'], 404);
        }

        // 4. Processar Eventos de Recebimento
        if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'])) {
            // Impedir processamento duplicado
            if ($order->status !== \App\Enums\OrderStatusEnum::PAID) {
                $order->update([
                    'status' => \App\Enums\OrderStatusEnum::PAID,
                    'paid_at' => now(),
                ]);
                Log::info('Pedido UUID ' . $orderUuid . ' marcado como PAID via Webhook!');
                
                // TODO: Event::dispatch(new OrderPaid($order));
            }
        } elseif (in_array($event, ['PAYMENT_OVERDUE', 'PAYMENT_DELETED', 'PAYMENT_REFUNDED'])) {
             // Opções secundárias (Estornos)
             $order->update(['status' => \App\Enums\OrderStatusEnum::CANCELLED]);
             Log::info('Pedido UUID ' . $orderUuid . ' CANCELADO via Webhook!');
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Endpoint receiver for MercadoPago Events
     */
    public function mercadopago(Request $request)
    {
        Log::info('Webhook MP Recebido:', $request->all());

        // Identifica Notificação IPN (topic) ou Webhook (type)
        $typeOrTopic = $request->input('type') ?? $request->input('topic');

        if ($typeOrTopic === 'payment' || isset($request['data']['id'])) {
            // ID real enviado pelo MP
            $paymentId = $request->input('data.id') ?? $request->input('data')['id'] ?? $request->input('id');

            if (!$paymentId) {
                return response()->json(['status' => 'ignored', 'reason' => 'No Payment ID provided'], 200);
            }

            // Chamada reversa de segurança: Pegamos os detalhes diretamente na malha do Mercado Pago pra evitar spoofs
            $accessToken = config('settings.mercadopago_access_token', '');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken
            ])->timeout(10)->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

            if ($response->successful()) {
                $payment = $response->json();
                $orderUuid = $payment['external_reference'] ?? null;
                $status = $payment['status'] ?? null;

                if ($orderUuid) {
                    $order = Order::where('uuid', $orderUuid)->first();
                    if ($order) {
                        // FUNDAMENTAL: O Webhook troca Preference_id pelo Payment_id real numérico
                        $order->update(['gateway_transaction_id' => $paymentId]);

                        if ($status === 'approved' && $order->status !== \App\Enums\OrderStatusEnum::PAID) {
                            $order->update(['status' => \App\Enums\OrderStatusEnum::PAID, 'paid_at' => now()]);
                            Log::info("Pedido {$orderUuid} Pago via MP!");
                        } elseif (in_array($status, ['refunded', 'cancelled', 'rejected'])) {
                            $order->update(['status' => \App\Enums\OrderStatusEnum::CANCELLED]);
                            Log::info("Pedido {$orderUuid} Cancelado/Rejeitado via MP.");
                        }
                    } else {
                         Log::warning("Mercado Pago Webhook: Pedido Origem Não Encontrado para UUID: {$orderUuid}");
                    }
                }
            } else {
                 Log::error('Erro ao buscar dados do pagamento no MP.', ['res' => $response->json()]);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Endpoint receiver for Stripe Events
     */
    public function stripe(Request $request)
    {
        Log::info('Webhook Stripe Recebido:', $request->all());

        $type = $request->input('type');
        $dataObj = $request->input('data.object');

        // Quando o cliente termina de inserir o CVC e foi aprovado
        if ($type === 'checkout.session.completed') {
            $orderUuid = $dataObj['client_reference_id'] ?? null;
            $paymentIntentId = $dataObj['payment_intent'] ?? null;

            if ($orderUuid) {
                $order = Order::where('uuid', $orderUuid)->first();
                if ($order && $order->status !== \App\Enums\OrderStatusEnum::PAID) {
                    // Atualizamos o gateway_transaction_id para o Intent! (cs_ pra pi_)
                    $order->update([
                        'status' => \App\Enums\OrderStatusEnum::PAID,
                        'paid_at' => now(),
                        'gateway_transaction_id' => $paymentIntentId 
                    ]);
                    Log::info("Pedido {$orderUuid} Pago via Stripe!");
                }
            }
        } elseif ($type === 'charge.refunded') {
            $paymentIntentId = $dataObj['payment_intent'] ?? null;
            if ($paymentIntentId) {
                 $order = Order::where('gateway_transaction_id', $paymentIntentId)->first();
                 if ($order && $order->status !== \App\Enums\OrderStatusEnum::CANCELLED) {
                      $order->update(['status' => \App\Enums\OrderStatusEnum::CANCELLED]);
                      Log::info("Pedido {$order->uuid} Estornado e Cancelado pelo reflexo do Stripe.");
                 }
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Endpoint receiver for PayPal Events
     */
    public function paypal(Request $request)
    {
        Log::info('Webhook PayPal Recebido:', $request->all());

        $eventType = $request->input('event_type');
        $resource = $request->input('resource');

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            // No PayPal v2, o ID do Pedido raiz fica escondido na estrutura e precisamos dele.
            // Para encontrar o externalReference a gente escava o purchase_units original ou supplementary_data
            
            // Mas, normalmente o IPN (Webhook) de captação traz o custom_id equivalente
            $orderUuid = $resource['custom_id'] ?? null;
            
            // Tentar extrair do link "up" se custom_id não vier (referência ao order)
            if (!$orderUuid && isset($resource['supplementary_data']['related_ids']['order_id'])) {
                 $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id'];
                 // Para este fluxo funcionar magicamente, a PayPal Order Original gerada na gateway foi gravada no gateway_transaction_id
                 $order = Order::where('gateway_transaction_id', $paypalOrderId)->first();
            } else {
                 $order = Order::where('uuid', $orderUuid)->first();
            }

            if (isset($order) && $order && $order->status !== \App\Enums\OrderStatusEnum::PAID) {
                 // Agora salvamos o CAPTURE ID real para podermos emitir o estorno depois pelo painel
                 $captureId = $resource['id'];
                 
                 $order->update([
                     'status' => \App\Enums\OrderStatusEnum::PAID,
                     'paid_at' => now(),
                     'gateway_transaction_id' => $captureId
                 ]);
                 Log::info("Pedido {$order->uuid} Pago via PayPal Capture!");
            }
        } elseif ($eventType === 'PAYMENT.CAPTURE.REFUNDED') {
            // Em caso de estornos pela plataforma do Paypal
            $captureId = $request->input('resource.links.0.href'); // Busca na url ou se for estorno direto usamos id capture
            // Mais seguro buscar order pelo que já temos registrado (capture id)
            $refundCaptureId = \Illuminate\Support\Str::afterLast($request->input('resource.links.1.href') ?? '', '/');
            
            $order = Order::where('gateway_transaction_id', $refundCaptureId)->first();
            if (!$order) {
                 // Fallback
                 $order = Order::where('gateway_transaction_id', $resource['id'])->first();
            }

            if ($order && $order->status !== \App\Enums\OrderStatusEnum::CANCELLED) {
                 $order->update(['status' => \App\Enums\OrderStatusEnum::CANCELLED]);
                 Log::info("Pedido {$order->uuid} cancelado por Reflexo PayPal.");
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Endpoint receiver for Pagar.Me Events
     */
    public function pagarme(Request $request)
    {
        Log::info('Webhook PagarMe Recebido:', $request->all());

        $type = $request->input('type');
        $data = $request->input('data');

        if (in_array($type, ['charge.paid', 'order.paid'])) {
            // Em PagarMe V5, webhook envia o objeto root em $data.
            $orderUuid = $data['order']['code'] ?? $data['code'] ?? null;
            $chargeId = $data['id'] ?? null; 

            // Se for PaymentLink, as vezes a order não possui code injetado, mas temos o ID na Order
            $order = Order::where('uuid', $orderUuid)->first();

            // Se não encontrou pelo UUID, pode ser Payment Link. No Link salvamos gateway_transaction_id = pl_xxx
            if (!$order) {
                // Se foi gerado por checkout direto o $data['id'] é ch_xxx. Se Payment Link será um link associado à charge
                // Como não queremos confiar cegamente, podemos procurar por uma Order que bate ID e pendente. Pagar.me V5 pode retornar metadata.
                $orderUuidMeta = $data['metadata']['uuid'] ?? null;
                if ($orderUuidMeta) {
                    $order = Order::where('uuid', $orderUuidMeta)->first();
                }
            }

            if ($order && $order->status !== \App\Enums\OrderStatusEnum::PAID) {
                // Ao pagar com sucesso, atualiza o chargeId para podermos estornar no futuro
                $realChargeId = $data['last_transaction']['id'] ?? $chargeId;
                
                $order->update([
                    'status' => \App\Enums\OrderStatusEnum::PAID,
                    'paid_at' => now(),
                    'gateway_transaction_id' => $realChargeId 
                ]);
                Log::info("Pedido {$order->uuid} Pago via PagarMe Core V5!");
            } else {
                Log::warning("Pagar.Me Webhook: Pedido não mapeado. Typo: {$type}");
            }
        } elseif (in_array($type, ['charge.refunded', 'order.canceled'])) {
            $chargeId = $data['id'] ?? null;
            if ($chargeId) {
                $order = Order::where('gateway_transaction_id', $chargeId)->first();
                if (!$order && isset($data['code'])) {
                     $order = Order::where('uuid', $data['code'])->first();
                }

                if ($order && $order->status !== \App\Enums\OrderStatusEnum::CANCELLED) {
                     $order->update(['status' => \App\Enums\OrderStatusEnum::CANCELLED]);
                     Log::info("Pedido {$order->uuid} cancelado por Reflexo Pagar.Me.");
                }
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
