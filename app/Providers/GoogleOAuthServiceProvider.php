<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Support\Facades\Storage;

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
            $client->addScope(GoogleDrive::DRIVE);

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

            $service = new GoogleDrive($client);
            $adapter = new GoogleDriveAdapter($service, $config['folder_id'] ?? null);
            $driver = new Filesystem($adapter);

            return new FilesystemAdapter($driver, $adapter, $config);
        });
    }
}
