<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Photo;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Illuminate\Support\Facades\Storage;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $photo;
    public $timeout = 180; // 3 minutos

    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    public function handle(): void
    {
        // Pega do storage (Original já foi feito o upload e salvo protegida)
        $originalContent = Storage::disk($this->photo->storage_driver)->get($this->photo->original_path);
        
        $manager = new ImageManager(new Driver());
        // v4 usa decode() ao invés de read()
        $image = $manager->decode($originalContent);

        // --- 1. Miniatura Admin ---
        $thumbImage = clone $image;
        // Na UI Administrativa, as colunas rendem no máximo ~300px de largura e 150px de altura. ScaleDown salva processamento preservando memória na escala
        $thumbImage->scaleDown(width: 350);
        // Codificação WEBP usando a API V4 nativa
        $thumbEncoded = $thumbImage->encode(new \Intervention\Image\Encoders\WebpEncoder(80))->toString();
        
        $thumbPath = 'photos/' . $this->photo->gallery_id . '/' . $this->photo->uuid . '_thumb.webp';
        Storage::disk('public')->put($thumbPath, $thumbEncoded);
        
        // --- 2. Miniatura Público (Com Marca d'água) ---
        $watermarkImage = clone $image;
        // Escala perfeita para Lightbox / Grade Mansory de Client Dashboard sem exceder Full HD
        $watermarkImage->scaleDown(width: 1200);

        try {
            $logoPath = public_path('images/watermark-default.png');
            if (file_exists($logoPath)) {
                $watermarkImage->insert($logoPath, 'center', 50, 50);
            }
        } catch (\Exception $e) {
            // Ignora
        }

        $watermarkEncoded = $watermarkImage->encode(new \Intervention\Image\Encoders\WebpEncoder(85))->toString();
        $watermarkPath = 'photos/' . $this->photo->gallery_id . '/' . $this->photo->uuid . '_watermark.webp';
        Storage::disk('public')->put($watermarkPath, $watermarkEncoded);

        // Atualizar Model
        $this->photo->update([
            'thumbnail_path' => $thumbPath,
            'watermark_path' => $watermarkPath,
            'status' => 'ready'
        ]);
    }
}
