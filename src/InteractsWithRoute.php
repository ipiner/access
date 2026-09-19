<?php

declare(strict_types=1);

namespace Pin\Access;

use Illuminate\Routing\Route;
use Pin\Access\Attributes\Access;
use Pin\Route\Routable;

/**
 * 在路由枚举注册时追加权限中间件。
 */
trait InteractsWithRoute
{
    use \Pin\Route\InteractsWithRoute {
        register as __register;
    }

    /**
     * 注册路由
     *
     * @param  callable|array|string  $handler  路由处理器
     * @param  string|string[]|null  $middlewares  附加中间件
     * @param  string|Routable|null|false  $accessCode  null 使用路由名称，false 跳过校验；Access 属性优先
     */
    public function register(
        callable|array|string $handler,
        string|array|null $middlewares = null,
        Routable|string|null|false $accessCode = null
    ): Route {
        $route = $this->__register($handler, $middlewares);

        $accessMiddleware = $this->resolveAccessMiddleware($route, $accessCode);
        if ($accessMiddleware !== null) {
            $route->middleware($accessMiddleware);
        }

        return $route;
    }

    /**
     * 解析访问权限中间件。
     */
    protected function resolveAccessMiddleware(
        Route $route,
        Routable|string|false|null $accessCode
    ): ?string {
        if (
            ! config('pin.access.enabled')
            || in_array('auth', $route->excludedMiddleware(), true)
        ) {
            return null;
        }

        $middleware = config('pin.access.middleware');

        $attribute = $this->attribute(Access::class);
        if ($attribute !== null) {
            $accessCode = $attribute->value;
        }

        return match (true) {
            $accessCode === false => null,
            $accessCode === null => $middleware,
            is_string($accessCode) => "{$middleware}:{$accessCode}",
            default => "{$middleware}:{$accessCode->name()}",
        };
    }
}
