<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Gallery;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    // Etapa 1: Revisão do Carrinho e Seleção de Pacote
    public function review(Request $request, $uuid)
    {
        $gallery = Gallery::where('uuid', $uuid)->firstOrFail();
        $photosArray = explode(',', $request->input('photo_ids'));
        
        if (empty($photosArray) || empty($photosArray[0])) {
            return back()->with('error', 'Selecione ao menos uma foto.');
        }

        $totalSelected = count($photosArray);
        $packages = Package::where('is_active', true)->get();

        // Envia as fotos via session flash to keep it secure without exposing all IDs in GET
        $request->session()->put('checkout_photos', $photosArray);

        return view('client.checkout.review', compact('gallery', 'totalSelected', 'packages', 'photosArray'));
    }

    // Etapa 2: Confirmação do Pedido e Gravação
    public function process(Request $request, $uuid)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|string',
            'document' => 'nullable|string'
        ]);

        if ($request->filled('document') && empty(Auth::user()->document)) {
             $user = Auth::user();
             $user->document = preg_replace('/[^0-9]/', '', $request->document);
             $user->save();
        }

        $paymentMethodEnum = \App\Enums\PaymentMethodEnum::tryFrom($request->payment_method);
        if (!$paymentMethodEnum) {
            return back()->with('error', 'Método de pagamento inválido.');
        }

        $gallery = Gallery::where('uuid', $uuid)->firstOrFail();
        $photosArray = $request->session()->get('checkout_photos', []);
        
        if (empty($photosArray)) {
            return redirect()->route('client.galleries.show', $gallery->uuid)->with('error', 'Sessão expirada. Refaça a seleção.');
        }

        $package = Package::find($request->package_id);
        $totalSelected = count($photosArray);
        $extraPhotos = max(0, $totalSelected - $package->included_photos_count);
        $totalAmount = $package->price + ($extraPhotos * $package->extra_photo_price);

        // Gera a Order com método embutido
        $order = Order::create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'gallery_id' => $gallery->id,
            'total_photos' => $totalSelected,
            'included_photos' => min($totalSelected, $package->included_photos_count),
            'extra_photos' => $extraPhotos,
            'total_amount' => $totalAmount,
            'status' => \App\Enums\OrderStatusEnum::PENDING,
            'gateway' => $paymentMethodEnum->value // Guarda se é PIX, Boleto ou Cartão
        ]);

        // Grava as OrderItems (As listagens de fotos prontas a serem destravadas)
        foreach ($photosArray as $index => $photoId) {
            OrderItem::create([
                'order_id' => $order->id,
                'photo_id' => $photoId,
                'is_extra' => ($index >= $package->included_photos_count),
                'price' => ($index >= $package->included_photos_count) ? $package->extra_photo_price : 0
            ]);
        }

        $request->session()->forget('checkout_photos');

        // Coleta Array Mestre do Cartão se aplicável
        $paymentData = [];
        if ($paymentMethodEnum === \App\Enums\PaymentMethodEnum::CREDIT_CARD && $request->filled('card_number')) {
             $paymentData = $request->only(['card_holder', 'card_number', 'card_expiry', 'card_cvv']);
        }

        // Factory Pattern resolvendo Múltiplos Gateways via Enum Method
        $gateway = \App\Services\Payments\PaymentGatewayFactory::resolve($paymentMethodEnum);
        $paymentResponse = $gateway->generateCharge($order, empty($paymentData) ? null : $paymentData);

        if (!$paymentResponse->success) {
            // Em caso de falhas de comunicação de API
            return redirect()->route('client.dashboard')->with('error', $paymentResponse->message);
        }

        if ($paymentResponse->externalId && empty($order->gateway_transaction_id)) {
            $order->update(['gateway_transaction_id' => $paymentResponse->externalId]);
        }
        
        if (!empty($paymentResponse->payload)) {
            $order->update(['gateway_payload' => $paymentResponse->payload]);
        }

        if ($paymentResponse->redirectUrl) {
            // Redireciona para Plataforma Externa de Faturamento (ApproveLinks / Links de Ocultos Boleto/Pix)
            return redirect($paymentResponse->redirectUrl);
        }

        // Transparente Completo Autorizado (Cartões à vista aprovados de primeira ou dinheiro físico)
        return redirect()->route('client.dashboard')->with('success', $paymentResponse->message);
    }

    // Etapa 3: Retentativa de Pagamento Pense/Falho
    public function retryPayment(Request $request, $uuid)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'card_number' => 'nullable|string',
            'card_holder' => 'nullable|string',
            'card_expiry' => 'nullable|string',
            'card_cvv' => 'nullable|string'
        ]);

        $order = Order::where('uuid', $uuid)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status === \App\Enums\OrderStatusEnum::PAID) {
            return redirect()->route('client.dashboard')->with('error', 'Este pedido já está pago e liberado.');
        }

        $paymentMethodEnum = \App\Enums\PaymentMethodEnum::tryFrom($request->payment_method);
        if (!$paymentMethodEnum) {
            return back()->with('error', 'Método de pagamento inválido.');
        }

        // Coleta Novamente se tentar por Cartão Limpo
        $paymentData = [];
        if ($paymentMethodEnum === \App\Enums\PaymentMethodEnum::CREDIT_CARD && $request->filled('card_number')) {
             $paymentData = $request->only(['card_holder', 'card_number', 'card_expiry', 'card_cvv']);
        }

        // Atualiza o gateway de preferência
        $order->update(['gateway' => $paymentMethodEnum->value]);

        $gateway = \App\Services\Payments\PaymentGatewayFactory::resolve($paymentMethodEnum);
        $paymentResponse = $gateway->generateCharge($order, empty($paymentData) ? null : $paymentData);

        if (!$paymentResponse->success) {
            return redirect()->route('client.dashboard')->with('error', $paymentResponse->message);
        }

        if (!empty($paymentResponse->payload)) {
            $order->update(['gateway_payload' => $paymentResponse->payload]);
        }

        if ($paymentResponse->redirectUrl) {
            return redirect($paymentResponse->redirectUrl);
        }

        return redirect()->route('client.dashboard')->with('success', $paymentResponse->message);
    }
}
