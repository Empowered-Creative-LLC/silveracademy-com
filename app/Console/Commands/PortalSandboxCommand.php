<?php

namespace App\Console\Commands;

use Database\Seeders\PortalSandboxSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PortalSandboxCommand extends Command
{
    protected $signature = 'portal:sandbox
                            {--serve : Start the sandbox web server after setup}
                            {--port=8001 : Port for --serve}';

    protected $description = 'Create a local SQLite test lab (no Supabase / remote database required)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to seed sandbox data in production.');

            return self::FAILURE;
        }

        $databasePath = database_path('sandbox.sqlite');
        $envPath = base_path('.env.sandbox');
        $port = (int) $this->option('port');
        $appUrl = 'http://127.0.0.1:'.$port;

        $this->info('Setting up an isolated portal sandbox.');
        $this->comment('This uses a local SQLite file. It does not connect to Supabase or production.');
        $this->newLine();

        File::ensureDirectoryExists(dirname($databasePath));
        if (File::exists($databasePath)) {
            File::delete($databasePath);
        }
        File::put($databasePath, '');

        $this->writeSandboxEnv($envPath, $databasePath, $appUrl);

        config([
            'database.default' => 'sandbox',
            'database.connections.sandbox.database' => $databasePath,
            'session.driver' => 'file',
            'cache.default' => 'file',
            'queue.default' => 'sync',
            'mail.default' => 'log',
            'portal.sandbox_enabled' => true,
            'app.url' => $appUrl,
        ]);
        DB::purge('sandbox');
        DB::reconnect('sandbox');
        DB::setDefaultConnection('sandbox');

        $this->components->task('Migrating sandbox database', function () {
            Artisan::call('migrate:fresh', [
                '--database' => 'sandbox',
                '--force' => true,
                '--no-interaction' => true,
            ]);
        });

        $this->components->task('Seeding test accounts', function () {
            Artisan::call('db:seed', [
                '--class' => PortalSandboxSeeder::class,
                '--database' => 'sandbox',
                '--force' => true,
                '--no-interaction' => true,
            ]);
        });

        $this->newLine();
        $this->info('Portal Test Lab is ready (local SQLite, no remote database).');
        $this->line('Database file: '.$databasePath);
        $this->newLine();
        $this->table(
            ['Role', 'Email', 'Password'],
            collect(PortalSandboxSeeder::accounts())->map(fn (array $account) => [
                $account['role'],
                $account['email'],
                $account['password'],
            ])->all()
        );

        $this->newLine();
        $this->warn('Restart the site with the sandbox environment (stop any existing php artisan serve first):');
        $this->line("  php artisan serve --env=sandbox --port={$port}");
        $this->line('  Then open '.$appUrl.'/login');
        $this->line('  Admin checklist: '.$appUrl.'/portal/sandbox');

        if ($this->option('serve')) {
            $this->newLine();
            $this->info("Starting sandbox server on {$appUrl} …");

            // Set APP_ENV in the process environment so every PHP child spawned by
            // `php artisan serve` inherits it and loads .env.sandbox correctly.
            putenv('APP_ENV=sandbox');

            return $this->call('serve', [
                '--env' => 'sandbox',
                '--port' => $port,
            ]);
        }

        return self::SUCCESS;
    }

    private function writeSandboxEnv(string $envPath, string $databasePath, string $appUrl): void
    {
        $appKey = config('app.key');
        if (! is_string($appKey) || $appKey === '') {
            $this->error('APP_KEY is missing. Run php artisan key:generate first.');
            throw new \RuntimeException('Missing APP_KEY');
        }

        $contents = <<<ENV
APP_NAME="Silver Academy Sandbox"
APP_ENV=sandbox
APP_KEY={$appKey}
APP_DEBUG=true
APP_URL={$appUrl}

DB_CONNECTION=sqlite
DB_DATABASE={$databasePath}

SESSION_DRIVER=file
SESSION_LIFETIME=480
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
LOG_CHANNEL=stack
FILESYSTEM_DISK=local

PORTAL_COMING_SOON=false
PORTAL_SANDBOX=true
ENV;

        File::put($envPath, $contents);
        $this->comment('Wrote '.$envPath);
    }
}
