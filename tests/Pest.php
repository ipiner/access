<?php

declare(strict_types=1);

use Pin\Tests\Models\AccessUser;
use Pin\Tests\TestCase;

pest()->extend(TestCase::class)->in(__DIR__);

function accessUser(?string $username = null, array $menus = []): AccessUser
{
    $user = new AccessUser(['id' => crc32(uniqid()), 'username' => $username ?? uniqid()]);
    $user->menus = collect($menus);

    return $user;
}
