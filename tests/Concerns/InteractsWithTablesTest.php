<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Cerbero\OctaneTestbench\TestCase;
use Laravel\Octane\Facades\Octane;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the trait to interact with tables.
 *
 */
class InteractsWithTablesTest extends TestCase
{
    /**
     * Define routes setup.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        Octane::route('GET', '/tables', function () {
            Octane::table('example')->set('row', ['name' => 'foo', 'votes' => 123]);
            return new Response();
        });
    }

    /**
     * @test
     */
    public function interacts_with_octane_tables()
    {
        $this
            ->assertOctaneTableMissing('example', 'row')
            ->assertOctaneTableCount('example', 0)
            ->get('tables')
            ->assertOctaneTableHas('example', 'row')
            ->assertOctaneTableHas('example', 'row.votes', 123)
            ->assertOctaneTableCount('example', 1);
    }
}
