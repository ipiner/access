<?php

declare(strict_types=1);

namespace Pin\Access\Facades;

use Illuminate\Support\Facades\Facade;
use Pin\Access\Contracts\AccessUser;
use Pin\Access\Models\Menu;

/**
 * @method static \Pin\Access\Access forUser(AccessUser $user)
 * @method static string[] codes()
 * @method static Menu[] menus()
 *
 * @see \Pin\Access\Access
 */
class Access extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.access';
    }
}
