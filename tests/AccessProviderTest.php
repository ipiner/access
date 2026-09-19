<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Pin\Access\AccessProvider;
use Pin\Access\Models\Menu;

beforeEach(function () {
    config(['pin.access.cache_ttl' => 0]);
});

it('resolves a shared ancestor once and keeps supplied menus', function () {
    $parent = new Menu(['id' => 10, 'path' => '10']);
    $first = new Menu(['id' => 20, 'pid' => 10, 'path' => '10/20']);
    $second = new Menu(['id' => 30, 'pid' => 10, 'path' => '10/30']);
    $provider = Mockery::mock(AccessProvider::class, [accessUser()])->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $provider->shouldReceive('findMenu')->once()->with(10)->andReturn($parent);

    // 任意集合键不能被当作菜单 ID；路径还包含节点自身。
    $menus = $this->invoker($provider)->loadAncestorMenus(collect([10 => $first, 20 => $second]));

    expect($menus->keyBy('id')->all())->toBe([20 => $first, 30 => $second, 10 => $parent]);
});

it('does not reload an ancestor already supplied after its child', function () {
    $parent = new Menu(['id' => 10, 'path' => '10']);
    $child = new Menu(['id' => 20, 'pid' => 10, 'path' => '10/20']);
    $provider = Mockery::mock(AccessProvider::class, [accessUser()])->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $provider->shouldNotReceive('findMenu');

    $menus = $this->invoker($provider)->loadAncestorMenus(collect([$child, $parent]));

    expect($menus->keyBy('id')->all())->toBe([20 => $child, 10 => $parent]);
});

it('does not silently ignore a missing ancestor', function () {
    $provider = Mockery::mock(AccessProvider::class, [accessUser()])->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $provider->shouldReceive('findMenu')->with(10)->once()->andThrow(ModelNotFoundException::class);

    $this->invoker($provider)->loadAncestorMenus(collect([new Menu(['id' => 20, 'path' => '10/20'])]));
})->throws(ModelNotFoundException::class);

it('filters disabled ancestors and their descendants', function () {
    $user = accessUser(menus: [
        new Menu(['id' => 10, 'path' => '10', 'code' => 'parent', 'type' => Menu::MENU, 'enabled' => '0']),
        new Menu(['id' => 20, 'pid' => 10, 'path' => '10/20', 'code' => 'child', 'type' => Menu::MENU, 'enabled' => 1]),
    ]);
    $provider = new AccessProvider($user);

    expect($provider->codes())->toBe([])->and($provider->menus())->toBe([]);
});

it('sorts menus and keeps buttons only in access codes', function () {
    $user = accessUser(menus: [
        new Menu(['id' => 30, 'pid' => 0, 'path' => '30', 'sort' => 2, 'code' => 'last', 'type' => Menu::MENU]),
        new Menu(['id' => 20, 'pid' => 0, 'path' => '20', 'sort' => 1, 'code' => 'first', 'type' => Menu::MENU]),
        new Menu(['id' => 40, 'pid' => 20, 'path' => '20/40', 'code' => 'button', 'type' => Menu::BUTTON]),
    ]);
    $provider = new AccessProvider($user);

    expect($provider->codes())->toBe(['first', 'last', 'button'])
        ->and(array_keys($provider->menus()))->toBe([20, 30])
        ->and($provider->menus()[20]['code'])->toBe('first');
});

it('resolves an empty menu collection', function () {
    $provider = new AccessProvider(accessUser());

    expect($provider->codes())->toBe([])->and($provider->menus())->toBe([]);
});

it('supports both menu model extension methods', function () {
    $provider = new class(accessUser()) extends AccessProvider
    {
        protected function menuModelClass(): string
        {
            return 'LegacyMenu';
        }
    };

    expect($this->invoker($provider)->menuModelClass())->toBe('LegacyMenu');

    $provider = Mockery::mock(AccessProvider::class, [accessUser()])->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $provider->shouldReceive('menuModelClass')->once()->andReturn(Menu::class);

    // 验证查询使用新扩展点；保留模型原有的缺失节点异常。
    $this->invoker($provider)->findMenu(0);
})->throws(ModelNotFoundException::class);

it('normalizes enabled values without treating the locked enabled state as disabled', function ($enabled, bool $disabled) {
    expect((new Menu(['enabled' => $enabled]))->isDisabled())->toBe($disabled);
})->with([[0, true], ['0', true], [1, false], ['1', false], [2, false], ['2', false]]);
