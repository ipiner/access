<?php

declare(strict_types=1);

namespace Pin\Access\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Pin\Access\Access as AccessManager;
use Pin\Access\UnauthorizedException;

/**
 * 权限访问中间件。
 */
class Access
{
    /**
     * @param  string|null  $code  权限码，默认使用路由名称
     *
     * @throws UnauthorizedException
     */
    public function handle(Request $request, Closure $next, ?string $code = null): mixed
    {
        if ($this->shouldRun($request)) {
            $code ??= $request->route()?->getName();

            // 未命名路由必须显式提供权限码，避免产生类型错误或意外放行。
            if ($code === null || $code === '') {
                throw new UnauthorizedException($code ?? '');
            }

            $this->authorize($code);
        }

        return $next($request);
    }

    /**
     * 执行权限校验
     *
     * @throws UnauthorizedException
     */
    protected function authorize(string $code): void
    {
        if (! Gate::allows(AccessManager::ABILITY, $code)) {
            throw new UnauthorizedException($code);
        }
    }

    /**
     * 是否需要执行权限校验
     */
    protected function shouldRun(Request $request): bool
    {
        return config('pin.access.enabled', false)
            && ! $request->isRequest(config('pin.access.except', []));
    }
}
