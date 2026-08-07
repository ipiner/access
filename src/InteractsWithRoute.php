<?php

declare(strict_types=1);

namespace Pin\Access;

use Illuminate\Routing\Route;
use Pin\Access\Attributes\Access;
use Pin\Route\Routable;

/**
 * 组合路由枚举所需的属性读取、定义解析、注册和测试能力。
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
     * @param  string|Routable|null|false  $accessCode  访问权限码
     */
    public function register(
        callable|array|string $handler,
        string|array|null $middlewares = null,
        Routable|string|null|false $accessCode = null
    ): Route {
        $route = $this->__register($handler, $middlewares);

        if ($middlewares = $this->resolveAccessMiddleware($route, $accessCode)) {
            $route->middleware($middlewares);
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
            || in_array('auth', $route->excludedMiddleware())
        ) {
            return null;
        }

        $middleware = config('pin.access.middleware');

        $attr = $this->attribute(Access::class);
        if ($attr) {
            $accessCode = $attr->value;
        }

        return match (true) {
            $accessCode === false => null,
            $accessCode === null => $middleware,
            is_string($accessCode) => "{$middleware}:{$accessCode}",
            default => "{$middleware}:{$accessCode->name()}",
        };
    }
}
