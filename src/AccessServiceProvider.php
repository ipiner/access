<?php

declare(strict_types=1);

namespace Pin\Access;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Pin\Access\Contracts\AccessUser;
use Pin\Support\ServiceProvider;

/**
 * 访问服务提供者
 */
class AccessServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/access.php', 'pin.access');
        $this->publishes([
            __DIR__.'/../config/access.php' => config_path('pin/access.php'),
        ], 'pin-access-config');

        $this->publishes(
            [__DIR__.'/../database/migrations' => database_path('migrations')],
            'pin-access-migrations'
        );

        $this->app->bind('pin.access', Access::class);

        Gate::before(
            function (Authenticatable $user, string $ability, array $parameters) {
                if ($ability !== Access::ABILITY || ! $user instanceof AccessUser) {
                    return null;
                }

                /** @var AccessUser $user */
                if ($user->hasAllAccess()) {
                    return true;
                }

                return in_array(
                    $parameters[0],
                    Facades\Access::forUser($user)->codes()
                );
            });
    }
}
