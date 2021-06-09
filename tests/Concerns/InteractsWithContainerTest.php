<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Cerbero\OctaneTestbench\Stubs\ServiceStub;
use Cerbero\OctaneTestbench\TestCase;
use Laravel\Octane\Facades\Octane;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the trait to interact with the IoC container.
 *
 */
class InteractsWithContainerTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        Octane::route('GET', '/container', function () {
            $service = resolve(ServiceStub::class);
            return new Response($service->foo());
        });
    }

    /**
     * @test
     */
    public function mocks_a_service()
    {
        $this
            ->mocks(ServiceStub::class, ['foo' => 321])
            ->get('container')
            ->assertSee('321');
    }

    /**
     * @test
     */
    public function partially_mocks_a_service()
    {
        $this
            ->partiallyMocks(ServiceStub::class, ['foo' => 321])
            ->get('container')
            ->assertSee('321');
    }
}
