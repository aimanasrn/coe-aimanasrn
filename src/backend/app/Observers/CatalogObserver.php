<?php

namespace App\Observers;

use App\CatalogCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class CatalogObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        CatalogCache::invalidate();
    }

    public function deleted(Model $model): void
    {
        CatalogCache::invalidate();
    }
}
