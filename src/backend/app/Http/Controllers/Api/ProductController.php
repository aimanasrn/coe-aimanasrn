<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'stock' => ['nullable', Rule::in(['all', 'low', 'out'])],
            'per_page' => ['sometimes', 'integer', Rule::in([5, 10, 15, 20, 25, 50])],
            'sort_by' => ['sometimes', Rule::in(['id', 'name', 'price', 'stock_quantity'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
        ]);

        $products = Product::with('category')
            ->forInventory($filters['q'] ?? null, $filters['stock'] ?? 'all')
            ->orderBy($filters['sort_by'] ?? 'id', $filters['sort_direction'] ?? 'asc')
            ->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 10));

        return response()->json(array_merge($products->toArray(), [
            'low_stock_threshold' => Product::LOW_STOCK_THRESHOLD,
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $product = $this->saveProduct($request);

        $product->load('category');

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return response()->json([
            'data' => $product,
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $product = $this->saveProduct($request, $product);

        $product->load('category');

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $images = $product->images ?? [];
        $product->delete();
        Storage::disk(config('filesystems.product_images_disk'))->delete($images);

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function image(Product $product, int $image): StreamedResponse
    {
        $path = ($product->images ?? [])[$image] ?? null;
        abort_unless($path && Storage::disk(config('filesystems.product_images_disk'))->exists($path), 404);

        return Storage::disk(config('filesystems.product_images_disk'))->response($path, null, [
            'Cache-Control' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function saveProduct(Request $request, ?Product $product = null): Product
    {
        $validated = $this->validateProduct($request, $product);
        $originalImages = $product?->images ?? [];
        $retained = $validated['retained_images'] ?? array_keys($originalImages);
        $uploads = $request->file('images', []);

        if (count($retained) + count($uploads) > 6) {
            throw ValidationException::withMessages(['images' => 'You can add up to 6 product images.']);
        }

        $images = array_map(fn (int $index): string => $originalImages[$index], $retained);
        $newImages = [];

        try {
            foreach ($uploads as $upload) {
                $path = $upload->store('product-images', config('filesystems.product_images_disk'));
                if ($path === false) {
                    throw new \RuntimeException('Could not store the product image.');
                }
                $newImages[] = $path;
            }

            $data = Arr::except($validated, ['images', 'retained_images']);
            $data['images'] = [...$images, ...$newImages];
            $product = DB::transaction(function () use ($product, $data): Product {
                if ($product === null) {
                    return Product::create($data);
                }
                $product->update($data);

                return $product;
            });
        } catch (Throwable $error) {
            Storage::disk(config('filesystems.product_images_disk'))->delete($newImages);
            throw $error;
        }

        Storage::disk(config('filesystems.product_images_disk'))->delete(array_values(array_diff($originalImages, $images)));

        return $product;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        if (is_string($request->input('retained_images'))) {
            $request->merge(['retained_images' => json_decode($request->input('retained_images'), true)]);
        }

        return $request->validate([
            'brand' => ['nullable', 'string', 'max:150'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'unit' => ['sometimes', Rule::in(['item', 'pack', 'set', 'box', 'download'])],
            'images' => ['sometimes', 'array', 'max:6'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'retained_images' => ['sometimes', 'array', 'max:6'],
            'retained_images.*' => ['required', 'integer', 'distinct', Rule::in(array_keys($product?->images ?? []))],
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'price' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],
            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],
        ]);
    }
}
