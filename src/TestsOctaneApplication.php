<?php

namespace Cerbero\OctaneTestbench;

/**
 * The trait to test an application powered by Laravel Octane.
 *
 */
trait TestsOctaneApplication
{
    use Concerns\CreatesApplication;
    use Concerns\InteractsWithCache;
    use Concerns\InteractsWithContainer;
    use Concerns\InteractsWithEvents;
    use Concerns\InteractsWithTables;
    use Concerns\MakesHttpRequests;
    use Concerns\ProvidesConcurrencySupport;
}
