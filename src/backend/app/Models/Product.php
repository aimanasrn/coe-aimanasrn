<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id',
    'sku',
    'name',
    'description',
    'price',
    'stock_quantity',
    'brand',
    'barcode',
    'unit',
    'images',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const LOW_STOCK_THRESHOLD = 5;

    protected $hidden = ['images'];

    protected $appends = ['image_urls'];

    /**
     * @return list<string>
     */
    public function getImageUrlsAttribute(): array
    {
        return array_map(
            fn (int $index): string => "/api/products/{$this->id}/images/{$index}",
            array_keys($this->images ?? [])
        );
    }

    #[Scope]
    protected function forInventory(Builder $query, ?string $search = null, string $stock = 'all'): void
    {
        if ($search !== null && trim($search) !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->whereLike('name', '%'.trim($search).'%')
                    ->orWhereLike('sku', '%'.trim($search).'%');
            });
        }

        if ($stock === 'low') {
            $query->whereBetween('stock_quantity', [1, self::LOW_STOCK_THRESHOLD]);
        } elseif ($stock === 'out') {
            $query->where('stock_quantity', 0);
        }
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'images' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
