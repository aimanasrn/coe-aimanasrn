<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $this->normalizeEmail($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254', Rule::unique(User::class)],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(8)],
            'role' => ['prohibited'],
            'token_version' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return $this->tokenResponse($this->guard()->login($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $this->normalizeEmail($request);
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $token = $this->guard()->attempt($credentials);

        if (! $token) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return $this->tokenResponse($token);
    }

    public function me(): JsonResponse
    {
        return response()->json(['data' => new UserResource($this->guard()->user())]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $this->normalizeEmail($request);
        $user = $request->user('api');
        $changingEmail = $request->has('email') && $request->input('email') !== $user->email;
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:254', Rule::unique(User::class)->ignore($user->id)],
            'password' => ['sometimes', 'required', 'string', 'max:72', 'confirmed', Password::min(8)],
            'current_password' => [Rule::requiredIf($changingEmail || $request->has('password')), 'nullable', 'string'],
            'role' => ['prohibited'],
            'token_version' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
        ]);

        $user = DB::transaction(function () use ($user, $validated, $changingEmail): User {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            if (($changingEmail || isset($validated['password'])) && ! Hash::check($validated['current_password'] ?? '', $user->password)) {
                throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
            }

            $user->fill(array_intersect_key($validated, array_flip(['name', 'email', 'password'])));

            if ($changingEmail) {
                $user->email_verified_at = null;
            }

            if (isset($validated['password'])) {
                $user->token_version++;
            }

            $user->save();

            return $user;
        });

        $this->guard()->invalidate();

        return $this->tokenResponse($this->guard()->login($user));
    }

    public function refresh(): JsonResponse
    {
        $guard = $this->guard();

        return $this->tokenResponse($guard->claims($guard->user()->getJWTCustomClaims())->refresh());
    }

    public function logout(): JsonResponse
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function guard(): JWTGuard
    {
        return Auth::guard('api');
    }

    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
    }

    private function tokenResponse(string $token, int $status = 200): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'data' => new UserResource($this->guard()->user()),
        ], $status)->header('Cache-Control', 'no-store');
    }
}
