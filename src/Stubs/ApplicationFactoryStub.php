<?php

namespace Cerbero\OctaneTestbench\Stubs;

use Illuminate\Foundation\Application;
use Laravel\Octane\ApplicationFactory;

/**
 * The application factory stub.
 *
 */
class ApplicationFactoryStub extends ApplicationFactory
{
    /**
     * Instantiate the class.
     *
     * @param Application $app
     */
    public function __construct(protected Application $app)
    {
        parent::__construct($app->basePath());
    }

    /**
     * Create a new application instance.
     *
     * @param array $initialInstances
     * @return Application
     */
    public function createApplication(array $initialInstances = []): Application
    {
        return $this->app;
    }
}
