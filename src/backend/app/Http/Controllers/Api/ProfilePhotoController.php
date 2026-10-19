<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProfilePhotoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ]);

        $user = $request->user('api');
        $path = $request->file('avatar')->store("profile-photos/{$user->id}", config('filesystems.profile_photos_disk'));
        abort_if($path === false, 503, 'Could not save your profile photo. Please try again.');

        return $this->replacePhoto($user, $path);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->replacePhoto($request->user('api'), null);
    }

    public function show(Request $request, string $filename): StreamedResponse
    {
        $path = $request->user('api')->avatar_path;
        $disk = Storage::disk(config('filesystems.profile_photos_disk'));

        abort_unless($path && basename($path) === $filename && $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function replacePhoto(User $user, ?string $path): JsonResponse
    {
        $disk = Storage::disk(config('filesystems.profile_photos_disk'));
        $previousPath = null;

        try {
            $user = DB::transaction(function () use ($user, $path, &$previousPath): User {
                $user = User::query()->lockForUpdate()->findOrFail($user->id);
                $previousPath = $user->avatar_path;
                $user->avatar_path = $path;
                $user->save();

                return $user;
            });
        } catch (Throwable $error) {
            if ($path !== null) {
                $disk->delete($path);
            }
            throw $error;
        }

        if ($previousPath !== null) {
            $disk->delete($previousPath);
        }

        return response()->json(['data' => new UserResource($user)])->header('Cache-Control', 'no-store');
    }
}
