<?php

declare(strict_types=1);

namespace Pin\Access\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Pin\Access\Models\Menu;

/**
 * 访问控制用户接口
 */
interface AccessUser extends Authenticatable
{
    /**
     * 是否拥有全部权限
     */
    public function hasAllAccess(): bool;

    /**
     * 获取用户可访问的菜单集合
     *
     * @return Collection<Menu>
     */
    public function accessibleMenus(): Collection;
}
