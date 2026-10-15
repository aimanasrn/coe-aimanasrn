<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.product_images_disk' => 'public']);
    }

    public function test_product_images_use_the_configured_s3_disk(): void
    {
        config(['filesystems.product_images_disk' => 's3']);
        Storage::fake('s3');
        Storage::fake('public');

        $response = $this->post('/api/products', [
            ...$this->payload(),
            'images' => [$this->image()],
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        $product = Product::findOrFail($response->json('data.id'));
        $path = $product->images[0];
        Storage::disk('s3')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $this->get($response->json('data.image_urls.0'))->assertOk();

        $this->deleteJson("/api/products/{$product->id}")->assertOk();
        Storage::disk('s3')->assertMissing($path);
    }

    public function test_creates_product_details_and_serves_uploaded_images(): void
    {
        Storage::fake('public');
        $payload = $this->payload();
        $response = $this->post('/api/products', [
            ...$payload,
            'brand' => 'StudyMart',
            'barcode' => '9789831234567',
            'unit' => 'pack',
            'images' => [$this->image()],
        ], ['Accept' => 'application/json']);

        $response->assertCreated()->assertJsonPaths([
            'data.brand' => 'StudyMart',
            'data.barcode' => '9789831234567',
            'data.unit' => 'pack',
        ])->assertJsonCount(1, 'data.image_urls')->assertJsonMissingPath('data.images');

        $product = Product::findOrFail($response->json('data.id'));
        Storage::disk('public')->assertExists($product->images[0]);
        $this->get($response->json('data.image_urls.0'))
            ->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->get("/api/products/{$product->id}/images/8")->assertNotFound();
    }

    public function test_multipart_updates_can_keep_remove_and_add_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('product-images/front.png', 'front');
        Storage::disk('public')->put('product-images/back.png', 'back');
        $product = Product::factory()->create([
            'images' => ['product-images/front.png', 'product-images/back.png'],
        ]);

        $response = $this->post("/api/products/{$product->id}", [
            ...$this->payload($product),
            '_method' => 'PUT',
            'retained_images' => '[1]',
            'images' => [$this->image()],
            'brand' => 'Updated publisher',
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonCount(2, 'data.image_urls')
            ->assertJsonPath('data.brand', 'Updated publisher');
        $product->refresh();
        $this->assertSame('product-images/back.png', $product->images[0]);
        Storage::disk('public')->assertMissing('product-images/front.png');
        Storage::disk('public')->assertExists($product->images);

        $paths = $product->images;
        $this->post("/api/products/{$product->id}", [
            ...$this->payload($product), '_method' => 'PUT', 'retained_images' => '[]',
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonCount(0, 'data.image_urls');
        Storage::disk('public')->assertMissing($paths);
    }

    public function test_an_update_without_image_fields_preserves_existing_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('product-images/cover.png', 'cover');
        $product = Product::factory()->create(['images' => ['product-images/cover.png']]);

        $this->putJson("/api/products/{$product->id}", $this->payload($product))
            ->assertOk()->assertJsonCount(1, 'data.image_urls');
        Storage::disk('public')->assertExists('product-images/cover.png');
    }

    public function test_rejects_foreign_image_indexes_and_too_many_combined_images(): void
    {
        Storage::fake('public');
        $paths = array_map(fn (int $index): string => "product-images/{$index}.png", range(0, 5));
        foreach ($paths as $path) {
            Storage::disk('public')->put($path, 'image');
        }
        $product = Product::factory()->create(['images' => $paths]);

        $this->putJson("/api/products/{$product->id}", [
            ...$this->payload($product), 'retained_images' => [99],
        ])->assertUnprocessable()->assertJsonValidationErrors('retained_images.0');
        $this->post("/api/products/{$product->id}", [
            ...$this->payload($product), '_method' => 'PUT', 'images' => [$this->image()],
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('images');
        $this->assertSame($paths, $product->fresh()->images);
        $this->assertCount(6, Storage::disk('public')->allFiles('product-images'));
    }

    public function test_rejects_invalid_files_and_oversized_images_without_creating_products(): void
    {
        Storage::fake('public');
        $payload = $this->payload();
        foreach ([UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml'), $this->image()->size(5121)] as $file) {
            $this->post('/api/products', [...$payload, 'images' => [$file]], ['Accept' => 'application/json'])
                ->assertUnprocessable()->assertJsonValidationErrors('images.0');
        }
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_deleting_a_product_also_removes_its_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('product-images/deleted.png', 'image');
        Storage::disk('public')->put('product-images/other.png', 'other');
        $product = Product::factory()->create(['images' => ['product-images/deleted.png']]);

        $this->deleteJson("/api/products/{$product->id}")->assertOk();
        Storage::disk('public')->assertMissing('product-images/deleted.png');
        Storage::disk('public')->assertExists('product-images/other.png');
    }

    public function test_validates_optional_details_and_legacy_products_have_an_empty_gallery(): void
    {
        $product = Product::factory()->create();
        $this->getJson("/api/products/{$product->id}")->assertOk()
            ->assertJsonCount(0, 'data.image_urls')->assertJsonPath('data.unit', 'item');
        $this->putJson("/api/products/{$product->id}", [
            ...$this->payload($product),
            'brand' => str_repeat('a', 151),
            'barcode' => str_repeat('b', 65),
            'unit' => 'invalid',
        ])->assertUnprocessable()->assertJsonValidationErrors(['brand', 'barcode', 'unit']);
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('cover.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j1ioAAAAASUVORK5CYII='
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(?Product $product = null): array
    {
        return [
            'name' => $product?->name ?? 'Image workbook',
            'sku' => $product?->sku ?? 'IMAGE-001',
            'category_id' => $product?->category_id ?? Category::factory()->create()->id,
            'price' => $product?->price ?? '12.90',
            'stock_quantity' => $product?->stock_quantity ?? 10,
            'description' => $product?->description,
        ];
    }
}
