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

        $zipFileName = 'Ensaio_Completo_' . substr($order->uuid, 0, 8) . '.zip';
        $zipsDir = storage_path('app/zips');
        $zipPath = $zipsDir . '/' . $zipFileName;

        if (!file_exists($zipsDir)) {
            mkdir($zipsDir, 0755, true);
        }

        // Validação de Hospedagem (30 Dias Cache)
        if (file_exists($zipPath)) {
            $fileAgeDays = (time() - filemtime($zipPath)) / (60 * 60 * 24);
            if ($fileAgeDays > 30) {
                // Muito velho, excluir para forçar regeneração
                unlink($zipPath);
            } else {
                // ZIP fresco ainda disponível
                return response()->download($zipPath);
            }
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            
            // Subdiretório temporário para Downsync do S3/Drive
            $tempRawPath = storage_path('app/temp_raws/' . $order->uuid);
            if (!file_exists($tempRawPath)) {
                mkdir($tempRawPath, 0755, true);
            }

            foreach ($order->items as $item) {
                if ($item->photo && Storage::disk($item->photo->storage_driver)->exists($item->photo->original_path)) {
                    
                    if ($item->photo->storage_driver !== 'local') {
                        // Downsync massivo da Nuvem para o Servidor temporariamente
                        $cloudFileStream = Storage::disk($item->photo->storage_driver)->readStream($item->photo->original_path);
                        $tempLocalFilePath = $tempRawPath . '/' . basename($item->photo->original_path);
                        
                        file_put_contents($tempLocalFilePath, $cloudFileStream);
                        if (is_resource($cloudFileStream)) { fclose($cloudFileStream); }
                        
                        $zip->addFile($tempLocalFilePath, $item->photo->original_name);
                        
                    } else {
                        // Puxa direto do disco NATIVO se ainda não foi arquivado
                        $physicalPath = Storage::disk('local')->path($item->photo->original_path);
                        $zip->addFile($physicalPath, $item->photo->original_name);
                    }
                }
            }
            $zip->close();
            
            // Limpa arquivos pesados em Downsync temporário caso existam
            if (file_exists($tempRawPath)) {
                \Illuminate\Support\Facades\File::deleteDirectory($tempRawPath);
            }

        } else {
            return back()->with('error', 'Falha estrutural ao empacotar os arquivos ZIP no Servidor.');
        }

        // Realiza o Download Padrão (Sem deletar na hora, preserva por 30 dias pro Gargalo)
        return response()->download($zipPath);
    }
}
