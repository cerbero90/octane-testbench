<?php

namespace Cerbero\OctaneTestbench\Concerns;

/**
 * The trait to interact with the Octane cache.
 *
 */
trait InteractsWithCache
{
    /**
     * Assert that the Laravel Octane cache has the given key set
     *
     * @param string $key
     * @param mixed|null $value
     * @return static
     */
    public function assertOctaneCacheHas(string $key, mixed $value = null): static
    {
        $cache = $this->app->cache->store('octane');

        if ($value === null) {
            $this->assertTrue($cache->has($key), "The Octane cache doesn't have the [$key] key set");
        } else {
            $actual = $cache->get($key);
            $this->assertSame($value, $actual, "Expected cached value [$value], got [$actual] instead");
        }

        return $this;
    }

    /**
     * Assert that the Laravel Octane cache doesn't have the given key set
     *
     * @param string $key
     * @return static
     */
    public function assertOctaneCacheMissing(string $key): static
    {
        $cache = $this->app->cache->store('octane');

        $this->assertFalse($cache->has($key), "The Octane cache has the [$key] key set");

        return $this;
    }
}
