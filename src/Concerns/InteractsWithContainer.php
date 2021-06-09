<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Mockery;

/**
 * The trait to interact with the IoC container.
 *
 */
trait InteractsWithContainer
{
    /**
     * Mock the given bound target
     *
     * @param string $target
     * @param mixed ...$parameters
     * @return static
     */
    public function mocks(string $target, ...$parameters): static
    {
        $mock = Mockery::mock($this->app[$target], ...$parameters);

        $this->app->instance($target, $mock);

        return $this;
    }

    /**
     * Partially mock the given bound target
     *
     * @param string $target
     * @param mixed ...$parameters
     * @return static
     */
    public function partiallyMocks(string $target, ...$parameters): static
    {
        $mock = Mockery::mock($this->app[$target], ...$parameters)->makePartial();

        $this->app->instance($target, $mock);

        return $this;
    }
}
