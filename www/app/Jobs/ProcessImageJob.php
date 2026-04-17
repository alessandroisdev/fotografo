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
        $image = $manager->read($originalContent);

        // --- 1. Miniatura Admin ---
        // Resize proporcional pra Admin (ex: max 600px)
        $thumbImage = clone $image;
        $thumbImage->scale(width: 800);
        $thumbEncoded = $thumbImage->toWebp(75);
        
        $thumbPath = 'photos/' . $this->photo->gallery_id . '/' . $this->photo->uuid . '_thumb.webp';
        Storage::disk('public')->put($thumbPath, $thumbEncoded);
        
        // --- 2. Miniatura Público (Com Marca d'água) ---
        $watermarkImage = clone $image;
        $watermarkImage->scale(width: 1200); // um pouco maior pra ver detalhes com a logo

        // Tentar buscar logo configurada ou default
        try {
            // Em tese deveria haver $logoPath = storage_path('app/public/watermark.png');
            $logoPath = public_path('images/watermark-default.png');
            if (file_exists($logoPath)) {
                $watermarkImage->place($logoPath, 'center', 50, 50, 50); // 50% opacity
            }
        } catch (\Exception $e) {
            // Ignora watermark se não achar a img
        }

        $watermarkEncoded = $watermarkImage->toWebp(80);
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
