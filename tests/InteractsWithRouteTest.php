<?php

declare(strict_types=1);

use Pin\Access\Middleware\Access;
use Pin\Route\Routable;
use Pin\Tests\UserRoute;

describe('resolves access middleware', function () {
    it('returns null if enabled is disabled', function () {
        $route = UserRoute::List->register([]);
        $invoker = $this->invoker(UserRoute::List);
        expect($invoker->resolveAccessMiddleware($route, 'some code'))->toBeNull();
    });

    it('returns null if accessCode is false', function () {
        config(['pin.access.enabled' => true]);
        $route = UserRoute::List->register([]);
        $invoker = $this->invoker(UserRoute::List);
        expect($invoker->resolveAccessMiddleware($route, false))->toBeNull();
    });

    it('returns null if route excludes auth', function () {
        config(['pin.access.enabled' => true]);
        $route = UserRoute::List->register([])->withoutMiddleware('auth');
        $invoker = $this->invoker(UserRoute::List);
        expect($invoker->resolveAccessMiddleware($route, 'some code'))->toBeNull();
    });

    it('returns as expected', function ($code, $expected) {
        config(['pin.access.enabled' => true]);
        $route = UserRoute::List->register([]);
        $invoker = $this->invoker($code instanceof Routable ? $code : UserRoute::List);
        expect($invoker->resolveAccessMiddleware($route, $code))->toBe($expected);
    })->with([
        [null, Access::class],
        ['users', Access::class.':'.'users'],
        [UserRoute::List, Access::class.':'.'users'],
        [UserRoute::Export, Access::class.':'.'users'],
        [UserRoute::PublicList, null],
        [UserRoute::Detail, Access::class],
        [UserRoute::Summary, Access::class.':users'],
    ]);
});

it('registers access middleware alongside existing middleware', function () {
    config(['pin.access.enabled' => true]);

    $route = UserRoute::List->register([], ['auth'], 'custom');

    expect($route->middleware())->toBe(['auth', Access::class.':custom']);
});

it('lets access attributes override registration arguments including false and null', function () {
    config(['pin.access.enabled' => true]);

    expect(UserRoute::Export->register([], accessCode: false)->middleware())->toContain(Access::class.':users')
        ->and(UserRoute::PublicList->register([], accessCode: 'custom')->middleware())->toBe([])
        ->and(UserRoute::Detail->register([], accessCode: 'custom')->middleware())->toContain(Access::class);
});
