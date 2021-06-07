<?php

namespace Cerbero\OctaneTestbench;

use Cerbero\OctaneTestbench\Providers\OctaneTestbenchServiceProvider;
use Orchestra\Testbench\TestCase;

/**
 * The package test suite.
 *
 */
class OctaneTestbenchTest extends TestCase
{
    /**
     * Retrieve the package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            OctaneTestbenchServiceProvider::class,
        ];
    }
}
