<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class PaymentWebhookController extends Controller
{
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
            if ($order->status !== 'paid') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                Log::info('Pedido UUID ' . $orderUuid . ' marcado como PAID via Webhook!');
                
                // TODO: Event::dispatch(new OrderPaid($order));
            }
        } elseif (in_array($event, ['PAYMENT_OVERDUE', 'PAYMENT_DELETED', 'PAYMENT_REFUNDED'])) {
             // Opções secundárias (Estornos)
             $order->update(['status' => 'cancelled']);
             Log::info('Pedido UUID ' . $orderUuid . ' CANCELADO via Webhook!');
        }

        return response()->json(['status' => 'success'], 200);
    }
}
