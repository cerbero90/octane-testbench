<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Cerbero\OctaneTestbench\TestCase;
use Illuminate\Support\Facades\Cache;
use Laravel\Octane\Facades\Octane;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the trait to interact with cache.
 *
 */
class InteractsWithCacheTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        Octane::route('GET', '/cache', function () {
            Cache::store('octane')->set('foo', 'bar');
            return new Response();
        });
    }

    /**
     * @test
     */
    public function interacts_with_octane_cache()
    {
        $this
            ->assertOctaneCacheMissing('foo')
            ->get('cache')
            ->assertOctaneCacheHas('foo')
            ->assertOk()
            ->assertOctaneCacheHas('foo', 'bar');
    }
}
