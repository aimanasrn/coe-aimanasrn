<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class JwtAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.secret' => str_repeat('test-secret-', 6),
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        $this->artisan('migrate', [
            '--path' => 'database/migrations/0001_01_01_000000_create_users_table.php',
            '--no-interaction' => true,
        ])->assertSuccessful();
        $this->artisan('migrate', [
            '--path' => 'database/migrations/2026_10_19_042034_add_role_and_token_version_to_users_table.php',
            '--no-interaction' => true,
        ])->assertSuccessful();
    }

    public function test_valid_credentials_issue_a_token_that_identifies_the_user(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
            ->assertJsonPath('token_type', 'bearer');

        $this->resetAuthentication();

        $this->withToken($response->json('access_token'))->getJson('/api/auth/me')
            ->assertOk()->assertJsonPath('data.id', $user->id)
            ->assertJsonMissingPath('data.password')->assertJsonMissingPath('data.remember_token');
    }

    public function test_invalid_credentials_do_not_issue_a_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertUnauthorized()->assertJsonMissingPath('access_token');
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_missing_and_malformed_tokens_are_rejected(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
        $this->withToken('invalid-token')->getJson('/api/auth/me')->assertUnauthorized();
        $this->postJson('/api/auth/refresh')->assertUnauthorized();
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }

    public function test_logout_revokes_the_token(): void
    {
        $token = Auth::guard('api')->login(User::factory()->create());
        $this->resetAuthentication();

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->resetAuthentication();

        $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_refresh_replaces_the_token_and_revokes_the_previous_token(): void
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($user);
        $this->resetAuthentication();

        $response = $this->withToken($token)->postJson('/api/auth/refresh')->assertOk();
        $newToken = $response->json('access_token');
        $this->assertNotSame($token, $newToken);
        $this->resetAuthentication();

        $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();
        $this->resetAuthentication();

        $this->withToken($newToken)->getJson('/api/auth/me')
            ->assertOk()->assertJsonPath('data.id', $user->id);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', ['email' => 'missing@example.com', 'password' => 'wrong'])
                ->assertUnauthorized();
        }

        $this->postJson('/api/auth/login', ['email' => 'missing@example.com', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    private function resetAuthentication(): void
    {
        Auth::forgetGuards();
        $this->app->forgetScopedInstances();
    }
}
