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
            'package_id' => 'required|exists:packages,id'
        ]);

        $gallery = Gallery::where('uuid', $uuid)->firstOrFail();
        $photosArray = $request->session()->get('checkout_photos', []);
        
        if (empty($photosArray)) {
            return redirect()->route('client.galleries.show', $gallery->uuid)->with('error', 'Sessão expirada. Refaça a seleção.');
        }

        $package = Package::find($request->package_id);
        $totalSelected = count($photosArray);
        $extraPhotos = max(0, $totalSelected - $package->included_photos_count);
        $totalAmount = $package->price + ($extraPhotos * $package->extra_photo_price);

        // Gera a Order
        $order = Order::create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'gallery_id' => $gallery->id,
            'total_photos' => $totalSelected,
            'included_photos' => min($totalSelected, $package->included_photos_count),
            'extra_photos' => $extraPhotos,
            'total_amount' => $totalAmount,
            'status' => 'pending'
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

        return redirect()->route('client.dashboard')->with('success', 'Pedido gerado com sucesso! Acesse o portal do PIX ou aguarde o contato do seu Fotógrafo.');
    }
}
