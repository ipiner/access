<?php

declare(strict_types=1);

namespace Pin\Access\Attributes;

use Attribute;
use Pin\Route\Routable;

/**
 * 覆盖路由枚举 Case 自动推导出的 Access 权限码。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Access
{
    /**
     * @param  Routable|string|false|null  $value  Access 权限码
     */
    public function __construct(public Routable|string|false|null $value)
    {
    }
}
