<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class MigrateOldImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:migrate-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra fotos brutas com mais de 30 dias para a Nuvem de Storage configurada (GDrive/S3)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Buscando imagens antigas...");
        
        $cloudDriver = Setting::where('key', 'cloud_default_driver')->value('value') ?? 's3';

        // Busca fotos com storage local que tenham mais de um mês de criação
        $photos = Photo::where('storage_driver', 'local')
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->get();

        foreach ($photos as $photo) {
            $this->info("Migrando foto ID {$photo->id} -> driver: {$cloudDriver}");
            
            try {
                // Recupera binario do disco local
                $content = Storage::disk('local')->get($photo->original_path);
                if (!$content) continue;
                
                // Salva na nuvem
                Storage::disk($cloudDriver)->put($photo->original_path, $content);
                
                // Remove local se subiu com sucesso na nuvem
                if (Storage::disk($cloudDriver)->exists($photo->original_path)) {
                    Storage::disk('local')->delete($photo->original_path);
                    
                    // Atualiza o registro
                    $photo->update(['storage_driver' => $cloudDriver, 'status' => 'cloud_archived']);
                }
            } catch (\Exception $e) {
                $this->error("Erro na foto {$photo->id}: " . $e->getMessage());
            }
        }

        $this->info("Migração concluída.");
    }
}
