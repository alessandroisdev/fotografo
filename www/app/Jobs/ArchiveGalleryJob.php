<?php

namespace App\Jobs;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ArchiveGalleryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $gallery;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Timeout em segundos (pode demorar pra subir AWS/GDrive).
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(Gallery $gallery)
    {
        $this->gallery = $gallery;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $archiveDisk = config('settings.archive_disk', 'local');
        
        if ($archiveDisk === 'local') {
            Log::info("Arquivamento ignorado para Galeria {$this->gallery->uuid}. Disk padrao é local.");
            return;
        }

        $photos = Photo::where('gallery_id', $this->gallery->id)
                      ->where('storage_driver', 'local')
                      ->where('status', 'ready')
                      ->get();

        Log::info("Iniciando motor de arquivamento da Galeria {$this->gallery->uuid} -> Mapeadas {$photos->count()} fotos para Cloud: {$archiveDisk}");

        foreach ($photos as $photo) {
            try {
                if (Storage::disk('local')->exists($photo->original_path)) {
                    // Copiar da máquina local para a Nuvem de Destino
                    $stream = Storage::disk('local')->readStream($photo->original_path);
                    
                    // Colocar o arquivo na mesma arvore mas no disco Remoto (Google / S3)
                    $cloudUploaded = Storage::disk($archiveDisk)->put($photo->original_path, $stream);
                    
                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    if ($cloudUploaded) {
                        // Deletar O bruto da maquina nativa poupando espaço
                        Storage::disk('local')->delete($photo->original_path);
                        
                        // Assinalar novo provedor ao banco
                        $photo->update(['storage_driver' => $archiveDisk]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Archival falhou na foto {$photo->id}: " . $e->getMessage());
            }
        }
        
        Log::info("Galeria {$this->gallery->uuid} arquivada com sucesso em {$archiveDisk}.");
    }
}
