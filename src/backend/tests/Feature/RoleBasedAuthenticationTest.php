<?php

namespace Tests\Feature;

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleBasedAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'jwt.secret' => str_repeat('test-secret-', 6),
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        foreach (['0001_01_01_000000_create_users_table.php', '2026_10_19_042034_add_role_and_token_version_to_users_table.php'] as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.$migration, '--no-interaction' => true])->assertSuccessful();
        }
    }

    public function test_registration_creates_a_customer_and_returns_a_working_token(): void
    {
        $response = $this->postJson('/api/auth/register', $this->registration())
            ->assertCreated()->assertJsonPath('data.role', 'customer')->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonMissingPath('data.password')->assertJsonMissingPath('data.token_version');
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('customer-password', $user->password));

        $this->token($response->json('access_token'));
        $this->getJson('/api/auth/profile')->assertOk()->assertJsonPath('data.id', $user->id);
    }

    public function test_registration_rejects_privileged_fields_duplicate_emails_and_unconfirmed_passwords(): void
    {
        $this->postJson('/api/auth/register', [...$this->registration(), 'role' => 'admin', 'token_version' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors(['role', 'token_version']);
        $this->assertDatabaseCount('users', 0);
        User::factory()->create(['email' => 'new@example.com']);
        $this->postJson('/api/auth/register', [...$this->registration(), 'password_confirmation' => 'different'])
            ->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function catalogEndpoints(): array
    {
        return [
            'dashboard' => ['GET', '/api/dashboard'],
            'products' => ['GET', '/api/products'],
            'create product' => ['POST', '/api/products'],
            'categories' => ['GET', '/api/categories'],
            'create category' => ['POST', '/api/categories'],
        ];
    }

    #[DataProvider('catalogEndpoints')]
    public function test_customers_cannot_access_catalog_management(string $method, string $path): void
    {
        $this->token(Auth::guard('api')->login(User::factory()->create()));
        $this->json($method, $path)->assertForbidden();
    }

    public function test_authorization_uses_the_current_database_role_instead_of_stale_token_claims(): void
    {
        $user = User::factory()->admin()->create();
        $token = Auth::guard('api')->login($user);
        $user->role = UserRole::Customer;
        $user->save();
        $this->token($token);

        $this->getJson('/api/products')->assertForbidden();
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.role', 'customer');
    }

    public function test_profile_updates_only_the_authenticated_user_and_cannot_promote_roles(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['name' => 'Other customer']);
        $this->token(Auth::guard('api')->login($user));
        $this->patchJson('/api/auth/profile', ['name' => 'Updated name', 'id' => $other->id])
            ->assertOk()->assertJsonPath('data.id', $user->id)->assertJsonPath('data.name', 'Updated name');
        $this->assertSame('Other customer', $other->fresh()->name);
        $this->assertSame('Updated name', $user->fresh()->name);

        $this->token(Auth::guard('api')->login($user->fresh()));
        $this->patchJson('/api/auth/profile', ['role' => 'admin'])->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->assertSame(UserRole::Customer, $user->fresh()->role);
    }

    public function test_email_change_requires_the_current_password_and_preserves_uniqueness(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);
        $this->token(Auth::guard('api')->login($user));
        $this->patchJson('/api/auth/profile', ['email' => 'changed@example.com'])
            ->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->patchJson('/api/auth/profile', ['email' => 'changed@example.com', 'current_password' => 'wrong'])
            ->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->patchJson('/api/auth/profile', ['email' => 'taken@example.com', 'current_password' => 'password'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->patchJson('/api/auth/profile', ['email' => 'CHANGED@example.com', 'current_password' => 'password'])
            ->assertOk()->assertJsonPath('data.email', 'changed@example.com');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_password_change_invalidates_every_old_session_and_issues_a_replacement(): void
    {
        $user = User::factory()->create();
        $firstToken = Auth::guard('api')->login($user);
        $this->resetAuthentication();
        $secondToken = Auth::guard('api')->login($user);
        $this->token($firstToken);

        $response = $this->patchJson('/api/auth/profile', [
            'current_password' => 'password', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
        ])->assertOk();
        $this->token($firstToken);
        $this->getJson('/api/auth/me')->assertUnauthorized();
        $this->token($secondToken);
        $this->getJson('/api/auth/me')->assertUnauthorized();
        $this->postJson('/api/auth/refresh')->assertUnauthorized();
        $this->token($response->json('access_token'));
        $this->getJson('/api/auth/me')->assertOk();
        $refreshed = $this->postJson('/api/auth/refresh')->assertOk();
        $this->token($refreshed->json('access_token'));
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.role', 'customer');
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_password_change_rejects_wrong_current_password_and_unconfirmed_new_password(): void
    {
        $user = User::factory()->create();
        $this->token(Auth::guard('api')->login($user));
        $this->patchJson('/api/auth/profile', [
            'current_password' => 'wrong', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->patchJson('/api/auth/profile', [
            'current_password' => 'password', 'password' => 'new-password-123', 'password_confirmation' => 'different',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertSame(0, $user->fresh()->token_version);
    }

    public function test_expired_and_tampered_tokens_are_rejected(): void
    {
        $token = Auth::guard('api')->setTTL(1)->login(User::factory()->create());
        $this->travel(2)->minutes();
        $this->token($token);
        $this->getJson('/api/auth/me')->assertUnauthorized();
        $this->travelBack();

        $parts = explode('.', $token);
        $claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $claims['role'] = 'admin';
        $parts[1] = rtrim(strtr(base64_encode(json_encode($claims)), '+/', '-_'), '=');
        $this->token(implode('.', $parts));
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_guests_cannot_read_or_update_profiles(): void
    {
        $this->getJson('/api/auth/profile')->assertUnauthorized();
        $this->patchJson('/api/auth/profile', ['name' => 'Changed'])->assertUnauthorized();
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/register', [])->assertUnprocessable();
        }
        $this->postJson('/api/auth/register', [])->assertStatus(429);
    }

    /**
     * @return array<string, string>
     */
    private function registration(): array
    {
        return ['name' => 'New customer', 'email' => 'NEW@example.com', 'password' => 'customer-password', 'password_confirmation' => 'customer-password'];
    }

    private function token(string $token): void
    {
        $this->resetAuthentication();
        $this->withToken($token);
    }

    private function resetAuthentication(): void
    {
        Auth::forgetGuards();
        $this->app->forgetScopedInstances();
    }
}
