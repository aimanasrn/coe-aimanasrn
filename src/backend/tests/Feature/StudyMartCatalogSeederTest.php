<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\StudyMartCatalogSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StudyMartCatalogSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeds_every_category_and_product_with_automatic_skus(): void
    {
        $this->seed(StudyMartCatalogSeeder::class);

        $this->assertDatabaseCount('categories', 11);
        $this->assertDatabaseCount('products', 53);
        $this->assertSame(53, Product::query()->where('brand', StudyMartCatalogSeeder::BRAND)->count());
        $this->assertSame(0, Product::query()->where('price', '<=', 0)->count());
        $this->assertTrue(Product::query()->where('stock_quantity', 0)->exists());
        $this->assertTrue(Product::query()
            ->whereBetween('stock_quantity', [1, Product::LOW_STOCK_THRESHOLD])->exists());
        $this->assertTrue(Category::query()->doesntHave('products')->doesntExist());

        Product::query()->with('category')->each(function (Product $product): void {
            $this->assertSame($product->generatedSku(), $product->sku);
        });

        $pen = Product::query()->where('name', 'Blue Ballpoint Pen')->firstOrFail();
        $this->assertSame('Writing Instruments', $pen->category->name);
        $this->assertStringStartsWith('WRIT-', $pen->sku);
        $this->assertSame('1.50', $pen->price);
    }

    public function test_rerunning_keeps_admin_changes_and_creates_no_duplicates(): void
    {
        $this->seed(StudyMartCatalogSeeder::class);
        $pen = Product::query()->where('name', 'Blue Ballpoint Pen')->firstOrFail();
        $pen->update([
            'price' => '1.80',
            'stock_quantity' => 17,
            'brand' => 'Custom brand',
            'unit' => 'pack',
            'images' => ['existing/pen.jpg'],
        ]);
        $originalSku = $pen->sku;

        $this->seed(StudyMartCatalogSeeder::class);

        $this->assertDatabaseCount('categories', 11);
        $this->assertDatabaseCount('products', 53);
        $pen->refresh();
        $this->assertSame($originalSku, $pen->sku);
        $this->assertSame('1.80', $pen->price);
        $this->assertSame(17, $pen->stock_quantity);
        $this->assertSame('Custom brand', $pen->brand);
        $this->assertSame('pack', $pen->unit);
        $this->assertSame(['existing/pen.jpg'], $pen->images);
    }

    public function test_fills_placeholder_products_from_the_old_seeder_but_keeps_their_skus(): void
    {
        $category = Category::factory()->create(['name' => 'Writing Instruments']);
        $placeholder = Product::factory()->for($category)->create([
            'name' => 'Blue Ballpoint Pen',
            'sku' => 'ST-PEN-BP-BLU-001',
            'price' => '0.00',
            'stock_quantity' => 0,
            'brand' => null,
        ]);

        $this->seed(StudyMartCatalogSeeder::class);

        $placeholder->refresh();
        $this->assertDatabaseCount('products', 53);
        $this->assertSame('ST-PEN-BP-BLU-001', $placeholder->sku);
        $this->assertSame('1.50', $placeholder->price);
        $this->assertSame(240, $placeholder->stock_quantity);
        $this->assertSame(StudyMartCatalogSeeder::BRAND, $placeholder->brand);
    }

    public function test_database_seeder_can_run_more_than_once(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'test@example.com')->count());
        $this->assertDatabaseCount('products', 53);
    }
}
