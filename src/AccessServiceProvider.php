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
     * 注册配置和当前用户的权限服务。
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/access.php', 'pin.access');
        $this->app->bind('pin.access', Access::class);
    }

    /**
     * 发布资源并注册权限校验。
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/access.php' => config_path('pin/access.php'),
        ], 'pin-access-config');

        $this->publishes(
            [__DIR__.'/../database/migrations' => database_path('migrations')],
            'pin-access-migrations'
        );

        Gate::before($this->authorizeAccess(...));
    }

    /**
     * 仅处理 access 能力，其他能力继续使用应用自身的 Gate 定义。
     *
     * @param  array<int, mixed>  $arguments
     */
    protected function authorizeAccess(Authenticatable $user, string $ability, array $arguments): ?bool
    {
        if ($ability !== Access::ABILITY || ! $user instanceof AccessUser) {
            return null;
        }

        if ($user->hasAllAccess()) {
            return true;
        }

        $code = $arguments[0] ?? null;

        if (! is_string($code) || $code === '') {
            return false;
        }

        // Gate::forUser() 可校验未登录的指定用户，不能依赖当前认证用户的门面实例。
        $access = $this->app->make('pin.access', ['user' => $user]);

        return in_array($code, $access->codes(), true);
    }
}
