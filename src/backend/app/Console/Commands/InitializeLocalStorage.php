<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

#[Signature('storage:initialize-local {--import-local : Copy existing product images from the public disk}')]
#[Description('Create the local S3 bucket and optionally import existing product images')]
class InitializeLocalStorage extends Command
{
    public function handle(): int
    {
        if (! $this->laravel->environment('local', 'testing')) {
            $this->error('This command is only available in the local environment.');

            return self::FAILURE;
        }

        $bucket = config('filesystems.disks.s3.bucket');
        if (! $bucket || ! config('filesystems.disks.s3.endpoint')) {
            $this->error('Configure AWS_BUCKET and AWS_ENDPOINT for local object storage first.');

            return self::FAILURE;
        }

        $disk = Storage::disk('s3');
        $client = $disk->getClient();
        if (! $client->doesBucketExistV2($bucket)) {
            $client->createBucket(['Bucket' => $bucket]);
        }

        $this->info("Local object storage bucket [{$bucket}] is ready.");

        if ($this->option('import-local')) {
            $this->info('Imported '.$this->importImages($disk).' existing product image(s).');
        }

        return self::SUCCESS;
    }

    private function importImages(FilesystemAdapter $disk): int
    {
        $local = Storage::disk('public');
        $imported = 0;

        foreach (Product::query()->select(['id', 'images'])->lazyById() as $product) {
            foreach ($product->images ?? [] as $path) {
                if ($disk->exists($path) || ! $local->exists($path)) {
                    continue;
                }

                $stream = $local->readStream($path);
                if ($stream === null || $stream === false) {
                    throw new RuntimeException("Could not read local image [{$path}].");
                }

                try {
                    if (! $disk->put($path, $stream, ['mimetype' => $local->mimeType($path)])) {
                        throw new RuntimeException("Could not import local image [{$path}].");
                    }
                } finally {
                    fclose($stream);
                }

                $imported++;
            }
        }

        return $imported;
    }
}
