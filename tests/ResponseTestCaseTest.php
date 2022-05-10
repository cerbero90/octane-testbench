<?php

namespace Cerbero\OctaneTestbench;

use Exception;
use Illuminate\Testing\TestResponse;

/**
 * Test the response test case.
 *
 */
class ResponseTestCaseTest extends TestCase
{
    /**
     * @test
     */
    public function asserts_exceptions()
    {
        (new ResponseTestCase($this, new Exception('foo')))
            ->assertException(new Exception('foo'));
    }

    /**
     * @test
     */
    public function calls_macros()
    {
        $called = false;

        TestResponse::macro('foo', function () use (&$called) {
            $called = true;
        });

        $response = (new ResponseTestCase($this, response('foo')))->foo();

        $this->assertTrue($called);
        $this->assertInstanceOf(ResponseTestCase::class, $response);
    }
}
