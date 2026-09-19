<?php

declare(strict_types=1);

namespace Pin\Tests\Models;

use Illuminate\Support\Collection;
use Pin\Access\Contracts\AccessUser as AccessUserContract;

class AccessUser extends User implements AccessUserContract
{
    public Collection $menus;

    public function hasAllAccess(): bool
    {
        return $this->username === 'admin';
    }

    public function accessibleMenus(): Collection
    {
        return $this->menus ?? collect();
    }
}
