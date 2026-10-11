<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with('category')
            ->orderBy('id')
            ->paginate(10);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'sku' => [
                'required',
                'string',
                'max:64',
                'unique:products,sku',
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

        $product = Product::create($validated);

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
}