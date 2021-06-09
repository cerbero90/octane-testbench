<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Laravel\Octane\ApplicationFactory;
use Laravel\Octane\Events\RequestReceived;
use Mockery;

/**
 * The trait to create an Octane application.
 *
 */
trait CreatesApplication
{
    /**
     * Create the Laravel Octane application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $factory = new ApplicationFactory($this->getBasePath());
        $bindings = $this->getInitialServerBindings();
        $app = $factory->warm($factory->createApplication($bindings));

        $app->events->forget(RequestReceived::class);

        return $app;
    }

    /**
     * Retrieve the application base path.
     *
     * @return string
     */
    protected function getBasePath(): string
    {
        return realpath(dirname(__DIR__, 5));
    }

    /**
     * Retrieve the initial services to bind for the server in use
     *
     * @return array
     */
    protected function getInitialServerBindings(): array
    {
        $serverState = [];
        $serverState['octaneConfig'] = require $this->getBasePath() . '/config/octane.php';

        if ($serverState['octaneConfig']['server'] == 'roadrunner') {
            return [];
        }

        $workerState = (object) ['tables' => require $this->getOctaneBinPath() . '/createSwooleTables.php'];

        return [
            'octane.cacheTable' => require $this->getOctaneBinPath() . '/createSwooleCacheTable.php',
            'Swoole\Http\Server' => Mockery::spy('Swoole\Http\Server'),
            'Laravel\Octane\Swoole\WorkerState' => $workerState,
        ];
    }

    /**
     * Retrieve the Octane bin path.
     *
     * @return string
     */
    protected function getOctaneBinPath(): string
    {
        return $this->getBasePath() . '/vendor/laravel/octane/bin';
    }
}
