<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    public const SKU_FALLBACK_CODE = 'PROD';

    /**
     * Create a product and assign its automatic SKU. A temporary unique value fills the
     * required column until the new ID is known.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function createWithGeneratedSku(array $attributes): self
    {
        $product = static::create([...$attributes, 'sku' => 'PENDING-'.Str::uuid()]);
        $product->update(['sku' => $product->generatedSku()]);

        return $product;
    }

    /**
     * Build the automatic SKU: up to four letters from the category name and the zero-padded
     * product ID, for example BOOK-00042. The ID keeps every generated SKU unique.
     */
    public function generatedSku(): string
    {
        $code = Str::of($this->category?->name ?? '')
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->substr(0, 4)
            ->value();

        return ($code !== '' ? $code : self::SKU_FALLBACK_CODE).'-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

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
