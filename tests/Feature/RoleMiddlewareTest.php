<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('middleware allows admin to access admin-only routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $admin);

    $middleware = new EnsureUserHasRole;
    $next = fn ($request) => response('OK');

    $response = $middleware->handle($request, $next, 'admin');

    expect($response->getContent())->toBe('OK');
});

test('middleware blocks regular user from accessing admin-only routes', function () {
    $user = User::factory()->create(['role' => 'user']);
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureUserHasRole;
    $next = fn ($request) => response('OK');

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Unauthorized action.');

    $middleware->handle($request, $next, 'admin');
});

test('middleware redirects guest to login', function () {
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => null);

    $middleware = new EnsureUserHasRole;
    $next = fn ($request) => response('OK');

    $response = $middleware->handle($request, $next, 'admin');

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toContain('login');
});
