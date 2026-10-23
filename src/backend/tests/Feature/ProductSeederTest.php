<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeds_the_supplied_products_into_categories_matched_by_name(): void
    {
        $unrelated = Category::factory()->create(['name' => 'Unrelated category']);
        $this->seed(CategorySeeder::class);
        $this->seed(ProductSeeder::class);

        $expectedCounts = [
            'Writing Instruments' => 6,
            'Notebooks & Paper' => 5,
            'Erasers & Sharpeners' => 2,
            'Correction Supplies' => 2,
            'Geometry & Measuring Tools' => 3,
            'Files & Folders' => 3,
            'Office Accessories' => 4,
            'Cutting & Adhesives' => 3,
            'Art & Colouring Supplies' => 3,
            'Storage & Organisers' => 1,
        ];

        $this->assertDatabaseCount('products', 32);
        $this->assertDatabaseCount('categories', 11);
        $this->assertEquals($expectedCounts, Category::query()
            ->whereIn('name', array_keys($expectedCounts))->withCount('products')
            ->pluck('products_count', 'name')->all());
        $this->assertSame(0, $unrelated->products()->count());
        $this->assertSame(32, Product::query()->where('price', 0)->where('stock_quantity', 0)->count());
        $this->assertDatabaseHas('products', [
            'sku' => 'ST-PEN-BP-BLU-001',
            'name' => 'Blue Ballpoint Pen',
            'category_id' => Category::query()->where('name', 'Writing Instruments')->value('id'),
        ]);
    }

    public function test_rerunning_preserves_existing_prices_stock_and_product_details_without_duplicates(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(ProductSeeder::class);
        $pen = Product::query()->where('sku', 'ST-PEN-BP-BLU-001')->firstOrFail();
        $pen->update([
            'price' => '4.50',
            'stock_quantity' => 17,
            'brand' => 'Existing brand',
            'barcode' => '123456789',
            'unit' => 'pack',
            'images' => ['existing/pen.jpg'],
        ]);
        $other = Product::factory()->for($pen->category)->create(['sku' => 'CUSTOM-001']);

        $this->seed(ProductSeeder::class);

        $this->assertDatabaseCount('products', 33);
        $this->assertDatabaseCount('categories', 10);
        $pen->refresh();
        $this->assertSame('4.50', $pen->price);
        $this->assertSame(17, $pen->stock_quantity);
        $this->assertSame('Existing brand', $pen->brand);
        $this->assertSame('123456789', $pen->barcode);
        $this->assertSame('pack', $pen->unit);
        $this->assertSame(['existing/pen.jpg'], $pen->images);
        $this->assertDatabaseHas('products', ['id' => $other->id, 'sku' => 'CUSTOM-001']);
    }

    public function test_missing_categories_fail_before_changing_any_products(): void
    {
        $this->seed(CategorySeeder::class);
        Category::query()->where('name', 'Storage & Organisers')->firstOrFail()->delete();
        $pen = Product::factory()->create([
            'category_id' => Category::query()->where('name', 'Writing Instruments')->value('id'),
            'sku' => 'ST-PEN-BP-BLU-001',
            'name' => 'Existing custom name',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing categories: Storage & Organisers');

        try {
            $this->seed(ProductSeeder::class);
        } finally {
            $this->assertDatabaseCount('products', 1);
            $this->assertDatabaseHas('products', ['id' => $pen->id, 'name' => 'Existing custom name']);
        }
    }
}
