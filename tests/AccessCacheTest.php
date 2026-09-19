<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Pin\Access\AccessProvider;
use Pin\Access\Contracts\AccessUser;
use Pin\Access\Models\Menu;
use Pin\Support\Facades\RuntimeCache;

beforeEach(function () {
    config(['cache.default' => 'array']);
    RuntimeCache::flush();
});

afterEach(function () {
    RuntimeCache::flush();
});

it('reuses cached data across providers and flushes both cache layers', function () {
    $user = accessUser(menus: [new Menu(['id' => 1, 'code' => 'before', 'type' => Menu::MENU])]);
    $provider = new AccessProvider($user);
    $cacheKey = $this->invoker($provider)->cacheKey($user);

    expect($cacheKey)->toBe('auth-access:'.$user->getAuthIdentifier())
        ->and($provider->codes())->toBe(['before']);
    $user->menus = collect();
    expect((new AccessProvider($user))->codes())->toBe(['before']);

    RuntimeCache::delete($cacheKey);
    expect($provider->codes())->toBe(['before']);

    AccessProvider::flushAccess($user);
    expect(Cache::get($cacheKey))->toBeNull()
        ->and(RuntimeCache::get($cacheKey))->toBeNull()
        ->and($provider->codes())->toBe([]);
});

it('shares cached access for the same identifier across user classes', function () {
    $firstUser = accessUser(menus: [new Menu(['id' => 1, 'code' => 'first'])]);
    $secondUser = new class extends Pin\Tests\Models\AccessUser
    {
    };
    $secondUser->id = $firstUser->id;
    $secondUser->menus = collect([new Menu(['id' => 2, 'code' => 'second'])]);

    expect((new AccessProvider($firstUser))->codes())->toBe(['first'])
        ->and((new AccessProvider($secondUser))->codes())->toBe(['first']);
});

it('shares and flushes cached access across provider classes', function () {
    $user = accessUser(menus: [new Menu(['id' => 1, 'code' => 'first'])]);
    expect((new AccessProvider($user))->codes())->toBe(['first']);

    $user->menus = collect([new Menu(['id' => 2, 'code' => 'second'])]);
    $provider = new class($user) extends AccessProvider
    {
    };

    expect($provider->codes())->toBe(['first']);

    $provider::flushAccess($user);
    expect((new AccessProvider($user))->codes())->toBe(['second']);
});

it('uses the authentication identifier without requiring an id property', function () {
    $user = new class(['uuid' => 'user-uuid']) extends GenericUser implements AccessUser
    {
        public function getAuthIdentifier()
        {
            return $this->uuid;
        }

        public function hasAllAccess(): bool
        {
            return false;
        }

        public function accessibleMenus(): Collection
        {
            return collect();
        }
    };
    $provider = new AccessProvider($user);
    $cacheKey = $this->invoker($provider)->cacheKey($user);

    expect($cacheKey)->toBe('auth-access:user-uuid')
        ->and($provider->codes())->toBe([])
        ->and(Cache::get($cacheKey))->toBe(['menus' => [], 'codes' => []]);

    AccessProvider::flushAccess($user);
    expect(Cache::get($cacheKey))->toBeNull();
});

it('does not cache users without an authentication identifier', function () {
    $user = accessUser(menus: [new Menu(['id' => 1, 'code' => 'before'])]);
    $user->id = null;
    $provider = new AccessProvider($user);

    expect($provider->codes())->toBe(['before']);
    $user->menus = collect();
    expect($provider->codes())->toBe([]);

    AccessProvider::flushAccess($user);
});

it('expires both cache layers using the configured lifetime', function () {
    config(['pin.access.cache_ttl' => 2]);
    $user = accessUser(menus: [new Menu(['id' => 1, 'code' => 'before'])]);
    $provider = new AccessProvider($user);

    expect($provider->codes())->toBe(['before']);
    $user->menus = collect();
    $this->travel(3)->seconds();

    expect($provider->codes())->toBe([]);
});

it('bypasses both cache layers when caching is disabled', function () {
    $user = accessUser(menus: [new Menu(['id' => 1, 'code' => 'before'])]);
    $provider = new AccessProvider($user);

    expect($provider->codes())->toBe(['before']);
    config(['pin.access.cache_ttl' => 0]);
    $user->menus = collect();

    expect($provider->codes())->toBe([]);
});
