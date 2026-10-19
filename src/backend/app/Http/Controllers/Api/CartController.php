<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->cart($request);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.CartItem::MAX_QUANTITY],
        ]);

        DB::transaction(function () use ($request, $data): void {
            User::query()->whereKey($request->user('api')->id)->lockForUpdate()->firstOrFail();
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $item = CartItem::query()->firstOrNew([
                'user_id' => $request->user('api')->id,
                'product_id' => $product->id,
            ]);
            $quantity = ($item->quantity ?? 0) + $data['quantity'];
            $this->checkQuantity($product, $quantity);
            $item->quantity = $quantity;
            $item->save();
        });

        return $this->cart($request, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.CartItem::MAX_QUANTITY],
        ]);

        DB::transaction(function () use ($request, $product, $data): void {
            User::query()->whereKey($request->user('api')->id)->lockForUpdate()->firstOrFail();
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $item = CartItem::query()->where('user_id', $request->user('api')->id)
                ->where('product_id', $product->id)->firstOrFail();
            $this->checkQuantity($product, $data['quantity']);
            $item->update(['quantity' => $data['quantity']]);
        });

        return $this->cart($request);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        DB::transaction(function () use ($request, $product): void {
            User::query()->whereKey($request->user('api')->id)->lockForUpdate()->firstOrFail();
            CartItem::query()->where('user_id', $request->user('api')->id)
                ->where('product_id', $product->id)->delete();
        });

        return $this->cart($request);
    }

    public function clear(Request $request): JsonResponse
    {
        DB::transaction(function () use ($request): void {
            User::query()->whereKey($request->user('api')->id)->lockForUpdate()->firstOrFail();
            CartItem::query()->where('user_id', $request->user('api')->id)->delete();
        });

        return $this->cart($request);
    }

    private function checkQuantity(Product $product, int $quantity): void
    {
        if ($quantity > CartItem::MAX_QUANTITY) {
            throw ValidationException::withMessages(['quantity' => 'You can add up to 99 of each product.']);
        }
        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => $product->stock_quantity === 0
                    ? 'This product is out of stock.'
                    : "Only {$product->stock_quantity} units are currently available.",
            ]);
        }
    }

    private function cart(Request $request, int $status = 200): JsonResponse
    {
        $items = CartItem::query()->where('user_id', $request->user('api')->id)
            ->with('product.category')->orderBy('id')->get()
            ->filter(fn (CartItem $item): bool => $item->product !== null)->values();

        return response()->json(['data' => (new CartResource($items))->resolve($request)], $status)
            ->header('Cache-Control', 'private, no-store');
    }
}
