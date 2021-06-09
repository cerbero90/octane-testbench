<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Exception;
use Laravel\Octane\Exceptions\TaskExceptionResult;
use Laravel\Octane\Exceptions\TaskTimeoutException;
use Throwable;

/**
 * The trait to provide concurrency support.
 *
 */
trait ProvidesConcurrencySupport
{
    /**
     * Expect Octane to run tasks concurrently
     *
     * @param callable|null $callback
     * @return static
     */
    public function expectsConcurrency(callable $callback = null): static
    {
        return $this->mocks('octane', function ($mock) use ($callback) {
            $mock->shouldReceive('concurrently')->andReturnUsing($callback ?: function (array $tasks) {
                array_walk($tasks, fn (&$task) => $task = $task());
                return $tasks;
            });
        });
    }

    /**
     * Expect Octane to run tasks concurrently
     *
     * @param array ...$results
     * @return static
     */
    public function expectsConcurrencyResults(array ...$results): static
    {
        return $this->mocks('octane', function ($mock) use ($results) {
            $mock->shouldReceive('concurrently')->andReturn(...$results);
        });
    }

    /**
     * Expect Octane to throw an exception when running tasks concurrently
     *
     * @param Throwable|null $e
     * @return static
     */
    public function expectsConcurrencyException(Throwable $e = null): static
    {
        $exception = TaskExceptionResult::from($e ?: new Exception())->getOriginal();

        return $this->mocks('octane', function ($mock) use ($exception) {
            $mock->shouldReceive('concurrently')->andThrow($exception);
        });
    }

    /**
     * Expect Octane to timeout when running tasks concurrently
     *
     * @param int $milliseconds
     * @return static
     */
    public function expectsConcurrencyTimeout(int $milliseconds = 100): static
    {
        return $this->mocks('octane', function ($mock) use ($milliseconds) {
            $mock->shouldReceive('concurrently')->andThrow(TaskTimeoutException::after($milliseconds));
        });
    }
}
