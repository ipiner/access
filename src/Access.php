<?php

declare(strict_types=1);

namespace Pin\Access;

use Illuminate\Contracts\Auth\Authenticatable;
use Pin\Access\Contracts\AccessProvider;
use Pin\Access\Contracts\AccessUser;

/**
 * 当前用户的权限访问门面。
 *
 * @mixin AccessProvider
 */
class Access
{
    /**
     * Gate 使用的权限能力名称
     */
    public const string ABILITY = 'access';

    /**
     * 底层权限提供器实例
     */
    public protected(set) AccessProvider $provider;

    /**
     * 构造函数
     *
     * @param  Authenticatable  $user  当前用户实例
     */
    public function __construct(protected Authenticatable $user)
    {
        $class = config('pin.access.access_provider');
        $this->provider = new $class($user);
    }

    /**
     * 魔术方法代理到底层 AccessProvider
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->provider->{$method}(...$parameters);
    }

    /**
     * 为指定用户创建权限访问实例。
     */
    public function forUser(AccessUser $user): static
    {
        return $user === $this->user ? $this : new static($user);
    }
}
