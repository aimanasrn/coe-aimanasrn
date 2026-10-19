<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'filesystems.profile_photos_disk' => 's3',
            'jwt.secret' => str_repeat('test-secret-', 6),
        ]);
        Storage::fake('s3');
        Storage::fake('public');
    }

    #[DataProvider('accountRoles')]
    public function test_customers_and_admins_can_upload_and_reload_their_own_photo(bool $admin): void
    {
        $user = $admin ? User::factory()->admin()->create() : User::factory()->create();
        $this->authenticate($user);

        $response = $this->upload($this->image())->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', $admin ? 'admin' : 'customer')
            ->assertJsonMissingPath('data.avatar_path');
        $url = $response->json('data.avatar_url');
        $path = $user->fresh()->avatar_path;

        $this->assertStringStartsWith('/api/auth/avatar/', $url);
        $this->assertStringStartsWith("profile-photos/{$user->id}/", $path);
        Storage::disk('s3')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $image = $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $image->headers->get('Cache-Control'));
        $this->resetAuthentication();
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.avatar_url', $url);
        $this->assertSame(0, $user->fresh()->token_version);
    }

    public function test_replacing_a_photo_removes_the_old_file_and_changes_the_image_url(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);
        $first = $this->upload($this->image())->assertOk();
        $firstPath = $user->fresh()->avatar_path;
        $second = $this->upload($this->image())->assertOk();

        $this->assertNotSame($first->json('data.avatar_url'), $second->json('data.avatar_url'));
        Storage::disk('s3')->assertMissing($firstPath);
        Storage::disk('s3')->assertExists($user->fresh()->avatar_path);
        $this->assertCount(1, Storage::disk('s3')->allFiles());
        $this->get($first->json('data.avatar_url'))->assertNotFound();
        $this->resetAuthentication();
        $this->get($second->json('data.avatar_url'))->assertOk();
    }

    public function test_removing_a_photo_only_changes_the_authenticated_account_and_is_repeatable(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['avatar_path' => 'profile-photos/other/kept.png']);
        Storage::disk('s3')->put($other->avatar_path, 'other photo');
        $this->authenticate($user);
        $uploaded = $this->upload($this->image())->assertOk();
        $path = $user->fresh()->avatar_path;

        $this->deleteJson('/api/auth/avatar', ['id' => $other->id])->assertOk()->assertJsonPath('data.avatar_url', null);
        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('s3')->assertMissing($path);
        Storage::disk('s3')->assertExists($other->avatar_path);
        $this->assertSame('profile-photos/other/kept.png', $other->fresh()->avatar_path);
        $this->resetAuthentication();
        $this->get($uploaded->json('data.avatar_url'))->assertNotFound();
        $this->resetAuthentication();
        $this->deleteJson('/api/auth/avatar')->assertOk()->assertJsonPath('data.avatar_url', null);
    }

    public function test_another_account_cannot_read_or_replace_someone_elses_photo(): void
    {
        $owner = User::factory()->create();
        $this->authenticate($owner);
        $photo = $this->upload($this->image())->assertOk();
        $ownerPath = $owner->fresh()->avatar_path;
        $other = User::factory()->create();
        $this->authenticate($other);

        $this->get($photo->json('data.avatar_url'))->assertNotFound();
        $this->resetAuthentication();
        $this->upload($this->image(), ['id' => $owner->id, 'avatar_path' => $ownerPath])
            ->assertOk()->assertJsonPath('data.id', $other->id);
        $this->assertSame($ownerPath, $owner->fresh()->avatar_path);
        $this->assertNotSame($ownerPath, $other->fresh()->avatar_path);
        Storage::disk('s3')->assertExists($ownerPath);
    }

    public function test_rejects_missing_invalid_oversized_and_overly_wide_photos_without_changing_the_saved_photo(): void
    {
        $user = User::factory()->create(['avatar_path' => 'profile-photos/old.png']);
        Storage::disk('s3')->put($user->avatar_path, 'old photo');
        $this->authenticate($user);

        $wide = $this->png();
        $wide = substr_replace($wide, pack('N', 4097), 16, 4);
        $wide = substr_replace($wide, pack('N', crc32(substr($wide, 12, 17))), 29, 4);
        $files = [
            UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml'),
            UploadedFile::fake()->createWithContent('fake.png', '<script>alert(1)</script>'),
            $this->image()->size(2049),
            UploadedFile::fake()->createWithContent('wide.png', $wide),
        ];
        foreach ($files as $file) {
            $this->upload($file)->assertUnprocessable()->assertJsonValidationErrors('avatar');
        }
        $this->postJson('/api/auth/avatar', [])->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->assertSame('profile-photos/old.png', $user->fresh()->avatar_path);
        $this->assertSame(['profile-photos/old.png'], Storage::disk('s3')->allFiles());
    }

    public function test_a_storage_failure_preserves_the_current_photo(): void
    {
        $user = User::factory()->create(['avatar_path' => 'profile-photos/kept.png']);
        $this->authenticate($user);
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('putFileAs')->once()->andReturn(false);
        $disk->shouldNotReceive('delete');
        Storage::shouldReceive('disk')->with('s3')->andReturn($disk);

        $this->upload($this->image())->assertStatus(503);
        $this->assertSame('profile-photos/kept.png', $user->fresh()->avatar_path);
    }

    public function test_profile_edits_and_later_logins_preserve_the_photo(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);
        $photo = $this->upload($this->image())->assertOk();
        $url = $photo->json('data.avatar_url');

        $this->patchJson('/api/auth/profile', ['name' => 'Updated name'])->assertOk()->assertJsonPath('data.avatar_url', $url);
        $this->authenticate($user->fresh());
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.avatar_url', $url);
    }

    public function test_guests_cannot_upload_remove_or_read_profile_photos(): void
    {
        $this->post('/api/auth/avatar', ['avatar' => $this->image()], ['Accept' => 'application/json'])->assertUnauthorized();
        $this->deleteJson('/api/auth/avatar')->assertUnauthorized();
        $this->getJson('/api/auth/avatar/photo.png')->assertUnauthorized();
    }

    public static function accountRoles(): array
    {
        return ['customer' => [false], 'admin' => [true]];
    }

    private function upload(UploadedFile $image, array $extra = []): TestResponse
    {
        $response = $this->post('/api/auth/avatar', ['avatar' => $image, ...$extra], ['Accept' => 'application/json']);
        $this->resetAuthentication();

        return $response;
    }

    private function authenticate(User $user): void
    {
        $this->resetAuthentication();
        $this->withToken(Auth::guard('api')->login($user));
        $this->resetAuthentication();
    }

    private function resetAuthentication(): void
    {
        Auth::forgetGuards();
        $this->app->forgetScopedInstances();
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', $this->png());
    }

    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j1ioAAAAASUVORK5CYII=');
    }
}
