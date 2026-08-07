<?php

declare(strict_types=1);

namespace Pin\Access;

use Pin\Access\Contracts\AccessUser;
use Pin\Access\Models\Menu;

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
     * @return string[]
     */
    public function codes(): array
    {
        return $this->resolveAccessData()['codes'];
    }

    /**
     * 获取用户可访问菜单列表
     *
     * @return Menu[]
     */
    public function menus(): array
    {
        return $this->resolveAccessData()['menus'];
    }
}
