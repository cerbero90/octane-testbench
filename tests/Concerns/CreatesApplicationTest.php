<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Cerbero\OctaneTestbench\TestCase;
use Laravel\Octane\Facades\Octane;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the trait to create applications.
 *
 */
class CreatesApplicationTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        Octane::route('GET', '/sample-octane-route', function () {
            return new Response('sample octane route');
        });
    }

    /**
     * Define web routes setup.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    protected function defineWebRoutes($router)
    {
        $router->get('sample-web-route', function () {
            return response('sample web route');
        });
    }

    /**
     * @test
     */
    public function creates_swoole_application()
    {
        $this->assertSame('swoole', $this->app['config']['octane.server']);

        $this
            ->get('sample-octane-route')
            ->assertOk()
            ->assertSeeText('sample octane route');

        $this
            ->get('sample-web-route')
            ->assertOk()
            ->assertSeeText('sample web route');
    }

    /**
     * @test
     */
    public function creates_roadrunner_application()
    {
        $this->app['config']->set('octane.server', 'roadrunner');
        $this->assertSame('roadrunner', $this->app['config']['octane.server']);

        $this
            ->get('sample-octane-route')
            ->assertOk()
            ->assertSeeText('sample octane route');

        $this
            ->get('sample-web-route')
            ->assertOk()
            ->assertSeeText('sample web route');
    }
}
