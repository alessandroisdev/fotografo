<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CloudSettingProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // Se o repositório de settings estiver acessível via tabela ou cache (já contido no helper config())
            
            // Injetando AWS S3
            if (config('settings.s3_key')) {
                config([
                    'filesystems.disks.s3.key' => config('settings.s3_key'),
                    'filesystems.disks.s3.secret' => config('settings.s3_secret'),
                    'filesystems.disks.s3.region' => config('settings.s3_region'),
                    'filesystems.disks.s3.bucket' => config('settings.s3_bucket'),
                    'filesystems.disks.s3.endpoint' => config('settings.s3_endpoint'),
                ]);
            }

            // Injetando Google Drive API (Mapeamento do Flysystem adapter)
            if (config('settings.google_client_id')) {
                config([
                    'filesystems.disks.google.clientId' => config('settings.google_client_id'),
                    'filesystems.disks.google.clientSecret' => config('settings.google_client_secret'),
                    'filesystems.disks.google.refreshToken' => config('settings.google_refresh_token'),
                    'filesystems.disks.google.folderId' => config('settings.google_folder_id'),
                ]);
            }

            \Illuminate\Support\Facades\Storage::extend('google', function ($app, $config) {
                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);
                
                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folderId'] ?? '', [ 'useHasDir' => true ]);
                return new \Illuminate\Filesystem\FilesystemAdapter(
                    new \League\Flysystem\Filesystem($adapter, $config),
                    $adapter,
                    $config
                );
            });

        } catch (\Exception $e) {
            // Em instâncias puras de migração/descomissionamento, ignorar.
        }
    }
}
