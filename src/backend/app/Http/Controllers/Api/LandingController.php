<?php

namespace App\Http\Controllers\Api;

use App\CatalogCache;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * Public, read-only catalog summary for the landing page. It never exposes SKUs,
 * stock numbers, barcodes or anything else from the admin catalog.
 */
class LandingController extends Controller
{
    public const FEATURED_LIMIT = 8;

    public function __invoke(): JsonResponse
    {
        $data = CatalogCache::remember('landing', [], fn (): array => [
            'stats' => [
                'products' => Product::query()->count(),
                'categories' => Category::query()->count(),
                'in_stock' => Product::query()->where('stock_quantity', '>', 0)->count(),
            ],
            'categories' => Category::query()
                ->withCount('products')
                ->orderByDesc('products_count')
                ->orderBy('name')
                ->get(['id', 'name', 'description'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'products_count' => $category->products_count,
                ])
                ->all(),
            'featured' => Product::query()
                ->with('category:id,name')
                ->where('stock_quantity', '>', 0)
                ->orderByRaw("CASE WHEN images IS NULL OR images::text IN ('[]', 'null') THEN 1 ELSE 0 END")
                ->orderByDesc('id')
                ->limit(self::FEATURED_LIMIT)
                ->get()
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category?->name,
                    'image_url' => empty($product->images) ? null : "/api/landing/products/{$product->id}/images/0",
                ])
                ->all(),
        ]);

        return response()->json($data)->header('Cache-Control', 'public, max-age=60');
    }
}
