<?php

declare(strict_types=1);

namespace Pin\Tests;

use Pin\Access\Attributes\Access;
use Pin\Access\InteractsWithRoute;
use Pin\Route\Routable;

enum UserRoute: string implements Routable
{
    use InteractsWithRoute;

    case List = 'GET:/api/users';

    #[Access('users')]
    case Export = 'GET:/api/users/export';
}
