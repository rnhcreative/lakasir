<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;
use App\Models\Tenants\BackupDatabase as BackupDatabaseModel;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup database harian dan upload ke Google Drive + simpan lokal';

    public function handle()
    {
        $this->info('Menjalankan proses backup database...');

        // Membuat log backup pada tabel backup_databases
        $backupDatabase = BackupDatabaseModel::create([
            'status' => 'started',
        ]);

        // --- 1. Tentukan nama file ---
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup-{$date}.sql";
        $zipFilename = "backup-{$date}.zip";
        $tempDir = storage_path('app/backups');
        $localDir = storage_path('backup/db'); // folder cadangan lokal

        // --- 2. Pastikan folder backup ada ---
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        if (!file_exists($localDir)) {
            mkdir($localDir, 0755, true);
        }

        $sqlFile = "{$tempDir}/{$filename}";
        $zipFile = "{$tempDir}/{$zipFilename}";

        // --- 3. Jalankan mysqldump ---
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg(env('DB_USERNAME')),
            escapeshellarg(env('DB_PASSWORD')),
            escapeshellarg(env('DB_HOST')),
            escapeshellarg(env('DB_DATABASE')),
            escapeshellarg($sqlFile)
        );

        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $backupDatabase->update([
                'is_uploaded_on_local' => false,
                'is_uploaded_on_cloud' => false,
                'status' => 'failed'
            ]);

            $this->error('Gagal membuat dump database.');

            return 1;
        }

        // --- 4. Kompres ke ZIP ---
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($sqlFile, basename($sqlFile));
            $zip->close();
            unlink($sqlFile); // hapus file .sql mentah
        } else {
            $backupDatabase->update([
                'is_uploaded_on_local' => false,
                'is_uploaded_on_cloud' => false,
                'status' => 'failed'
            ]);

            $this->error('Gagal membuat file ZIP.');

            return 1;
        }

        $this->info('Backup berhasil dibuat: ' . $zipFilename);

        // --- 5. Simpan juga ke folder lokal (storage/backup/db) ---
        $localCopy = $localDir . '/' . $zipFilename;
        if (copy($zipFile, $localCopy)) {
            $this->info("Backup juga disalin ke: {$localCopy}");
        } else {
            $backupDatabase->update([
                'is_uploaded_on_local' => false,
                'is_uploaded_on_cloud' => false,
                'status' => 'failed'
            ]);

            $this->error('Gagal menyalin backup ke folder lokal.');
        }

        // --- 6. Upload ke Google Drive ---
        try {
            Storage::disk('google')->put($zipFilename, file_get_contents($zipFile));
            $this->info('Upload ke Google Drive berhasil!');

            $backupDatabase->update([
                'filename' => $zipFilename,
                'is_uploaded_on_local' => true,
                'is_uploaded_on_cloud' => true,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            $backupDatabase->update([
                'filename' => $zipFilename,
                'is_uploaded_on_local' => true,
                'is_uploaded_on_cloud' => false,
                'status' => 'not sync'
            ]);

            $this->error('Gagal upload ke Google Drive: ' . $e->getMessage());
        }

        // --- 7. Hapus backup sementara lama (lebih dari 7 hari) ---
        foreach (glob($tempDir . '/*.zip') as $file) {
            if (filemtime($file) < now()->subDays(7)->getTimestamp()) {
                unlink($file);
            }
        }

        // --- 8. Hapus backup lokal lama (misal lebih dari 14 hari) ---
        foreach (glob($localDir . '/*.zip') as $file) {
            if (filemtime($file) < now()->subDays(14)->getTimestamp()) {
                unlink($file);
            }
        }

        $this->info('Pembersihan backup lama selesai.');
        $this->info('Backup database selesai.');

        return 0;
    }
}
