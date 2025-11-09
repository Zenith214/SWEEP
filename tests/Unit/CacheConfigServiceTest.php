<?php

namespace Tests\Unit;

use App\Services\CacheConfigService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheConfigServiceTest extends TestCase
{
    /**
     * Test cache key generation.
     */
    public function test_generates_unique_cache_keys(): void
    {
        $key1 = CacheConfigService::generateKey(
            CacheConfigService::PREFIX_COLLECTION,
            ['start' => '2025-01-01', 'end' => '2025-01-31']
        );

        $key2 = CacheConfigService::generateKey(
            CacheConfigService::PREFIX_COLLECTION,
            ['start' => '2025-02-01', 'end' => '2025-02-28']
        );

        $this->assertNotEquals($key1, $key2);
        $this->assertStringContainsString(CacheConfigService::PREFIX_COLLECTION, $key1);
    }

    /**
     * Test TTL configuration.
     */
    public function test_returns_correct_ttl_for_metric_types(): void
    {
        $this->assertEquals(300, CacheConfigService::getTTL('realtime'));
        $this->assertEquals(900, CacheConfigService::getTTL('historical'));
        $this->assertEquals(1800, CacheConfigService::getTTL('static'));
        $this->assertNull(CacheConfigService::getTTL('preferences'));
    }

    /**
     * Test cache invalidation.
     */
    public function test_invalidates_collection_metrics_cache(): void
    {
        // Set a test cache value
        $testKey = CacheConfigService::TAG_COLLECTION_METRICS;
        Cache::put($testKey, 'test_value', 60);
        
        $this->assertEquals('test_value', Cache::get($testKey));

        // Invalidate
        CacheConfigService::invalidateCollectionMetrics();

        // Verify it's cleared
        $this->assertNull(Cache::get($testKey));
    }
}
