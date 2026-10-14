<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_updates_the_selected_product_and_allows_keeping_its_sku(): void
    {
        $product = Product::factory()->create();
        $other = Product::factory()->create();
        $category = Category::factory()->create();
        $payload = $this->payload($product, [
            'category_id' => $category->id,
            'name' => 'Updated workbook',
            'description' => null,
            'price' => '0.00',
            'stock_quantity' => 0,
        ]);

        $this->putJson("/api/products/{$product->id}", $payload)
            ->assertOk()
            ->assertJsonPaths([
                'message' => 'Product updated successfully.',
                'data.id' => $product->id,
                'data.name' => 'Updated workbook',
                'data.sku' => $product->sku,
                'data.category.id' => $category->id,
                'data.price' => '0.00',
                'data.stock_quantity' => 0,
                'data.description' => null,
            ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, ...$payload]);
        $this->assertDatabaseHas('products', [
            'id' => $other->id,
            'name' => $other->name,
            'sku' => $other->sku,
        ]);
        $this->assertDatabaseCount('products', 2);
    }

    public function test_rejects_another_products_sku_without_changing_the_record(): void
    {
        $product = Product::factory()->create();
        $other = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", $this->payload($product, [
            'sku' => $other->sku,
            'name' => 'Should not be saved',
        ]))->assertUnprocessable()->assertJsonValidationErrors('sku');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
        ]);
    }

    #[DataProvider('invalidUpdates')]
    public function test_rejects_invalid_updates(string $field, mixed $value): void
    {
        $product = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", $this->payload($product, [
            $field => $value,
        ]))->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            ...$this->payload($product),
        ]);
    }

    public static function invalidUpdates(): array
    {
        return [
            'missing name' => ['name', ''],
            'missing sku' => ['sku', ''],
            'missing category' => ['category_id', null],
            'unknown category' => ['category_id', 999999],
            'negative price' => ['price', '-1.00'],
            'price precision' => ['price', '1.234'],
            'negative stock' => ['stock_quantity', -1],
            'fractional stock' => ['stock_quantity', 1.5],
            'stock overflow' => ['stock_quantity', 2147483648],
        ];
    }

    public function test_returns_not_found_for_a_missing_product(): void
    {
        $this->putJson('/api/products/999999', [])->assertNotFound();
    }

    public function test_creates_products_and_still_rejects_duplicate_skus(): void
    {
        $category = Category::factory()->create();
        $payload = [
            'name' => 'New workbook',
            'sku' => 'NEW-WORKBOOK-001',
            'category_id' => $category->id,
            'price' => '19.90',
            'stock_quantity' => 5,
            'description' => null,
        ];

        $this->postJson('/api/products', $payload)
            ->assertCreated()
            ->assertJsonPath('data.sku', 'NEW-WORKBOOK-001');

        $this->postJson('/api/products', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sku');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_deletes_only_the_selected_product_and_refreshes_inventory_totals(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $other = Product::factory()->for($category)->create([
            'price' => '10.00',
            'stock_quantity' => 3,
        ]);

        $this->deleteJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseHas('products', ['id' => $other->id]);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->getJson("/api/products/{$product->id}")->assertNotFound();
        $this->getJson('/api/products')->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $other->id);
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPaths([
            'data.summary.total_products' => 1,
            'data.summary.stock_units' => 3,
        ])->assertJsonPath('data.summary.stock_value', fn (string $value): bool => (float) $value === 30.0);
        $this->getJson('/api/categories')->assertJsonPath('data.0.products_count', 1);
        $this->deleteJson("/api/products/{$product->id}")->assertNotFound();
    }

    public function test_returns_not_found_when_deleting_a_missing_product(): void
    {
        $this->deleteJson('/api/products/999999')->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Product $product, array $overrides = []): array
    {
        return array_replace($product->only([
            'category_id', 'sku', 'name', 'description', 'price', 'stock_quantity',
        ]), $overrides);
    }
}
