<?php

namespace Tests\Feature;

use App\CatalogCache;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogCacheTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['jwt.secret' => str_repeat('cache-test-secret-', 5)]);
    }

    public function test_cache_hits_parameter_separation_expiry_and_invalidation(): void
    {
        $calls = 0;
        $load = function () use (&$calls): array {
            return ['value' => ++$calls];
        };
        $this->assertSame(['value' => 1], CatalogCache::remember('test', ['page' => 1], $load));
        $this->assertSame(['value' => 1], CatalogCache::remember('test', ['page' => 1], $load));
        $this->assertSame(['value' => 2], CatalogCache::remember('test', ['page' => 2], $load));
        $this->travel(CatalogCache::TTL_SECONDS + 1)->seconds();
        $this->assertSame(['value' => 3], CatalogCache::remember('test', ['page' => 1], $load));
        CatalogCache::invalidate();
        $this->assertSame(['value' => 4], CatalogCache::remember('test', ['page' => 1], $load));
    }

    public function test_committed_product_and_category_changes_invalidate_customer_and_admin_reads(): void
    {
        $product = Product::factory()->create(['name' => 'Original', 'price' => '10.00', 'stock_quantity' => 10]);
        $customer = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->authenticate($customer);
        $this->getJson('/api/shop/products')->assertOk()->assertJsonPath('data.0.name', 'Original');
        $this->getJson("/api/shop/products/{$product->id}")->assertOk();
        $this->getJson('/api/shop/categories')->assertOk();
        $this->authenticate($admin);
        $this->getJson('/api/products')->assertOk();
        $this->getJson('/api/categories')->assertOk();
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPath('data.summary.stock_units', 10);

        DB::transaction(function () use ($product): void {
            $product->update(['name' => 'Changed', 'price' => '20.00', 'stock_quantity' => 3]);
            $product->category->update(['name' => 'Renamed category']);
        });
        $this->getJson('/api/products')->assertOk()->assertJsonPath('data.0.name', 'Changed')->assertJsonPath('data.0.category.name', 'Renamed category');
        $this->getJson('/api/categories')->assertOk()->assertJsonPath('data.0.name', 'Renamed category');
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPath('data.summary.stock_units', 3);
        $this->authenticate($customer);
        $this->getJson('/api/shop/products')->assertOk()->assertJsonPath('data.0.price', '20.00');
        $this->getJson("/api/shop/products/{$product->id}")->assertOk()->assertJsonPath('data.name', 'Changed');
        $this->getJson('/api/shop/categories')->assertOk()->assertJsonPath('data.0.name', 'Renamed category');
        $product->delete();
        $this->getJson('/api/shop/products')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/shop/products/{$product->id}")->assertNotFound();
        $product->category->delete();
        $this->getJson('/api/shop/categories')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_rollback_does_not_invalidate_or_publish_uncommitted_data(): void
    {
        $category = Category::factory()->create(['name' => 'Committed']);
        $load = fn (): array => ['name' => $category->fresh()->name];
        CatalogCache::remember('rollback-test', [], $load);
        $version = Cache::get('catalog:version');
        DB::beginTransaction();
        $category->update(['name' => 'Uncommitted']);
        $this->assertSame(['name' => 'Uncommitted'], CatalogCache::remember('rollback-test', [], $load));
        DB::rollBack();
        $this->assertSame($version, Cache::get('catalog:version'));
        $this->assertSame(['name' => 'Committed'], CatalogCache::remember('rollback-test', [], $load));
    }

    public function test_cached_data_never_bypasses_authorization(): void
    {
        Product::factory()->create();
        $this->authenticate(User::factory()->admin()->create());
        $this->getJson('/api/products')->assertOk();
        $this->getJson('/api/dashboard')->assertOk();
        $this->authenticate(User::factory()->create());
        $this->getJson('/api/products')->assertForbidden();
        $this->getJson('/api/dashboard')->assertForbidden();
    }

    private function authenticate(User $user): void
    {
        Auth::forgetGuards();
        $token = Auth::guard('api')->login($user);
        Auth::forgetGuards();
        $this->withToken($token);
    }
}
