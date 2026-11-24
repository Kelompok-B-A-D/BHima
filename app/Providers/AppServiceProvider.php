<?php

namespace App\Providers;

use App\Contracts\AnalysisProvider;
use App\Services\Analysis\DeepSeekProvider;
use App\Services\Analysis\MockProvider;
use App\Services\Analysis\QuotaAwareProvider;
use App\Services\LlmQuotaService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private static bool $bootstrappingSqlite = false;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AnalysisProvider::class, function ($app) {
            $provider = strtolower(trim((string) config('legal_analysis.provider', 'mock')));

            if ($provider === 'deepseek') {
                return new QuotaAwareProvider(
                    $app->make(DeepSeekProvider::class),
                    $app->make(LlmQuotaService::class),
                );
            }

            return $app->make(MockProvider::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            $this->app->environment('production') ||
            str_starts_with((string) config('app.url'), 'https://') && request()->header('x-forwarded-proto') === 'https'
        ) {
            URL::forceScheme('https');
        }

        $this->configureResilientDatabase();
    }

    private function configureResilientDatabase(): void
    {
        if (self::$bootstrappingSqlite || $this->app->environment('testing') || $this->app->runningUnitTests()) {
            return;
        }

        $consoleCommand = $this->runningConsoleCommand();
        if (in_array($consoleCommand, [
            'config:cache',
            'config:clear',
            'route:cache',
            'route:clear',
            'view:cache',
            'view:clear',
            'event:cache',
            'event:clear',
            'optimize',
            'optimize:clear',
            'package:discover',
            'key:generate',
            'vendor:publish',
        ], true)) {
            return;
        }

        $defaultConnection = (string) config('database.default', 'sqlite');

        if (in_array($defaultConnection, ['mysql', 'mariadb'], true)) {
            if ($this->isMysqlConnectionReachable($defaultConnection)) {
                return;
            }
        } elseif ($defaultConnection !== 'sqlite') {
            return;
        }

        $sqlitePath = $this->resolveWritableSqlitePath();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.url' => null,
            'database.connections.sqlite.database' => $sqlitePath,
            'session.connection' => 'sqlite',
            'cache.stores.database.connection' => 'sqlite',
            'cache.stores.database.lock_connection' => 'sqlite',
            'queue.connections.database.connection' => 'sqlite',
            'queue.batching.database' => 'sqlite',
            'queue.failed.database' => 'sqlite',
        ]);

        DB::purge('mysql');
        DB::purge('mariadb');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        if (
            $consoleCommand !== null &&
            (str_starts_with($consoleCommand, 'migrate') || str_starts_with($consoleCommand, 'db:'))
        ) {
            return;
        }

        $this->ensureSqliteDatabaseInitialized($sqlitePath);
    }

    private function isMysqlConnectionReachable(string $connection): bool
    {
        $host = trim((string) config("database.connections.{$connection}.host", '127.0.0.1'));
        $port = (int) config("database.connections.{$connection}.port", 3306);
        $database = (string) config("database.connections.{$connection}.database", '');

        if ($host === '') {
            return false;
        }

        $flagFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bhima_mysql_down_'.md5("{$host}:{$port}:{$database}").'.flag';

        if (is_file($flagFile) && (time() - (int) @filemtime($flagFile)) < 60) {
            return false;
        }

        $socket = @fsockopen($host, $port > 0 ? $port : 3306, $errno, $errstr, 0.75);
        if (! is_resource($socket)) {
            @file_put_contents($flagFile, (string) time());

            return false;
        }
        @fclose($socket);

        try {
            DB::connection($connection)->getPdo();
            if (is_file($flagFile)) {
                @unlink($flagFile);
            }

            return true;
        } catch (\Throwable) {
            @file_put_contents($flagFile, (string) time());

            return false;
        }
    }

    private function resolveWritableSqlitePath(): string
    {
        $configured = (string) config('database.connections.sqlite.database', '');
        $candidates = [];

        if (
            $configured !== '' &&
            $configured !== ':memory:' &&
            (str_ends_with($configured, '.sqlite') || str_ends_with($configured, '.db'))
        ) {
            $candidates[] = $configured;
        }

        $candidates[] = database_path('database.sqlite');
        $candidates[] = storage_path('framework/database.sqlite');
        $candidates[] = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bhima_fallback.sqlite';

        foreach (array_unique($candidates) as $candidate) {
            $dir = dirname($candidate);
            if (is_file($candidate) && is_writable($candidate)) {
                return $candidate;
            }

            if (is_dir($dir) && is_writable($dir)) {
                if (! is_file($candidate)) {
                    @touch($candidate);
                }

                if (is_file($candidate) && is_writable($candidate)) {
                    return $candidate;
                }
            }
        }

        return database_path('database.sqlite');
    }

    private function ensureSqliteDatabaseInitialized(string $sqlitePath): void
    {
        $hash = md5($sqlitePath);
        $markerFile = sys_get_temp_dir().DIRECTORY_SEPARATOR."bhima_sqlite_{$hash}.initialized";

        if (is_file($markerFile) && is_file($sqlitePath) && (int) @filesize($sqlitePath) > 32768) {
            return;
        }

        $lockFile = sys_get_temp_dir().DIRECTORY_SEPARATOR."bhima_sqlite_{$hash}.lock";
        $lockHandle = @fopen($lockFile, 'c');
        if (is_resource($lockHandle)) {
            @flock($lockHandle, LOCK_EX);
        }

        self::$bootstrappingSqlite = true;

        try {
            if (! is_file($sqlitePath)) {
                @touch($sqlitePath);
            }

            $schema = Schema::connection('sqlite');
            $hasTables = $schema->hasTable('sessions')
                && $schema->hasTable('dashboard_records')
                && $schema->hasTable('knowledge_chunks')
                && $schema->hasTable('llm_daily_usages');

            if (! $hasTables) {
                $migrator = $this->app->make('migrator');
                $migrator->usingConnection('sqlite', function () use ($migrator) {
                    if (! $migrator->repositoryExists()) {
                        $migrator->getRepository()->createRepository();
                    }

                    $migrator->run([database_path('migrations')]);
                });
            }

            $recordCount = DB::connection('sqlite')->table('dashboard_records')->count();
            $userCount = DB::connection('sqlite')->table('users')->count();

            if ($recordCount === 0 || $userCount === 0) {
                $seeder = $this->app->make(DatabaseSeeder::class);
                $seeder->setContainer($this->app);
                $seeder->run();
            }

            @file_put_contents($markerFile, (string) time());
        } catch (\Throwable $e) {
            report($e);
        } finally {
            self::$bootstrappingSqlite = false;
            if (is_resource($lockHandle)) {
                @flock($lockHandle, LOCK_UN);
                @fclose($lockHandle);
            }
        }
    }

    private function runningConsoleCommand(): ?string
    {
        if (! $this->app->runningInConsole()) {
            return null;
        }

        $argv = $_SERVER['argv'] ?? [];

        return isset($argv[1]) && is_string($argv[1]) ? trim($argv[1]) : null;
    }
}
