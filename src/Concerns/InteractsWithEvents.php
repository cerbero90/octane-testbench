<?php

namespace Cerbero\OctaneTestbench\Concerns;

/**
 * The trait to interact with events.
 *
 */
trait InteractsWithEvents
{
    /**
     * Register listeners for the given event
     *
     * @param string $event
     * @param mixed ...$listeners
     * @return static
     */
    public function listensTo(string $event, mixed ...$listeners): static
    {
        foreach ($listeners as $listener) {
            $this->app->events->listen($event, $listener);
        }

        return $this;
    }

    /**
     * Exclude listeners for the given event
     *
     * @param string $event
     * @param mixed ...$listeners
     * @return static
     */
    public function stopsListeningTo(string $event, mixed ...$listeners): static
    {
        $this->app->events->forget($event);

        $listeners = array_diff($this->app->config['octane.listeners'][$event], $listeners);

        return $this->listensTo($event, ...$listeners);
    }
}
