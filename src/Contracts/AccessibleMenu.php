<?php

declare(strict_types=1);

namespace Pin\Access\Contracts;

/**
 * 访问菜单模型接口
 */
interface AccessibleMenu
{
    /**
     * 是否禁用
     */
    public function isDisabled(): bool;

    /**
     * 是否菜单类型
     */
    public function isMenu(): bool;
}
