<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\LandingController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_get_catalog_stats_categories_and_featured_products(): void
    {
        $books = Category::factory()->create(['name' => 'Books']);
        $pens = Category::factory()->create(['name' => 'Pens']);
        Product::factory()->for($books)->count(2)->create(['stock_quantity' => 5]);
        $soldOut = Product::factory()->for($pens)->create(['stock_quantity' => 0]);

        $response = $this->getJson('/api/landing')->assertOk()->assertJsonPaths([
            'stats.products' => 3,
            'stats.categories' => 2,
            'stats.in_stock' => 2,
            'categories.0.name' => 'Books',
            'categories.0.products_count' => 2,
            'categories.1.name' => 'Pens',
        ]);

        $response->assertJsonCount(2, 'featured');
        $this->assertNotContains($soldOut->id, array_column($response->json('featured'), 'id'));
    }

    public function test_featured_products_hide_admin_details_and_prefer_products_with_images(): void
    {
        $withoutImage = Product::factory()->create(['stock_quantity' => 3, 'images' => []]);
        $withImage = Product::factory()->create(['stock_quantity' => 3, 'images' => ['product-images/a.png']]);

        $response = $this->getJson('/api/landing')->assertOk()
            ->assertJsonPath('featured.0.id', $withImage->id)
            ->assertJsonPath('featured.0.image_url', "/api/landing/products/{$withImage->id}/images/0")
            ->assertJsonPath('featured.1.id', $withoutImage->id)
            ->assertJsonPath('featured.1.image_url', null);

        $this->assertSame(
            ['id', 'name', 'price', 'category', 'image_url'],
            array_keys($response->json('featured.0'))
        );
    }

    public function test_featured_products_are_limited(): void
    {
        Product::factory()->count(LandingController::FEATURED_LIMIT + 3)->create(['stock_quantity' => 1]);

        $this->getJson('/api/landing')->assertOk()
            ->assertJsonCount(LandingController::FEATURED_LIMIT, 'featured');
    }

    public function test_guests_can_load_product_images(): void
    {
        Storage::fake('s3');
        config(['filesystems.product_images_disk' => 's3']);
        Storage::disk('s3')->put('product-images/cover.png', 'image-content');
        $product = Product::factory()->create(['images' => ['product-images/cover.png']]);

        $this->get("/api/landing/products/{$product->id}/images/0")
            ->assertOk()->assertStreamedContent('image-content');
        $this->get("/api/landing/products/{$product->id}/images/1")->assertNotFound();
    }

    public function test_guests_still_cannot_use_the_shop_or_admin_catalog(): void
    {
        $this->getJson('/api/shop/products')->assertUnauthorized();
        $this->getJson('/api/products')->assertUnauthorized();
    }
}
