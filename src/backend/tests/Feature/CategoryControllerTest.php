<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
}
