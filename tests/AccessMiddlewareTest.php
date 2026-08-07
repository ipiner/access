<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Pin\Access\Middleware\Access;
use Pin\Access\UnauthorizedException;

it('does not call authorize when access is disabled', function () {
    $request = Request::create('/test');
    $middleware = new Access();
    $next = fn ($req) => 'ok';
    // fake route name
    $request->setRouteResolver(function () {
        return new class
        {
            public function getName()
            {
                return 'test.route';
            }
        };
    });
    $response = $middleware->handle($request, $next);
    expect($response)->toBe('ok');
});

it('skips authorization for excluded request', function () {
    config(['pin.access.except' => ['health']]);
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
