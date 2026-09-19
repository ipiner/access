<?php

declare(strict_types=1);

namespace Pin\Access\Concerns;

/**
 * 提供用户访问数据解析能力。
 */
trait HasAccessData
{
    /**
     * 解析用户访问数据
     *
     * - 按菜单 ID 索引的序列化菜单，包含完整祖先链
     * - 权限码列表
     *
     * @return array{
     *    menus: array<int, array<string, mixed>>,
     *    codes: list<string>
     *  }
     */
    protected function resolveAccessData(): array
    {
        return $this->remember(function () {
            $accessibleMenus = $this->normalizeMenus($this->user->accessibleMenus());
            $hasAllAccess = $this->user->hasAllAccess();

            $menus = [];
            $codes = [];

            foreach ($accessibleMenus as $menu) {
                if (! $hasAllAccess) {
                    $codes[] = $menu->code;
                }

                if ($menu->isMenu()) {
                    $menus[$menu->id] = $menu->toArray();
                }
            }

            return [
                'menus' => $menus,
                'codes' => $codes,
            ];
        });
    }
}
