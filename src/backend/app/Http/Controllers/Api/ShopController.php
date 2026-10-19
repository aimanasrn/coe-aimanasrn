<?php

namespace App\Http\Controllers\Api;

use App\CatalogCache;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'integer', 'min:1'],
            'sort' => ['sometimes', Rule::in(['newest', 'price_asc', 'price_desc', 'name'])],
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ]);

        $data = CatalogCache::remember('shop-products', $filters, function () use ($filters, $request): array {
            $query = Product::query()->with('category');
            if (! empty($filters['q'])) {
                $query->forInventory($filters['q']);
            }
            if (! empty($filters['category'])) {
                $query->where('category_id', $filters['category']);
            }
            $sorts = ['newest' => ['id', 'desc'], 'price_asc' => ['price', 'asc'], 'price_desc' => ['price', 'desc'], 'name' => ['name', 'asc']];
            [$column, $direction] = $sorts[$filters['sort'] ?? 'newest'];
            $products = $query->orderBy($column, $direction)->orderBy('id')->paginate(12, ['*'], 'page', $filters['page'] ?? 1);

            return [
                'data' => ShopProductResource::collection($products->getCollection())->resolve($request),
                'total' => $products->total(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => 12,
            ];
        });

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $data = CatalogCache::remember('shop-product', ['id' => $product->id], function () use ($product, $request): array {
            return ['data' => (new ShopProductResource($product->load('category')))->resolve($request)];
        });

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }

    public function categories(): JsonResponse
    {
        return response()->json(CatalogCache::remember('shop-categories', [], fn (): array => [
            'data' => Category::query()->orderBy('name')->get(['id', 'name'])->toArray(),
        ]))->header('Cache-Control', 'private, no-store');
    }
}
