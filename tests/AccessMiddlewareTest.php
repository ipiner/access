<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Pin\Access\Access as AccessManager;
use Pin\Access\Middleware\Access;
use Pin\Access\UnauthorizedException;

it('does not call authorize when access is disabled', function () {
    config(['pin.access.enabled' => false]);
    $request = Request::create('/test');
    $middleware = new Access();
    $next = fn ($req) => 'ok';
    Gate::shouldReceive('allows')->never();
    $response = $middleware->handle($request, $next);
    expect($response)->toBe('ok');
});

it('skips authorization for excluded request', function () {
    config(['pin.access.enabled' => true, 'pin.access.except' => ['health']]);
    Gate::shouldReceive('allows')->never();
    $middleware = new Access();

    $request = Request::create('/health');

    $response = $middleware->handle(
        $request,
        fn () => response('OK')
    );

    expect($response->getContent())->toBe('OK');
});

it('throws exception when gate denies', function () {
    config(['pin.access.enabled' => true]);
    $request = Request::create('/test');
    config(['pin.access.except' => 'admin/*']);
    $middleware = new Access();
    $next = fn () => 'ok';
    $middleware->handle($request, $next, 'perm');
})->throws(UnauthorizedException::class);

it('authorizes the route name when no explicit code is supplied', function () {
    config(['pin.access.enabled' => true]);
    $request = Request::create('/test');
    $request->setRouteResolver(fn () => (new Route('GET', '/test', []))->name('test.route'));
    Gate::shouldReceive('allows')->with(AccessManager::ABILITY, 'test.route')->once()->andReturnTrue();

    expect((new Access())->handle($request, fn () => 'ok'))->toBe('ok');
});

it('prefers an explicit code to the route name', function () {
    config(['pin.access.enabled' => true]);
    $request = Request::create('/test');
    $request->setRouteResolver(fn () => (new Route('GET', '/test', []))->name('test.route'));
    Gate::shouldReceive('allows')->with(AccessManager::ABILITY, 'explicit')->once()->andReturnTrue();

    expect((new Access())->handle($request, fn () => 'ok', 'explicit'))->toBe('ok');
});

it('denies requests without an access code or named route', function (bool $hasRoute) {
    config(['pin.access.enabled' => true]);
    $request = Request::create('/test');
    if ($hasRoute) {
        $request->setRouteResolver(fn () => new Route('GET', '/test', []));
    }
    Gate::shouldReceive('allows')->never();

    (new Access())->handle($request, fn () => 'ok');
})->with([true, false])->throws(UnauthorizedException::class);

it('skips authorization for excluded route names', function () {
    config(['pin.access.enabled' => true, 'pin.access.except' => ['health.*']]);
    $request = Request::create('/status');
    $request->setRouteResolver(fn () => (new Route('GET', '/status', []))->name('health.status'));
    Gate::shouldReceive('allows')->never();

    expect((new Access())->handle($request, fn () => 'ok'))->toBe('ok');
});
