<?php

declare(strict_types=1);

namespace Pin\Access\Contracts;

/**
 * 访问权限数据提供器
 */
interface AccessProvider
{
    /**
     * 获取当前用户拥有的权限码列表
     *
     * 拥有全部权限的用户返回空数组，由 hasAllAccess() 判断授权。
     *
     * @return list<string>
     */
    public function codes(): array;

    /**
     * 获取按菜单 ID 索引的序列化菜单，不包含按钮。
     *
     * @return array<int, array<string, mixed>>
     */
    public function menus(): array;
}
