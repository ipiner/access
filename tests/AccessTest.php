<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Pin\Access\Access;
use Pin\Access\AccessProvider;
use Pin\Access\Contracts\AccessUser;
use Pin\Access\Models\Menu;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\MenuFactory;
use Pin\Tests\Models\User;

uses(InteractsWithRedis::class);

it('skips non access ability', function () {
    expect(
        Gate::forUser(accessUser())->allows(uniqid())
    )->toBeFalse();
});

it('skips non access user', function () {
    expect(
        Gate::forUser(new User())->allows(Access::ABILITY)
    )->toBeFalse();
});

it('allows user with full access', function () {
    expect(
        Gate::forUser(accessUser('admin'))->allows(Access::ABILITY)
    )->toBeTrue();
});

it('allows user with matching access code', function () {
    $menu = MenuFactory::new()->create();
    $user = accessUser(null, [$menu]);
    $this->actingAs($user);
    expect(
        Gate::forUser($user)->allows(Access::ABILITY, $menu->code)
    )->toBeTrue();
});

it('resolves access data', function () {

    $user = accessUser();

    $menu = MenuFactory::new()->create(['code' => 'menu']);
    $child = MenuFactory::new()->create(['pid' => $menu->id, 'code' => 'child']);
    $button = MenuFactory::new()->create(['code' => 'button', 'type' => Menu::BUTTON]);

    $user->menus = collect([$child, $button]);

    $access = new Access($user);
    $codes = $access->codes();
    $menus = $access->menus();

    // cache
    expect(Cache::get('auth-access:'.$user->id))->toHaveKeys(['menus', 'codes']);
    AccessProvider::flushAccess($user);
    expect(Cache::get('auth-access:'.$user->id))->toBeNull()
        ->and($codes)->toBe([
            $menu->code,
            $button->code,
            $child->code,
        ])
        ->and($menus)->toHaveCount(2)
        ->toHaveKey($menu->id)
        ->toHaveKey($child->id)
        ->not->toHaveKey($button->id);
    // full access user
    $access = new Access(accessUser('admin', [$child, $button]));
    expect($access->codes())->toBe([]);
});

function accessUser(?string $username = null, array $menus = []): User
{
    $user = new class(['id' => crc32(uniqid()), 'username' => $username ?? uniqid()]) extends User implements AccessUser
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
    };

    $user->menus = collect($menus);

    return $user;
}
