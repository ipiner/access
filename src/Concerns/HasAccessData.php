<?php

declare(strict_types=1);

namespace Pin\Access\Concerns;

use Pin\Access\Models\Menu;

/**
 * 提供用户访问数据解析能力。
 */
trait HasAccessData
{
    /**
     * 解析用户访问数据
     *
     * - 前端可用的菜单树数据
     * - 权限码列表
     *
     * @return array{
     *    menus: array<int, Menu>,
     *    codes: string[]
     *  }
     */
    protected function resolveAccessData(): array
    {
        return $this->remember(function () {
            /** @var Menu[] $models */
            $models = $this->normalizeMenus($this->user->accessibleMenus());

            $menus = [];
            $codes = [];

            foreach ($models as $menu) {
                $codes[] = $menu->code;

                if ($menu->isMenu()) {
                    $menus[$menu->id] = $menu->toArray();
                }
            }

            return [
                'menus' => $menus,
                'codes' => $this->user->hasAllAccess() ? [] : $codes,
            ];
        });
    }
}
