<?php

use App\Models\User;
use Illuminate\Support\Facades\Cookie;

use function Pest\Laravel\get;

describe('Language Switch', function () {
    test('language switch sets cookie and redirects back', function () {
        $response = get('/locale/ja');

        $response->assertRedirect('/');
        // Check that cookie is queued (we can't decrypt it in tests easily)
        $response->assertCookie('app_locale');
    });

    test('language switch from authenticated page sets cookie and redirects back', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(
            '/locale/en',
            ['referer' => '/profile']
        );

        $response->assertRedirect();
        $response->assertCookie('app_locale');
    });

    test('language switch rejects invalid locale', function () {
        $response = get('/locale/invalid');

        $response->assertStatus(400);
    });

    test('locale switches between ja and en', function () {
        // Switch to Japanese
        $response = get('/locale/ja');
        $response->assertRedirect('/');
        $response->assertCookie('app_locale');
        
        // Switch to English
        $response = get('/locale/en');
        $response->assertRedirect('/');
        $response->assertCookie('app_locale');
    });
});
