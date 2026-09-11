<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        // Never allow a test process to read Laravel's cached local config.
        // That cache can contain DB_DATABASE=pos and would make
        // RefreshDatabase destructive to the developer database.
        $this->forceTestingEnvironment();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $database = (string) $app['db']->connection()->getDatabaseName();
        $environment = (string) $app->environment();

        if ($environment !== 'testing' || !str_ends_with(strtolower($database), '_testing')) {
            throw new \RuntimeException(
                sprintf(
                    'Tests interrompus : configuration de base dangereuse (environnement "%s", base "%s"). Les tests doivent utiliser une base suffixée par "_testing".',
                    $environment,
                    $database ?: '(inconnue)'
                )
            );
        }

        return $app;
    }

    /**
     * Force the isolated test environment before Laravel loads configuration.
     */
    private function forceTestingEnvironment(): void
    {
        $values = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'pos_testing',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
        ];

        foreach ($values as $key => $value) {
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        // A cached config.php is intentionally ignored for tests. Use a
        // per-process path that does not exist, so .env.testing is loaded.
        $cachePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pos-phpunit-config-'.getmypid().'.php';
        putenv('APP_CONFIG_CACHE='.$cachePath);
        $_ENV['APP_CONFIG_CACHE'] = $cachePath;
        $_SERVER['APP_CONFIG_CACHE'] = $cachePath;
    }
}
