<?php

declare(strict_types=1);

namespace Pin\Access\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Pin\Access\UnauthorizedException;

/**
 * 权限访问中间件。
 */
class Access
{
    /**
     * @param string|null code 权限码，默认使用路由名称
     *
     * @throws UnauthorizedException
     */
    public function handle(Request $request, Closure $next, ?string $code = null): mixed
    {
        if ($this->shouldRun($request)) {
            $this->authorize($code ?? $request->route()->getName());
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
        if (! Gate::allows(\Pin\Access\Access::ABILITY, $code)) {
            throw new UnauthorizedException($code);
        }
    }

    /**
     * 是否需要执行权限校验
     */
    protected function shouldRun(Request $request): bool
    {
        return config('pin.access.enabled')
            && ! $request->isRequest(config('pin.access.except'));
    }
}
