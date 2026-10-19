<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withToken(auth('api')->login(User::factory()->admin()->create()));
    }

    public function test_creates_a_category_with_a_description_and_product_count(): void
    {
        $this->postJson('/api/categories', [
            'name' => '  Learning resources  ',
            'description' => 'Workbooks and revision materials.',
        ])->assertCreated()->assertJsonPaths([
            'data.name' => 'Learning resources',
            'data.description' => 'Workbooks and revision materials.',
            'data.products_count' => 0,
        ]);

        $this->assertDatabaseHas('categories', ['name' => 'Learning resources']);
    }

    public function test_description_is_optional(): void
    {
        $this->postJson('/api/categories', ['name' => 'Stationery'])
            ->assertCreated()->assertJsonPath('data.description', null);
    }

    public function test_shows_the_selected_category_with_its_product_count(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->for($category)->create();

        $this->getJson("/api/categories/{$category->id}")->assertOk()->assertJsonPaths([
            'data.id' => $category->id,
            'data.description' => $category->description,
            'data.products_count' => 2,
        ]);
    }

    public function test_updates_only_the_selected_category_and_preserves_product_links(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated category',
            'description' => null,
        ])->assertOk()->assertJsonPaths([
            'data.name' => 'Updated category',
            'data.description' => null,
            'data.products_count' => 1,
        ]);

        $this->assertDatabaseHas('categories', ['id' => $other->id, 'name' => $other->name]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $category->id]);
    }

    public function test_allows_keeping_the_same_category_name(): void
    {
        $category = Category::factory()->create();

        $this->putJson("/api/categories/{$category->id}", [
            'name' => $category->name,
            'description' => 'Updated description.',
        ])->assertOk()->assertJsonPath('data.description', 'Updated description.');
    }

    public function test_rejects_duplicate_names_on_create_and_update(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();

        $this->postJson('/api/categories', ['name' => $category->name])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->putJson("/api/categories/{$other->id}", ['name' => $category->name])
            ->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', ['id' => $other->id, 'name' => $other->name]);
    }

    #[DataProvider('invalidCategories')]
    public function test_rejects_invalid_categories_on_create_and_update(string $field, mixed $value): void
    {
        $category = Category::factory()->create();
        $payload = ['name' => 'Valid category', 'description' => null, $field => $value];

        $this->postJson('/api/categories', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->putJson("/api/categories/{$category->id}", $payload)
            ->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $category->name]);
    }

    public static function invalidCategories(): array
    {
        return [
            'empty name' => ['name', ''],
            'blank name' => ['name', '   '],
            'non-string name' => ['name', ['invalid']],
            'long name' => ['name', str_repeat('a', 101)],
            'non-string description' => ['description', ['invalid']],
            'long description' => ['description', str_repeat('a', 5001)],
        ];
    }

    public function test_deletes_an_empty_category_without_affecting_other_categories(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();
        $product = Product::factory()->for($other)->create();

        $this->deleteJson("/api/categories/{$category->id}")->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('categories', ['id' => $other->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $other->id]);
    }

    public function test_cannot_delete_a_category_that_contains_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $this->deleteJson("/api/categories/{$category->id}")
            ->assertConflict()->assertJsonPath('message', 'This category still contains products. Move them to another category before deleting it.');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $category->id]);
    }

    public function test_returns_not_found_for_unknown_categories(): void
    {
        $this->getJson('/api/categories/999999')->assertNotFound();
        $this->putJson('/api/categories/999999', ['name' => 'Unknown'])->assertNotFound();
        $this->deleteJson('/api/categories/999999')->assertNotFound();
    }
}
