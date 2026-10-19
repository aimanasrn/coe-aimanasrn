<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CartTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['jwt.secret' => str_repeat('cart-test-secret-', 5)]);
    }

    public function test_cart_adds_merges_updates_and_calculates_exact_current_prices(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => '0.10', 'stock_quantity' => 10]);
        $this->authenticate($user);
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2, 'price' => '0.01'])
            ->assertCreated()->assertJsonPath('data.subtotal', '0.20')->assertJsonPath('data.item_count', 2);
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertCreated()->assertJsonPath('data.items.0.quantity', 3)->assertJsonPath('data.subtotal', '0.30');
        $this->assertDatabaseCount('cart_items', 1);
        $this->patchJson("/api/cart/items/{$product->id}", ['quantity' => 5])
            ->assertOk()->assertJsonPath('data.subtotal', '0.50');
        $product->update(['price' => '12.35']);
        $response = $this->getJson('/api/cart')->assertOk()->assertJsonPath('data.subtotal', '61.75');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_cart_is_isolated_from_other_customers_and_ignores_injected_owner_ids(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->create();
        CartItem::factory()->create(['user_id' => $owner->id, 'product_id' => $product->id, 'quantity' => 2]);
        $this->authenticate($other);
        $this->getJson('/api/cart')->assertOk()->assertJsonCount(0, 'data.items');
        $this->patchJson("/api/cart/items/{$product->id}", ['quantity' => 3])->assertNotFound();
        $this->deleteJson("/api/cart/items/{$product->id}")->assertOk();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1, 'user_id' => $owner->id])
            ->assertCreated()->assertJsonPath('data.item_count', 1);
        $this->deleteJson('/api/cart')->assertOk()->assertJsonPath('data.item_count', 0);
        $this->assertDatabaseHas('cart_items', ['user_id' => $owner->id, 'quantity' => 2]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_stock_and_quantity_limits_are_enforced_on_add_and_update(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 2]);
        $this->authenticate(User::factory()->create());
        foreach ([0, -1, 1.5, 100, 'abc'] as $quantity) {
            $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => $quantity])
                ->assertUnprocessable()->assertJsonValidationErrors('quantity');
        }
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2])->assertCreated();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->patchJson("/api/cart/items/{$product->id}", ['quantity' => 3])->assertUnprocessable();
        $product->update(['stock_quantity' => 0]);
        $this->getJson('/api/cart')->assertOk()->assertJsonPath('data.items.0.available', false)->assertJsonPath('data.items.0.max_quantity', 0);
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertUnprocessable();
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    public function test_merge_cannot_exceed_the_per_product_limit(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 500]);
        $this->authenticate(User::factory()->create());
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 99])->assertCreated();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertUnprocessable();
        $this->getJson('/api/cart')->assertOk()->assertJsonPath('data.item_count', 99);
    }

    public function test_removal_clear_and_product_deletion_leave_a_valid_empty_cart(): void
    {
        $product = Product::factory()->create();
        $this->authenticate(User::factory()->create());
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
        $this->deleteJson("/api/cart/items/{$product->id}")->assertOk()->assertJsonPath('data.subtotal', '0.00');
        $this->deleteJson("/api/cart/items/{$product->id}")->assertOk();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
        $product->delete();
        $this->getJson('/api/cart')->assertOk()->assertJsonCount(0, 'data.items');
        $this->assertDatabaseCount('cart_items', 0);
        $this->deleteJson('/api/cart')->assertOk();
    }

    public function test_cart_persists_across_logout_and_login(): void
    {
        $user = User::factory()->create(['password' => 'customer-password']);
        $product = Product::factory()->create();
        $this->authenticate($user);
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2])->assertCreated();
        $this->postJson('/api/auth/logout')->assertOk();
        Auth::forgetGuards();
        $token = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'customer-password'])->assertOk()->json('access_token');
        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/cart')->assertOk()->assertJsonPath('data.item_count', 2);
    }

    public function test_guests_and_admins_cannot_use_customer_carts(): void
    {
        $product = Product::factory()->create();
        $this->getJson('/api/cart')->assertUnauthorized();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertUnauthorized();
        $this->patchJson("/api/cart/items/{$product->id}", ['quantity' => 1])->assertUnauthorized();
        $this->deleteJson('/api/cart')->assertUnauthorized();
        $this->authenticate(User::factory()->admin()->create());
        $this->getJson('/api/cart')->assertForbidden();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertForbidden();
        $this->patchJson("/api/cart/items/{$product->id}", ['quantity' => 1])->assertForbidden();
        $this->deleteJson("/api/cart/items/{$product->id}")->assertForbidden();
        $this->deleteJson('/api/cart')->assertForbidden();
    }

    public function test_missing_products_and_missing_quantities_are_rejected(): void
    {
        $this->authenticate(User::factory()->create());
        $this->postJson('/api/cart/items', ['product_id' => 999999, 'quantity' => 1])->assertUnprocessable();
        $this->postJson('/api/cart/items', [])->assertUnprocessable()->assertJsonValidationErrors(['product_id', 'quantity']);
    }

    private function authenticate(User $user): void
    {
        Auth::forgetGuards();
        $token = Auth::guard('api')->login($user);
        Auth::forgetGuards();
        $this->withToken($token);
    }
}
