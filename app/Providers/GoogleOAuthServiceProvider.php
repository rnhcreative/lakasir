<?php

namespace App\Providers;

use League\Flysystem\Filesystem;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Google\Service\Drive as GoogleDrive;
use Masbug\Flysystem\GoogleDriveAdapter;
use Illuminate\Filesystem\FilesystemAdapter;

class GoogleOAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('google', function ($app, $config) {
            $client = new GoogleClient();
            $client->setAuthConfig(storage_path('app/google/credentials.json'));
            $client->setAccessType('offline'); // penting!
            $client->setApprovalPrompt('force'); // penting!
            $client->addScope(GoogleDrive::DRIVE_FILE);

            // Load the stored token (with refresh_token)
            $accessToken = json_decode(file_get_contents(storage_path('app/google/token.json')), true);

            $client->setAccessToken($accessToken);

            // Refresh if expired
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents(
                    storage_path('app/google/token.json'),
                    json_encode($client->getAccessToken())
                );
            }

            try {
                $service = new GoogleDrive($client);
                $adapter = new GoogleDriveAdapter($service, $config['folder_id'] ?? null);
                $driver = new Filesystem($adapter);

                Log::info('Google Drive Adapter initialized successfully.');

                return new FilesystemAdapter($driver, $adapter, $config);
            } catch (\Throwable $e) {
                Log::error('Google Drive Adapter Error: ' . $e->getMessage());

                throw $e;
            }
        });
    }
}
