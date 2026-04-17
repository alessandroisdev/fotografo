<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanZipsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:clean-zips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Garbage Collector: Exclui da fisicalidade do servidor galerias ZIPadas (Ensaio_Completo) geradas a mais de 30 dias para liberação passiva de HD Local.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $zipsDir = storage_path('app/zips');

        if (!File::exists($zipsDir)) {
            $this->info("Nenhum diretório raiz de Zips localizado. Encerrando.");
            return;
        }

        $files = File::files($zipsDir);
        $deletedCount = 0;
        $now = time();

        foreach ($files as $file) {
            $fileAgeDays = ($now - filemtime($file->getPathname())) / (60 * 60 * 24);
            
            // Margem exata: Apaga arquivos que viraram o trigésimo primeiro (31) dia
            if ($fileAgeDays > 30) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        $msg = "Garbage Collector Concluído. {$deletedCount} ZIP(s) antigos (> 30 dias) pulverizados da HD física do servidor.";
        Log::info($msg);
        $this->info($msg);
    }
}
