<?php

declare(strict_types=1);

namespace Pin\Access\Contracts;

use Pin\Access\Models\Menu;

/**
 * 访问权限数据提供器
 */
interface AccessProvider
{
    /**
     * 获取当前用户拥有的权限码列表
     *
     * @return string[]
     */
    public function codes(): array;

    /**
     * 获取用户可访问菜单列表
     *
     * @return Menu[]
     */
    public function menus(): array;
}
