<?php

namespace App\Http\Controllers\Api;

use App\CatalogCache;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CatalogCache::remember('admin-categories', [], fn (): array => [
            'data' => Category::query()->withCount('products')->orderBy('name')->get()->toArray(),
        ]));
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
