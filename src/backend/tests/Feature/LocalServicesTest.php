<?php

namespace Tests\Feature;

use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LocalServicesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('RUN_LOCAL_SERVICES_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_LOCAL_SERVICES_TESTS=1 to check local Redis and Garage.');
        }
    }

    public function test_redis_can_store_read_and_delete_cache_entries(): void
    {
        $cache = Cache::store('redis');
        $key = 'installation-check:'.Str::uuid();

        try {
            $this->assertTrue($cache->put($key, 'redis-ready', 60));
            $this->assertSame('redis-ready', $cache->get($key));
        } finally {
            $cache->forget($key);
        }

        $this->assertNull($cache->get($key));
    }

    public function test_garage_can_store_read_and_delete_files(): void
    {
        $disk = Storage::disk('s3');
        $path = 'installation-checks/'.Str::uuid().'.txt';

        try {
            $this->assertTrue($disk->put($path, 'garage-ready'));
            $this->assertSame('garage-ready', $disk->get($path));
        } finally {
            $disk->delete($path);
        }

        $this->assertFalse($disk->exists($path));
    }

    public function test_redis_sessions_persist_and_can_be_destroyed(): void
    {
        config(['session.connection' => 'default']);
        $manager = new SessionManager($this->app);
        $session = $manager->driver('redis');
        $id = Str::random(40);
        try {
            $session->setId($id);
            $session->start();
            $session->put('installation-check', 'session-ready');
            $session->save();
            $manager->forgetDrivers();
            $restored = $manager->driver('redis');
            $restored->setId($id);
            $restored->start();
            $this->assertSame('session-ready', $restored->get('installation-check'));
        } finally {
            $session->getHandler()->destroy($id);
        }
        $this->assertSame('', $session->getHandler()->read($id));
    }
}
