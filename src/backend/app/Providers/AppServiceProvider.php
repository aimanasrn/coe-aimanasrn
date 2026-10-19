<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Observers\CatalogObserver;
use App\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(CatalogObserver::class);
        Category::observe(CatalogObserver::class);
        Gate::define('shop', fn (User $user): bool => $user->role === UserRole::Customer);
        Gate::define('manage-catalog', fn (User $user): bool => $user->role === UserRole::Admin);
    }
}
