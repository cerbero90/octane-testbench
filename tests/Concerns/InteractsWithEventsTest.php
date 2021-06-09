<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Cerbero\OctaneTestbench\TestCase;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Facades\Octane;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToHttpKernel;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToPipelineHub;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the trait to interact with events.
 *
 */
class InteractsWithEventsTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        Octane::route('GET', '/events', fn () => new Response());
    }

    /**
     * @test
     */
    public function registers_listeners_for_an_octane_event()
    {
        $numListeners = count($this->app->events->getListeners(RequestReceived::class));

        $this
            ->listensTo(
                RequestReceived::class,
                GiveNewApplicationInstanceToHttpKernel::class,
                GiveNewApplicationInstanceToPipelineHub::class,
            )
            ->get('events');

        $this->assertCount($numListeners + 2, $this->app->events->getListeners(RequestReceived::class));
    }

    /**
     * @test
     */
    public function exclude_listeners_for_the_request_received_event()
    {
        $numListeners = count($this->app->events->getListeners(WorkerErrorOccurred::class));

        $this
            ->stopsListeningTo(
                WorkerErrorOccurred::class,
                ReportException::class,
                StopWorkerIfNecessary::class,
            )
            ->get('events');

        $this->assertCount($numListeners - 2, $this->app->events->getListeners(WorkerErrorOccurred::class));
    }
}
