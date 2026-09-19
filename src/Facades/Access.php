<?php

declare(strict_types=1);

namespace Pin\Access\Facades;

use Illuminate\Support\Facades\Facade;
use Pin\Access\Contracts\AccessUser;

/**
 * @method static \Pin\Access\Access forUser(AccessUser $user)
 * @method static list<string> codes()
 * @method static array<int, array<string, mixed>> menus()
 *
 * @see \Pin\Access\Access
 */
class Access extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'pin.access';
    }
}
