<?php

namespace App\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subtotal = 0;
        $items = $this->resource->map(function (CartItem $item) use ($request, &$subtotal): array {
            [$whole, $fraction] = explode('.', $item->product->price);
            $lineTotal = ((int) $whole * 100 + (int) $fraction) * $item->quantity;
            $subtotal += $lineTotal;

            return [
                'product' => (new ShopProductResource($item->product))->resolve($request),
                'quantity' => $item->quantity,
                'max_quantity' => min(CartItem::MAX_QUANTITY, $item->product->stock_quantity),
                'available' => $item->quantity <= $item->product->stock_quantity,
                'line_total' => $this->money($lineTotal),
            ];
        });

        return [
            'items' => $items->all(),
            'item_count' => $this->resource->sum('quantity'),
            'subtotal' => $this->money($subtotal),
            'currency' => 'MYR',
        ];
    }

    private function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
