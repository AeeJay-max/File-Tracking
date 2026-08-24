<?php

namespace Tests\Feature;

use App\Http\Middleware\CatchTokenMismatchMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class TokenExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_mismatch_middleware_logs_out_user_and_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $middleware = new CatchTokenMismatchMiddleware();
        $request = Request::create('/dashboard', 'POST');
        $session = $this->app['session']->driver();
        $session->start();
        $request->setLaravelSession($session);

        $response = $middleware->handle($request, function () {
            throw new TokenMismatchException('CSRF token mismatch');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('login'), $response->headers->get('Location'));
        $this->assertGuest();
    }

    public function test_json_token_mismatch_middleware_returns_419_json_response(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($user);

        $middleware = new CatchTokenMismatchMiddleware();
        $request = Request::create('/notifications/read-visible', 'POST');
        $request->headers->set('Accept', 'application/json');
        $session = $this->app['session']->driver();
        $session->start();
        $request->setLaravelSession($session);

        $response = $middleware->handle($request, function () {
            throw new TokenMismatchException('CSRF token mismatch');
        });

        $this->assertEquals(419, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'Your session or token has expired. Please log in again.', 'redirect' => route('login')]),
            $response->getContent()
        );
        $this->assertGuest();
    }
}
