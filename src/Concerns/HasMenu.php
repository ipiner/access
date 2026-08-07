<?php

declare(strict_types=1);

namespace Pin\Access\Concerns;

use Illuminate\Support\Collection;
use Pin\Access\Contracts\AccessibleMenu;
use Pin\Access\Models\Menu;
use Pin\Support\Facades\Tree;

/**
 * 提供权限菜单的父级补齐、过滤和排序能力。
 *
 * @template TModel of Menu
 */
trait HasMenu
{
    /**
     * 查询菜单
     */
    protected function findMenu(int $id): Menu
    {
        $model = $this->modelModelClass();

        return $model::findOrFail($id);
    }

    /**
     * 根据菜单 path 补齐前端展示所需的祖先菜单。
     */
    protected function loadAncestorMenus(Collection $menus): Collection
    {
        $result = [];

        // 确保父级存在
        foreach ($menus as $item) {
            foreach ($item->paths() as $id) {
                if (! isset($menus[$id], $result[$id])) {
                    $result[$id] = $this->findMenu($id);
                }
            }
            $result[$item->id] = $item;
        }

        return collect(array_values(array_filter($result)));
    }

    /**
     * @return class-string<TModel>
     */
    protected function modelModelClass(): string
    {
        return config('pin.access.menu_model');
    }

    /**
     * 归一化权限菜单集合，确保父级链完整并过滤禁用菜单。
     */
    protected function normalizeMenus(Collection $menus): Collection
    {
        $menus = Tree::filter(
            $this->loadAncestorMenus($menus),
            fn (AccessibleMenu $menu) => ! $menu->isDisabled()
        );

        return Tree::sort($menus);
    }
}
