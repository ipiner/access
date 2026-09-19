<?php

declare(strict_types=1);

namespace Pin\Access;

use Pin\Access\Contracts\AccessUser;

/**
 * 用户权限数据提供器。
 */
class AccessProvider implements Contracts\AccessProvider
{
    use Concerns\HasAccessData,
        Concerns\HasCache,
        Concerns\HasMenu;

    public function __construct(public AccessUser $user)
    {
    }

    /**
     * 获取用户拥有的权限码列表
     *
     * 超级用户/管理员返回空数组，表示拥有全部权限
     *
     * @return list<string>
     */
    public function codes(): array
    {
        return $this->user->hasAllAccess() ? [] : $this->resolveAccessData()['codes'];
    }

    /**
     * 获取按菜单 ID 索引的序列化菜单，不包含按钮。
     *
     * @return array<int, array<string, mixed>>
     */
    public function menus(): array
    {
        return $this->resolveAccessData()['menus'];
    }
}
