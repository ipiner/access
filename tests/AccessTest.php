<?php

declare(strict_types=1);

use Illuminate\Contracts\Cache\Repository;
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
    $cacheKey = $this->invoker(AccessProvider::class)->cacheKey($user);
    expect(Cache::get($cacheKey))->toHaveKeys(['menus', 'codes']);
    AccessProvider::flushAccess($user);
    expect(Cache::get($cacheKey))->toBeNull()
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

it('denies missing or invalid access codes', function (array $arguments) {
    expect(Gate::forUser(accessUser())->allows(Access::ABILITY, $arguments))->toBeFalse();
})->with([
    'missing' => [[]],
    'null' => [[null]],
    'integer' => [[123]],
    'empty' => [['']],
]);

it('compares access codes strictly', function () {
    $user = accessUser(menus: [new Menu(['id' => 1, 'code' => '01', 'type' => Menu::BUTTON])]);

    expect(Gate::forUser($user)->allows(Access::ABILITY, '1'))->toBeFalse()
        ->and(Gate::forUser($user)->allows(Access::ABILITY, '01'))->toBeTrue();
});

it('does not load menus when listing full access codes', function () {
    $user = Mockery::mock(AccessUser::class);
    $user->shouldReceive('hasAllAccess')->once()->andReturnTrue();
    $user->shouldNotReceive('accessibleMenus');

    expect((new Access($user))->codes())->toBe([]);
});

it('resolves custom provider dependencies through the container', function () {
    config(['pin.access.access_provider' => InjectableAccessProvider::class]);
    $user = accessUser();
    $access = new Access($user);

    expect($access->provider)->toBeInstanceOf(InjectableAccessProvider::class)
        ->and($access->provider->user)->toBe($user)
        ->and($access->provider->cache)->toBe(app('cache.store'));
});

it('reuses access for the same user and creates it for another user', function () {
    $user = accessUser();
    $otherUser = accessUser();
    $access = new Access($user);

    expect($access->forUser($user))->toBe($access)
        ->and($access->forUser($otherUser)->provider->user)->toBe($otherUser);
});

class InjectableAccessProvider extends AccessProvider
{
    public function __construct(AccessUser $user, public Repository $cache)
    {
        parent::__construct($user);
    }
}
