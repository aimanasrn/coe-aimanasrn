<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withToken(auth('api')->login(User::factory()->admin()->create()));
    }

    public function test_lists_categories_alphabetically_including_empty_categories(): void
    {
        Category::factory()->create(['name' => 'Stationery']);
        $books = Category::factory()->create(['name' => 'Books']);
        Product::factory()->for($books)->create();

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(2, 'data')->assertJsonPaths([
            'data.0.name' => 'Books', 'data.0.products_count' => 1,
            'data.1.name' => 'Stationery', 'data.1.products_count' => 0,
        ]);
    }

    public function test_returns_an_empty_list_when_no_categories_exist(): void
    {
        $this->getJson('/api/categories')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_paginates_the_catalog_without_truncating_the_category_picker(): void
    {
        for ($number = 1; $number <= 12; $number++) {
            Category::factory()->create(['name' => sprintf('Category %02d', $number)]);
        }

        $this->getJson('/api/categories?per_page=5&page=2')
            ->assertOk()->assertJsonCount(5, 'data')->assertJsonPaths([
                'data.0.name' => 'Category 06',
                'data.4.name' => 'Category 10',
                'total' => 12,
                'current_page' => 2,
                'per_page' => 5,
                'last_page' => 3,
            ]);

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(12, 'data');
    }

    public function test_searches_names_and_descriptions_case_insensitively(): void
    {
        Category::factory()->create(['name' => 'Books', 'description' => null]);
        Category::factory()->create(['name' => 'Writing', 'description' => 'Notebooks for revision']);
        Category::factory()->create(['name' => 'Other', 'description' => 'Everyday supplies']);

        $this->getJson('/api/categories?per_page=5&q=BOOK')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPaths([
                'data.0.name' => 'Books',
                'data.1.name' => 'Writing',
                'total' => 2,
            ]);
    }

    #[DataProvider('productFilters')]
    public function test_filters_categories_by_product_presence(string $filter, array $names): void
    {
        $books = Category::factory()->create(['name' => 'Books']);
        Product::factory()->for($books)->create();
        Category::factory()->create(['name' => 'Stationery']);

        $response = $this->getJson('/api/categories?per_page=5&products='.$filter)->assertOk();

        $this->assertSame($names, array_column($response->json('data'), 'name'));
        $response->assertJsonPath('total', count($names));
    }

    public static function productFilters(): array
    {
        return [
            'all' => ['all', ['Books', 'Stationery']],
            'with products' => ['with', ['Books']],
            'empty categories' => ['empty', ['Stationery']],
        ];
    }

    public function test_combines_search_with_the_product_filter(): void
    {
        Category::factory()->create(['name' => 'Empty study', 'description' => 'Study resources']);
        $matching = Category::factory()->create(['name' => 'Writing', 'description' => 'Study resources']);
        $other = Category::factory()->create(['name' => 'Other', 'description' => null]);
        Product::factory()->for($matching)->create();
        Product::factory()->for($other)->create();

        $this->getJson('/api/categories?per_page=5&products=with&q=study')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPaths([
                'data.0.id' => $matching->id,
                'data.0.products_count' => 1,
                'total' => 1,
            ]);
    }

    public function test_sorts_by_product_count_with_stable_ordering_for_ties(): void
    {
        $first = Category::factory()->create(['name' => 'A']);
        $most = Category::factory()->create(['name' => 'Z']);
        $empty = Category::factory()->create(['name' => 'B']);
        $tied = Category::factory()->create(['name' => 'C']);
        Product::factory()->for($first)->create();
        Product::factory()->count(3)->for($most)->create();
        Product::factory()->for($tied)->create();

        $response = $this->getJson('/api/categories?per_page=5&sort_by=products_count&sort_direction=desc')
            ->assertOk()->assertJsonPath('data.0.products_count', 3);

        $this->assertSame([$most->id, $first->id, $tied->id, $empty->id], array_column($response->json('data'), 'id'));
    }

    public function test_returns_pagination_metadata_when_no_categories_match(): void
    {
        Category::factory()->create(['name' => 'Books']);

        $this->getJson('/api/categories?per_page=5&q=missing')
            ->assertOk()->assertJsonCount(0, 'data')->assertJsonPaths([
                'total' => 0,
                'last_page' => 1,
                'current_page' => 1,
            ]);
    }

    #[DataProvider('invalidCatalogQueries')]
    public function test_rejects_invalid_catalog_queries(string $field, mixed $value): void
    {
        $query = http_build_query(['per_page' => 5, $field => $value]);

        $this->getJson('/api/categories?'.$query)->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public static function invalidCatalogQueries(): array
    {
        return [
            'negative page' => ['page', 0],
            'excessive page' => ['page', 10001],
            'unsupported page size' => ['per_page', 1000],
            'invalid product filter' => ['products', 'missing'],
            'invalid sort column' => ['sort_by', 'description'],
            'invalid sort direction' => ['sort_direction', 'invalid'],
            'long search' => ['q', str_repeat('a', 151)],
            'array search' => ['q', ['invalid']],
        ];
    }
}
