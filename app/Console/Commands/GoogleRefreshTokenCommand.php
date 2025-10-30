<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;

class GoogleRefreshTokenCommand extends Command
{
    /**
     * Nama command.
     *
     * @var string
     */
    protected $signature = 'google:refresh';

    /**
     * Deskripsi command.
     *
     * @var string
     */
    protected $description = 'Refresh token Google Drive OAuth secara otomatis tanpa login ulang';

    /**
     * Jalankan command.
     */
    public function handle()
    {
        $credentialsPath = storage_path('app/google/credentials.json');
        $tokenPath = storage_path('app/google/token.json');

        if (!file_exists($credentialsPath)) {
            $this->error("❌ File credentials.json tidak ditemukan di: {$credentialsPath}");
            return 1;
        }

        if (!file_exists($tokenPath)) {
            $this->error("❌ File token.json tidak ditemukan di: {$tokenPath}");
            $this->line("➡️  Jalankan 'php artisan google:auth' terlebih dahulu untuk membuat token awal.");
            return 1;
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->addScope(GoogleDrive::DRIVE_FILE);

        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        if (!$client->getRefreshToken()) {
            $this->error("⚠️ Token ini tidak memiliki refresh_token. Jalankan ulang 'php artisan google:auth' dengan 'approvalPrompt=force'.");
            return 1;
        }

        if (!$client->isAccessTokenExpired()) {
            $this->info('✅ Token masih valid — belum perlu diperbarui.');
            return 0;
        }

        // Refresh token
        $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

        if (isset($newToken['error'])) {
            $this->error('❌ Gagal refresh token: ' . $newToken['error_description']);
            return 1;
        }

        // Gabungkan data baru dengan token lama (agar refresh_token tidak hilang)
        $mergedToken = array_merge($accessToken, $newToken);

        file_put_contents($tokenPath, json_encode($mergedToken, JSON_PRETTY_PRINT));

        $this->info("✅ Token berhasil diperbarui dan disimpan ke: {$tokenPath}");

        return 0;
    }
}
