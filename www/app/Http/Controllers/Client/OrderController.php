<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use ZipArchive;

class OrderController extends Controller
{
    public function show($uuid)
    {
        $order = Order::where('uuid', $uuid)
                      ->where('user_id', Auth::id())
                      ->with(['items.photo', 'package', 'gallery'])
                      ->firstOrFail();

        return view('client.orders.show', compact('order'));
    }

    public function downloadZip($uuid)
    {
        $order = Order::where('uuid', $uuid)
                      ->where('user_id', Auth::id())
                      ->with(['items.photo'])
                      ->firstOrFail();

        if ($order->status !== 'paid') {
            abort(403, 'A fatura ainda não foi confirmada pelo fotógrafo.');
        }

        $zip = new ZipArchive();
        $zipFileName = 'Ensaio_Completo_' . substr($order->uuid, 0, 8) . '.zip';
        
        // Assegura que a pasta raiz temporária exista
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        
        $zipPath = $tempPath . '/' . $zipFileName;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($order->items as $item) {
                // As fotos na origem são salvas por $file->store(), ou seja, o storage_driver é 'local' e a key da storage é original_path
                if ($item->photo && Storage::disk($item->photo->storage_driver)->exists($item->photo->original_path)) {
                    
                    $physicalPath = Storage::disk($item->photo->storage_driver)->path($item->photo->original_path);
                    
                    // Adiciona ao zip usando o nome descritivo amigável
                    $zip->addFile($physicalPath, $item->photo->original_name);
                }
            }
            $zip->close();
        } else {
            return back()->with('error', 'Falha ao processar o formato Zip no Servidor.');
        }

        // Realiza o Download em formato Binário e apaga o ZIP logo em seguida economizando HD
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
