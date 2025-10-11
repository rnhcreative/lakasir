<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeProduction extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'optimize:production {--no-restart : Skip restarting PHP after optimization}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize Laravel for production with OPcache check and PHP restart';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('🚀 Optimizing Laravel for production...');

        // Step 1: Clear & rebuild caches
        $this->callSilent('optimize:clear');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('event:cache');
        $this->call('optimize');

        // Step 2: Ensure production mode in .env
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);
            $envContent = preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', $envContent);
            file_put_contents($envPath, $envContent);
        }

        // Step 3: Check OPcache
        $this->line('');
        $this->info('🔍 Checking OPcache status...');

        if (function_exists('opcache_get_status')) {
            $status = @opcache_get_status(false);

            if ($status && isset($status['opcache_enabled']) && $status['opcache_enabled']) {
                $memory = round($status['memory_usage']['used_memory'] / 1024 / 1024, 1);
                $this->info("✅ OPcache is ENABLED ({$memory} MB used).");
            } else {
                $this->warn('⚠️  OPcache is DISABLED!');
                $this->line('👉 To enable it, edit your php.ini and add:');
                $this->line('   opcache.enable=1');
                $this->line('   opcache.enable_cli=1');
                $this->line('   opcache.memory_consumption=256');
                $this->line('   opcache.validate_timestamps=0');
                $this->line('   opcache.max_accelerated_files=20000');
            }
        } else {
            $this->warn('⚠️  OPcache extension not found!');
            $this->line('👉 You may need to install or enable it manually.');
        }

        // Step 4: Restart PHP / Herd
        if (! $this->option('no-restart')) {
            $this->line('');
            $this->info('🔁 Restarting PHP to refresh OPcache...');

            if (stripos(PHP_OS, 'Darwin') !== false) {
                // macOS (Herd or Valet)
                if (shell_exec('which herd')) {
                    shell_exec('herd restart php');
                    $this->info('✅ PHP restarted via Herd.');
                } elseif (shell_exec('which valet')) {
                    shell_exec('valet restart');
                    $this->info('✅ PHP restarted via Valet.');
                } else {
                    $this->warn('⚠️  Herd/Valet not detected. Please restart PHP manually if needed.');
                }
            } else {
                // Linux or Windows environment
                if (shell_exec('which systemctl')) {
                    shell_exec('sudo systemctl restart php-fpm');
                    $this->info('✅ PHP-FPM restarted.');
                } else {
                    $this->warn('⚠️  Could not detect PHP service manager. Restart PHP manually if needed.');
                }
            }
        } else {
            $this->line('');
            $this->info('⏭️  Skipped PHP restart (you can do it manually if needed).');
        }

        $this->line('');
        $this->info('✅ Laravel successfully optimized for production!');
    }
}
