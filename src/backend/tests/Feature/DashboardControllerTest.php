<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withToken(auth('api')->login(User::factory()->admin()->create()));
    }

    public function test_returns_accurate_inventory_totals_and_disjoint_stock_counts(): void
    {
        $books = Category::factory()->create(['name' => 'Books']);
        Category::factory()->create(['name' => 'Stationery']);
        Product::factory()->for($books)->create(['price' => '15.90', 'stock_quantity' => 20]);
        Product::factory()->for($books)->create(['price' => '10.00', 'stock_quantity' => 5]);
        Product::factory()->for($books)->create(['price' => '99.99', 'stock_quantity' => 0]);

        $this->getJson('/api/dashboard')->assertOk()->assertJsonPaths([
            'data.summary.total_products' => 3,
            'data.summary.total_categories' => 2,
            'data.summary.stock_units' => 25,
            'data.summary.stock_value' => '368.00',
            'data.stock_counts.in_stock' => 1,
            'data.stock_counts.low_stock' => 1,
            'data.stock_counts.out_of_stock' => 1,
            'data.low_stock_threshold' => 5,
            'data.filtered_total' => 3,
            'data.categories.0.products_count' => 3,
            'data.categories.1.products_count' => 0,
        ]);
    }

    public function test_returns_zero_totals_for_an_empty_catalog(): void
    {
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPaths([
            'data.summary.total_products' => 0,
            'data.summary.total_categories' => 0,
            'data.summary.stock_units' => 0,
            'data.summary.stock_value' => '0.00',
            'data.stock_counts.in_stock' => 0,
            'data.stock_counts.low_stock' => 0,
            'data.stock_counts.out_of_stock' => 0,
            'data.filtered_total' => 0,
        ])->assertJsonCount(0, 'data.products')->assertJsonCount(0, 'data.categories');
    }

    public function test_search_matches_names_and_skus_without_changing_catalog_totals(): void
    {
        $books = Category::factory()->create();
        $science = Product::factory()->for($books)->create(['name' => 'Science Workbook', 'sku' => 'BOOK-SCIENCE-001']);
        Product::factory()->for($books)->create(['name' => 'Mathematics Workbook', 'sku' => 'BOOK-MATH-001']);

        $this->getJson('/api/dashboard?q=science')->assertOk()
            ->assertJsonPath('data.summary.total_products', 2)
            ->assertJsonPath('data.filtered_total', 1)
            ->assertJsonPath('data.products.0.id', $science->id);

        $this->getJson('/api/dashboard?q=BOOK-SCIENCE-001')->assertOk()
            ->assertJsonPath('data.filtered_total', 1)
            ->assertJsonPath('data.products.0.id', $science->id);
    }

    #[DataProvider('stockFilters')]
    public function test_stock_filters_include_the_correct_boundary_quantities(string $filter, array $quantities): void
    {
        $books = Category::factory()->create();
        Product::factory()->count(4)->for($books)->sequence(
            ['stock_quantity' => 0], ['stock_quantity' => 1], ['stock_quantity' => 5], ['stock_quantity' => 6]
        )->create();

        $response = $this->getJson('/api/dashboard?stock='.$filter)->assertOk()
            ->assertJsonPath('data.filtered_total', count($quantities));

        $actualQuantities = array_column($response->json('data.products'), 'stock_quantity');
        sort($actualQuantities);
        $this->assertSame($quantities, $actualQuantities);
    }

    public static function stockFilters(): array
    {
        return [
            'all quantities' => ['all', [0, 1, 5, 6]],
            'low stock includes one and five but excludes zero' => ['low', [1, 5]],
            'out of stock includes only zero' => ['out', [0]],
        ];
    }

    public function test_recent_products_are_capped_at_six_while_totals_include_the_whole_catalog(): void
    {
        $this->freezeTime();
        $books = Category::factory()->create();
        $products = Product::factory()->count(8)->for($books)->create();

        $this->getJson('/api/dashboard')->assertOk()->assertJsonCount(6, 'data.products')
            ->assertJsonPath('data.products.0.id', $products->last()->id)
            ->assertJsonPath('data.filtered_total', 8)
            ->assertJsonPath('data.summary.total_products', 8);
    }

    public function test_search_input_cannot_turn_into_an_unfiltered_sql_query(): void
    {
        Product::factory()->create(['name' => 'Science Workbook']);

        $this->getJson('/api/dashboard?'.http_build_query(['q' => "' OR 1=1 --"]))
            ->assertOk()->assertJsonPath('data.filtered_total', 0)
            ->assertJsonPath('data.summary.total_products', 1);
    }

    public function test_returns_422_for_invalid_filters(): void
    {
        $this->getJson('/api/dashboard?stock=unknown')->assertUnprocessable()
            ->assertJsonValidationErrors('stock')
            ->assertJsonPath('errors.stock.0', 'The selected stock is invalid.');

        $this->getJson('/api/dashboard?'.http_build_query(['q' => str_repeat('a', 151)]))
            ->assertUnprocessable()->assertJsonValidationErrors('q');
    }

    public function test_creating_a_product_updates_dashboard_counts_and_value(): void
    {
        $books = Category::factory()->create();
        $payload = [
            'category_id' => $books->id, 'sku' => 'BOOK-NEW-001', 'name' => 'New Workbook',
            'price' => '12.50', 'stock_quantity' => 10,
        ];

        $this->postJson('/api/products', $payload)->assertCreated()->assertJsonPath('data.name', 'New Workbook');

        $this->assertDatabaseHas('products', ['sku' => 'BOOK-NEW-001', 'stock_quantity' => 10]);
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPaths([
            'data.summary.total_products' => 1, 'data.summary.stock_units' => 10,
            'data.summary.stock_value' => '125.00',
        ]);
    }

    public function test_full_product_listing_uses_the_same_inventory_filters(): void
    {
        $books = Category::factory()->create();
        $lowStock = Product::factory()->for($books)->create(['name' => 'Science Workbook', 'stock_quantity' => 5]);
        Product::factory()->for($books)->create(['name' => 'Science Kit', 'stock_quantity' => 20]);

        $this->getJson('/api/products?q=science&stock=low&per_page=5')->assertOk()
            ->assertJsonPath('per_page', 5)
            ->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $lowStock->id);
    }

    #[DataProvider('productPageSizes')]
    public function test_product_listing_respects_page_size_and_page_offset(int $perPage): void
    {
        $products = Product::factory()->count(56)->for(Category::factory())->create();

        $this->getJson('/api/products?per_page='.$perPage)->assertOk()
            ->assertJsonCount($perPage, 'data')
            ->assertJsonPaths([
                'per_page' => $perPage,
                'total' => 56,
                'last_page' => (int) ceil(56 / $perPage),
                'data.0.id' => $products->first()->id,
            ]);

        $this->getJson('/api/products?per_page='.$perPage.'&page=2')->assertOk()
            ->assertJsonCount(min($perPage, 56 - $perPage), 'data')
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('data.0.id', $products[$perPage]->id);
    }

    public static function productPageSizes(): array
    {
        return array_map(fn (int $size): array => [$size], [5, 10, 15, 20, 25, 50]);
    }

    public function test_product_listing_defaults_to_ten_rows_without_a_page_size(): void
    {
        Product::factory()->count(12)->for(Category::factory())->create();

        $this->getJson('/api/products')->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('last_page', 2);
    }

    #[DataProvider('invalidProductPageSizes')]
    public function test_product_listing_rejects_invalid_page_sizes(mixed $perPage): void
    {
        $this->getJson('/api/products?'.http_build_query(['per_page' => $perPage]))
            ->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    #[DataProvider('productSortOrders')]
    public function test_product_listing_sorts_across_pages_with_stable_ties(string $column, string $direction, array $expectedIndexes): void
    {
        $products = Product::factory()->count(6)->for(Category::factory())->sequence(
            ['name' => 'Zulu', 'price' => '100.00', 'stock_quantity' => 20],
            ['name' => 'Alpha', 'price' => '2.00', 'stock_quantity' => 5],
            ['name' => 'Echo', 'price' => '9.00', 'stock_quantity' => 0],
            ['name' => 'Bravo', 'price' => '2.00', 'stock_quantity' => 5],
            ['name' => 'Echo', 'price' => '9.00', 'stock_quantity' => 1],
            ['name' => 'Yankee', 'price' => '25.00', 'stock_quantity' => 100],
        )->create();

        $query = http_build_query(['sort_by' => $column, 'sort_direction' => $direction, 'per_page' => 5]);
        $firstPage = $this->getJson('/api/products?'.$query)->assertOk()
            ->assertJsonPath('total', 6)->assertJsonPath('last_page', 2)->assertJsonCount(5, 'data');
        $secondPage = $this->getJson('/api/products?'.$query.'&page=2')->assertOk()
            ->assertJsonPath('current_page', 2)->assertJsonCount(1, 'data');

        $actualIds = array_column(array_merge($firstPage->json('data'), $secondPage->json('data')), 'id');
        $expectedIds = array_map(fn (int $index): int => $products[$index]->id, $expectedIndexes);
        $this->assertSame($expectedIds, $actualIds);

        $this->getJson('/api/products?'.$query.'&q=echo&stock=low')->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $products[4]->id);
    }

    public static function productSortOrders(): array
    {
        return [
            'name ascending' => ['name', 'asc', [1, 3, 2, 4, 5, 0]],
            'name descending' => ['name', 'desc', [0, 5, 2, 4, 3, 1]],
            'price ascending' => ['price', 'asc', [1, 3, 2, 4, 5, 0]],
            'price descending' => ['price', 'desc', [0, 5, 2, 4, 1, 3]],
            'stock ascending' => ['stock_quantity', 'asc', [2, 4, 1, 3, 0, 5]],
            'stock descending' => ['stock_quantity', 'desc', [5, 0, 1, 3, 4, 2]],
        ];
    }

    #[DataProvider('invalidProductSorting')]
    public function test_product_listing_rejects_invalid_sorting(array $query, string $field): void
    {
        $this->getJson('/api/products?'.http_build_query($query))->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }

    public static function invalidProductSorting(): array
    {
        return [
            'unknown column' => [['sort_by' => 'unknown'], 'sort_by'],
            'SQL expression' => [['sort_by' => 'price desc, id'], 'sort_by'],
            'array column' => [['sort_by' => ['name']], 'sort_by'],
            'unknown direction' => [['sort_direction' => 'up'], 'sort_direction'],
            'SQL direction' => [['sort_direction' => 'desc; DROP TABLE products'], 'sort_direction'],
            'array direction' => [['sort_direction' => ['asc']], 'sort_direction'],
        ];
    }

    public static function invalidProductPageSizes(): array
    {
        return [
            'zero' => [0],
            'negative' => [-5],
            'unsupported' => [7],
            'too large' => [1000],
            'fraction' => [5.5],
            'text' => ['all'],
            'array' => [[5]],
        ];
    }
}
