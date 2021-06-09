<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Cerbero\OctaneTestbench\TestCase;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Exceptions\TaskException;
use Laravel\Octane\Exceptions\TaskTimeoutException;
use Laravel\Octane\Facades\Octane;
use Laravel\Octane\Listeners\ReportException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the trait to provide concurrency support.
 *
 */
class ProvidesConcurrencySupportTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        Octane::route('GET', '/concurrency', function () {
            $results = Octane::concurrently([
                fn () => 1,
                fn () => 2,
                fn () => 3,
            ]);

            return new Response(implode('-', $results));
        });
    }

    /**
     * @test
     */
    public function expects_concurrency()
    {
        $this
            ->expectsConcurrency()
            ->get('concurrency')
            ->assertSee('1-2-3');
    }

    /**
     * @test
     */
    public function expects_custom_concurrency()
    {
        $this
            ->expectsConcurrency(fn ($tasks) => [$tasks[0](), false, $tasks[2]()])
            ->get('concurrency')
            ->assertSee('1--3');
    }

    /**
     * @test
     */
    public function expects_concurrency_results()
    {
        $this
            ->expectsConcurrencyResults([3, 2, 1])
            ->get('concurrency')
            ->assertSee('3-2-1');
    }

    /**
     * @test
     */
    public function expects_concurrency_exception()
    {
        $this
            ->expectsConcurrencyException()
            ->stopsListeningTo(WorkerErrorOccurred::class, ReportException::class)
            ->get('concurrency')
            ->assertException(TaskException::class);
    }

    /**
     * @test
     */
    public function expects_concurrency_timeout()
    {
        $this
            ->expectsConcurrencyTimeout(123)
            ->stopsListeningTo(WorkerErrorOccurred::class, ReportException::class)
            ->get('concurrency')
            ->assertException(TaskTimeoutException::after(123));
    }
}
