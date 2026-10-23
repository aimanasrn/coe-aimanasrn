<?php

namespace App\Http\Controllers\Api;

use App\CatalogCache;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->hasAny(['q', 'products', 'page', 'per_page', 'sort_by', 'sort_direction'])) {
            return response()->json(CatalogCache::remember('admin-categories', [], fn (): array => [
                'data' => Category::query()->withCount('products')->orderBy('name')->get()->toArray(),
            ]));
        }

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'products' => ['nullable', Rule::in(['all', 'with', 'empty'])],
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
            'per_page' => ['sometimes', 'integer', Rule::in([5, 10, 15, 20, 25, 50])],
            'sort_by' => ['sometimes', Rule::in(['name', 'products_count'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(CatalogCache::remember('admin-category-pages', array_merge($filters, [
            'page' => $request->integer('page', 1),
            'url' => $request->url(),
        ]), function () use ($filters): array {
            $categories = Category::query()->select(['id', 'name', 'description'])->withCount('products');
            $search = trim($filters['q'] ?? '');

            if ($search !== '') {
                $categories->where(function (Builder $query) use ($search): void {
                    $query->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('description', '%'.$search.'%');
                });
            }

            if (($filters['products'] ?? 'all') === 'with') {
                $categories->whereHas('products');
            } elseif (($filters['products'] ?? 'all') === 'empty') {
                $categories->whereDoesntHave('products');
            }

            return $categories
                ->orderBy($filters['sort_by'] ?? 'name', $filters['sort_direction'] ?? 'asc')
                ->orderBy('id')
                ->paginate((int) ($filters['per_page'] ?? 5))
                ->toArray();
        }));
    }

    public function store(Request $request): JsonResponse
    {
        $category = Category::create($this->validateCategory($request));

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category->loadCount('products'),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => $category->loadCount('products'),
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $category->update($this->validateCategory($request, $category));

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category->loadCount('products'),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        return DB::transaction(function () use ($category): JsonResponse {
            $category = Category::query()->lockForUpdate()->findOrFail($category->id);

            if ($category->products()->exists()) {
                return response()->json([
                    'message' => 'This category still contains products. Move them to another category before deleting it.',
                ], 409);
            }

            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully.',
            ]);
        });
    }

    /**
     * @return array{name: string, description?: string|null}
     */
    private function validateCategory(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
