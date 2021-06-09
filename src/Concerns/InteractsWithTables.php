<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Laravel\Octane\Facades\Octane;

/**
 * The trait to interact with Octane tables.
 *
 */
trait InteractsWithTables
{
    /**
     * Assert that the given Laravel Octane table has the provided row
     *
     * @param string $table
     * @param string $row
     * @param mixed|null $value
     * @return static
     */
    public function assertOctaneTableHas(string $table, string $row, mixed $value = null): static
    {
        if ($value === null) {
            $actual = Octane::table($table)->exist($row);
            $this->assertTrue($actual, "The row [$row] is not present in the Octane table [$table]");
        } else {
            [$row, $column] = explode('.', $row);
            $actual = Octane::table($table)->get($row, $column);
            $this->assertSame($value, $actual, "Expected value [$value] in column [$column], got [$actual] instead");
        }

        return $this;
    }

    /**
     * Assert that the given Laravel Octane table doesn't have the provided row
     *
     * @param string $table
     * @param string $row
     * @return static
     */
    public function assertOctaneTableMissing(string $table, string $row): static
    {
        $actual = Octane::table($table)->exist($row);

        $this->assertFalse($actual, "The row [$row] is present in the Octane table [$table]");

        return $this;
    }

    /**
     * Assert that the given Laravel Octane table contains the provided number of rows
     *
     * @param string $table
     * @param int $count
     * @return static
     */
    public function assertOctaneTableCount(string $table, int $count): static
    {
        $actual = Octane::table($table)->count();

        $this->assertSame($count, $actual, "The rows in the Octane table [$table] are $actual");

        return $this;
    }
}
