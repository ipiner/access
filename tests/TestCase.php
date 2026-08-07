<?php

declare(strict_types=1);

namespace Pin\Tests;

use Pin\Access\AccessServiceProvider;

class TestCase extends \Pin\Testing\TestCase
{
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom([
            '--path' => [
                __DIR__.'/../database/migrations',
                __DIR__.'/migrations',
            ],
        ]);
    }

    protected function providers(): array
    {
        return [
            ...parent::providers(),
            AccessServiceProvider::class,
        ];
    }
}
