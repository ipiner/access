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
     * 通过模型缓存查询缺失的祖先菜单。
     */
    protected function findMenu(int $id): Menu
    {
        $model = $this->menuModelClass();

        return $model::findOrFail($id);
    }

    /**
     * 根据菜单 path 补齐前端展示所需的祖先菜单。
     *
     * @param  Collection<array-key, TModel>  $menus
     * @return Collection<int, TModel>
     */
    protected function loadAncestorMenus(Collection $menus): Collection
    {
        $menusById = $menus->keyBy('id')->all();

        // 先索引已有节点，避免重复读取共享祖先或覆盖调用方传入的模型。
        foreach ($menus as $menu) {
            foreach ($menu->paths() as $id) {
                if (! isset($menusById[$id])) {
                    $menusById[$id] = $this->findMenu($id);
                }
            }
        }

        return collect(array_values($menusById));
    }

    /**
     * @return class-string<TModel>
     */
    protected function menuModelClass(): string
    {
        return config('pin.access.menu_model');
    }

    /**
     * 归一化权限菜单集合，确保父级链完整并过滤禁用菜单。
     *
     * @param  Collection<array-key, TModel>  $menus
     * @return Collection<int, TModel>
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
