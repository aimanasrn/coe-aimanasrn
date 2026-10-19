<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['jwt.secret' => str_repeat('shop-test-secret-', 5)]);
    }

    public function test_customer_browsing_supports_search_category_price_sort_and_pagination(): void
    {
        $first = Product::factory()->create(['name' => 'Math Book', 'price' => '25.00']);
        Product::factory()->create(['name' => 'Math Workbook', 'price' => '10.00', 'category_id' => $first->category_id]);
        Product::factory()->create(['name' => 'Blue Pen', 'price' => '2.50']);
        $this->authenticate(User::factory()->create());
        $this->getJson('/api/shop/products?q=Math&sort=price_asc')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Math Workbook')->assertJsonPath('total', 2);
        $this->getJson('/api/shop/products?category='.$first->category_id)->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/shop/products?q=missing')->assertOk()->assertJsonCount(0, 'data');
        Product::factory()->count(12)->create();
        $this->getJson('/api/shop/products?page=2')->assertOk()->assertJsonPath('current_page', 2)->assertJsonCount(3, 'data');
        $this->getJson('/api/shop/categories')->assertOk()->assertJsonStructure(['data' => [['id', 'name']]]);
        $this->getJson('/api/shop/products?sort=invalid')->assertUnprocessable();
        $this->getJson('/api/shop/products?page=-1')->assertUnprocessable();
    }

    public function test_customer_can_read_details_and_garage_images_without_access_to_management(): void
    {
        Storage::fake('s3');
        config(['filesystems.product_images_disk' => 's3']);
        Storage::disk('s3')->put('product-images/test.png', 'image-content');
        $product = Product::factory()->create(['images' => ['product-images/test.png']]);
        $this->authenticate(User::factory()->create());
        $this->getJson("/api/shop/products/{$product->id}")->assertOk()
            ->assertJsonPath('data.image_urls.0', "/api/shop/products/{$product->id}/images/0")
            ->assertJsonMissingPath('data.images')->assertJsonMissingPath('data.sku');
        $this->get("/api/shop/products/{$product->id}/images/0")->assertOk()->assertStreamedContent('image-content');
        $this->get("/api/shop/products/{$product->id}/images/1")->assertNotFound();
        $this->getJson('/api/shop/products/999999')->assertNotFound();
        $this->getJson('/api/products')->assertForbidden();
        $this->postJson('/api/products', [])->assertForbidden();
    }

    public function test_shop_is_customer_only_and_requires_authentication(): void
    {
        $this->getJson('/api/shop/products')->assertUnauthorized();
        $this->getJson('/api/shop/categories')->assertUnauthorized();
        $this->authenticate(User::factory()->admin()->create());
        $this->getJson('/api/shop/products')->assertForbidden();
        $this->getJson('/api/shop/categories')->assertForbidden();
    }

    private function authenticate(User $user): void
    {
        Auth::forgetGuards();
        $token = Auth::guard('api')->login($user);
        Auth::forgetGuards();
        $this->withToken($token);
    }
}
