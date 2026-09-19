<?php

use Pin\Access\AccessProvider;
use Pin\Access\Middleware\Access;
use Pin\Access\Models\Menu;

return [
    'middleware' => Access::class,
    'access_provider' => AccessProvider::class,
    'menu_model' => Menu::class,
    // 权限数据的进程缓存和应用缓存有效期（秒）；设为 0 可禁用。
    'cache_ttl' => 86400,
    'enabled' => env('APP_ENV') !== 'testing',
    'except' => [],
];
