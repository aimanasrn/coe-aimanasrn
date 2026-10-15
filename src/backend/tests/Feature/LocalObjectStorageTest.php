<?php

namespace Tests\Feature;

use App\Models\Product;
use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class LocalObjectStorageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_bucket_and_imports_referenced_images_without_overwriting_or_removing_files(): void
    {
        config(['filesystems.disks.s3.bucket' => 'studymart', 'filesystems.disks.s3.endpoint' => 'http://object-storage:9000']);
        $local = Storage::fake('public');
        $remote = Storage::fake('s3');
        $local->put('product-images/cover.png', 'cover');
        $local->put('product-images/existing.png', 'old');
        $local->put('product-images/unused.png', 'unused');
        $remote->put('product-images/existing.png', 'new');
        Product::factory()->create(['images' => ['product-images/cover.png', 'product-images/existing.png']]);

        $client = Mockery::mock(S3Client::class);
        $client->shouldReceive('doesBucketExistV2')->with('studymart')->twice()->andReturn(false, true);
        $client->shouldReceive('createBucket')->with(['Bucket' => 'studymart'])->once();
        $disk = Mockery::mock(AwsS3V3Adapter::class);
        $disk->shouldReceive('getClient')->twice()->andReturn($client);
        $disk->shouldReceive('exists')->andReturnUsing(fn (string $path): bool => $remote->exists($path));
        $disk->shouldReceive('put')->once()->andReturnUsing(fn (string $path, mixed $stream, array $options): bool => $remote->put($path, $stream, $options));
        Storage::shouldReceive('disk')->with('s3')->andReturn($disk);
        Storage::shouldReceive('disk')->with('public')->andReturn($local);

        $this->artisan('storage:initialize-local --import-local')
            ->expectsOutput('Imported 1 existing product image(s).')->assertSuccessful();
        $this->artisan('storage:initialize-local --import-local')
            ->expectsOutput('Imported 0 existing product image(s).')->assertSuccessful();

        $this->assertSame('cover', $remote->get('product-images/cover.png'));
        $this->assertSame('new', $remote->get('product-images/existing.png'));
        $remote->assertMissing('product-images/unused.png');
        $local->assertExists('product-images/cover.png');
    }

    public function test_rejects_missing_local_storage_configuration(): void
    {
        config(['filesystems.disks.s3.endpoint' => null]);

        $this->artisan('storage:initialize-local')->assertFailed();
    }

    public function test_cannot_initialize_a_production_bucket(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->artisan('storage:initialize-local')->assertFailed();
    }
}
