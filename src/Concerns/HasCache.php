<?php

declare(strict_types=1);

namespace Pin\Access\Concerns;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Pin\Support\Facades\RuntimeCache;

/**
 * 为权限解析结果提供进程缓存和应用缓存。
 */
trait HasCache
{
    /**
     * 清理指定用户的权限缓存。
     */
    public static function flushAccess(Authenticatable $user): void
    {
        if ($user->getAuthIdentifier() === null) {
            return;
        }

        $key = static::cacheKey($user);

        RuntimeCache::delete($key);
        Cache::forget($key);
    }

    /**
     * 使用用户认证标识生成权限缓存键。
     */
    protected static function cacheKey(Authenticatable $user): string
    {
        return 'auth-access:'.$user->getAuthIdentifier();
    }

    /**
     * 读取或生成权限缓存。
     */
    protected function remember(Closure $callback): array
    {
        $ttl = (int) config('pin.access.cache_ttl', 86400);

        // 未分配认证标识的用户不能共享缓存。
        if ($ttl <= 0 || $this->user->getAuthIdentifier() === null) {
            return $callback();
        }

        $key = static::cacheKey($this->user);

        return RuntimeCache::remember($key, function () use ($key, $ttl, $callback) {
            return Cache::remember($key, $ttl, $callback);
        }, $ttl);
    }
}
