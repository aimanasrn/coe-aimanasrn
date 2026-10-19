<?php

namespace App\Http\Controllers\Api;

use App\CatalogCache;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'stock' => ['nullable', Rule::in(['all', 'low', 'out'])],
        ]);

        return response()->json(CatalogCache::remember('dashboard', $filters, function () use ($filters): array {
            $inventory = Product::query()->selectRaw(
                'COUNT(*) AS total_products,
             COALESCE(SUM(stock_quantity), 0) AS stock_units,
             COALESCE(SUM(price * stock_quantity), 0.00) AS stock_value,
             COALESCE(SUM(CASE WHEN stock_quantity > ? THEN 1 ELSE 0 END), 0) AS in_stock,
             COALESCE(SUM(CASE WHEN stock_quantity BETWEEN 1 AND ? THEN 1 ELSE 0 END), 0) AS low_stock,
             COALESCE(SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END), 0) AS out_of_stock',
                [Product::LOW_STOCK_THRESHOLD, Product::LOW_STOCK_THRESHOLD]
            )->first();

            $products = Product::query()->forInventory($filters['q'] ?? null, $filters['stock'] ?? 'all');
            $filteredTotal = (clone $products)->count();

            return [
                'data' => [
                    'summary' => [
                        'total_products' => (int) $inventory->total_products,
                        'total_categories' => Category::query()->count(),
                        'stock_units' => (int) $inventory->stock_units,
                        'stock_value' => (string) $inventory->stock_value,
                    ],
                    'stock_counts' => [
                        'in_stock' => (int) $inventory->in_stock,
                        'low_stock' => (int) $inventory->low_stock,
                        'out_of_stock' => (int) $inventory->out_of_stock,
                    ],
                    'low_stock_threshold' => Product::LOW_STOCK_THRESHOLD,
                    'categories' => Category::query()->select(['id', 'name'])
                        ->withCount('products')->orderByDesc('products_count')->orderBy('name')->limit(4)->get()->toArray(),
                    'products' => $products->with('category:id,name')->latest('created_at')->orderByDesc('id')->limit(6)->get()->toArray(),
                    'filtered_total' => $filteredTotal,
                ],
            ];
        }));
    }
}
