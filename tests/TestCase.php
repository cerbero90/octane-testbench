<?php

namespace Cerbero\OctaneTestbench;

use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * The package test suite.
 *
 */
abstract class TestCase extends OrchestraTestCase
{
    use TestsOctaneApplication;

    /**
     * Setup the test suite.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        $basePath = static::applicationBasePath();
        $config = dirname($basePath, 3) . '/laravel/octane/config/octane.php';

        copy($config, $basePath . '/config/octane.php');
    }

    /**
     * Retrieve the application base path.
     *
     * @return string
     */
    protected function getBasePath(): string
    {
        return parent::getBasePath();
    }

    /**
     * Retrieve the Octane bin path.
     *
     * @return string
     */
    protected function getOctaneBinPath(): string
    {
        return __DIR__ . '/bin';
    }
}
