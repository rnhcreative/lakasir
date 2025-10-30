<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;

class GoogleAuthCommand extends Command
{
    /**
     * Nama command.
     *
     * @var string
     */
    protected $signature = 'google:auth';

    /**
     * Deskripsi command.
     *
     * @var string
     */
    protected $description = 'Generate atau refresh token Google Drive OAuth (token.json)';

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

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setAccessType('offline'); // wajib untuk refresh_token
        $client->setApprovalPrompt('force');
        $client->addScope(GoogleDrive::DRIVE_FILE);

        // Jika token sudah ada, tawarkan untuk refresh
        if (file_exists($tokenPath)) {
            $this->info("🔹 Token sudah ada di {$tokenPath}");
            if (!$this->confirm('Ingin generate ulang token baru?', false)) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        // Buat URL untuk login
        $authUrl = $client->createAuthUrl();
        $this->line("🌐 Buka URL berikut di browser untuk mengautentikasi aplikasi:");
        $this->line($authUrl);

        // Ambil kode dari user
        $authCode = $this->ask('Masukkan kode (authorization code) dari URL di atas');

        // Tukarkan auth code dengan access + refresh token
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($accessToken['error'])) {
            $this->error('❌ Gagal mengambil token: ' . $accessToken['error_description']);
            return 1;
        }

        // Simpan token
        if (!is_dir(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0755, true);
        }
        file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));

        $this->info("✅ Token berhasil disimpan di: {$tokenPath}");

        // Cek apakah ada refresh_token
        if (!isset($accessToken['refresh_token'])) {
            $this->warn("⚠️ Tidak ada refresh_token di token.json — pastikan kamu set 'accessType=offline' & 'approvalPrompt=force'");
        }

        return 0;
    }
}
