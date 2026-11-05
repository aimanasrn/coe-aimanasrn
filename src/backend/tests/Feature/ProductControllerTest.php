<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withToken(auth('api')->login(User::factory()->admin()->create()));
    }

    public function test_updates_the_selected_product_and_keeps_its_sku(): void
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

    public function test_ignores_sku_changes_when_updating(): void
    {
        $product = Product::factory()->create();
        $other = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", [
            ...$this->payload($product, ['name' => 'Renamed workbook']),
            'sku' => $other->sku,
        ])->assertOk()->assertJsonPath('data.sku', $product->sku);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Renamed workbook',
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

    public function test_creates_products_with_an_automatic_sku_and_ignores_a_typed_sku(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);
        $payload = [
            'name' => 'New workbook',
            'sku' => 'TYPED-SKU-001',
            'category_id' => $category->id,
            'price' => '19.90',
            'stock_quantity' => 5,
            'description' => null,
        ];

        $first = $this->postJson('/api/products', $payload)->assertCreated();
        $second = $this->postJson('/api/products', $payload)->assertCreated();

        $firstId = $first->json('data.id');
        $secondId = $second->json('data.id');
        $first->assertJsonPath('data.sku', 'BOOK-'.str_pad((string) $firstId, 5, '0', STR_PAD_LEFT));
        $second->assertJsonPath('data.sku', 'BOOK-'.str_pad((string) $secondId, 5, '0', STR_PAD_LEFT));
        $this->assertDatabaseMissing('products', ['sku' => 'TYPED-SKU-001']);
        $this->assertDatabaseCount('products', 2);
    }

    #[DataProvider('categoryCodes')]
    public function test_builds_the_sku_code_from_the_category_name(string $categoryName, string $code): void
    {
        $category = Category::factory()->create(['name' => $categoryName]);

        $response = $this->postJson('/api/products', [
            'name' => 'Coded product',
            'category_id' => $category->id,
            'price' => '1.00',
            'stock_quantity' => 1,
        ])->assertCreated();

        $response->assertJsonPath(
            'data.sku',
            $code.'-'.str_pad((string) $response->json('data.id'), 5, '0', STR_PAD_LEFT)
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function categoryCodes(): array
    {
        return [
            'long name' => ['Stationery', 'STAT'],
            'short name' => ['Art', 'ART'],
            'spaces and symbols' => ['A4 & paper', 'A4PA'],
            'accented letters' => ['Écoles', 'ECOL'],
            'no letters or digits' => ['& ! ?', 'PROD'],
        ];
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
            'category_id', 'name', 'description', 'price', 'stock_quantity',
        ]), $overrides);
    }
}
