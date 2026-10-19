<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'brand' => $this->brand,
            'unit' => $this->unit,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'image_urls' => array_map(
                fn (int $index): string => "/api/shop/products/{$this->id}/images/{$index}",
                array_keys($this->images ?? [])
            ),
        ];
    }
}
