<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['name' => 'Books'],
            ['description' => 'Educational books and workbooks.']
        );

        Product::firstOrCreate(
            ['sku' => 'BOOK-MATH-001'],
            [
                'category_id' => $category->id,
                'name' => 'Mathematics Workbook',
                'description' => 'Practice exercises for primary school students.',
                'price' => '15.90',
                'stock_quantity' => 20,
            ]
        );
    }
}
