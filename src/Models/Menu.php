<?php

declare(strict_types=1);

namespace Pin\Access\Models;

use Pin\Access\Contracts\AccessibleMenu;
use Pin\Models\Concerns\CacheAll;
use Pin\Modules\Log\Models\Concerns\HasOperationLog;
use Pin\Tree\TreeModel;

/**
 * 菜单树模型。
 *
 * @property string $code 菜单或按钮唯一标识
 * @property string $type 菜单类型
 * @property int $enabled 是否启用
 */
class Menu extends TreeModel implements AccessibleMenu
{
    use CacheAll, HasOperationLog;

    /**
     * 禁用
     */
    public const int DISABLED = 0;

    /**
     * 启用
     */
    public const int ENABLED = 1;

    /**
     * 类型：菜单
     */
    public const string MENU = 'menu';

    /**
     * 类型：按钮
     */
    public const string BUTTON = 'button';

    /**
     * 是否禁用
     */
    public function isDisabled(): bool
    {
        return $this->enabled === static::DISABLED;
    }

    /**
     * 是否菜单类型
     */
    public function isMenu(): bool
    {
        return $this->type == static::MENU;
    }

    /**
     * 用于日志记录的名称字段
     */
    public function subjectNameColumn(): string
    {
        return 'name';
    }
}
