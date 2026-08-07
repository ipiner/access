<?php

use Pin\Access\AccessProvider;
use Pin\Access\Middleware\Access;
use Pin\Access\Models\Menu;

return [
    'middleware' => Access::class,
    'access_provider' => AccessProvider::class,
    'menu_model' => Menu::class,
    'enabled' => env('APP_ENV') !== 'testing',
    'except' => [],
];
