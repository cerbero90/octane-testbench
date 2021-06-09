<?php

namespace Cerbero\OctaneTestbench;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Symfony\Component\VarDumper\VarDumper;

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
    public function dumps_the_response()
    {
        $result = null;
        $previousHandler = VarDumper::setHandler(function ($var) use (&$result) {
            $result = $var;
        });

        $response = (new ResponseTestCase($this, response('foo')))->dump();

        $this->assertInstanceOf(ResponseTestCase::class, $response);
        $this->assertInstanceOf(Response::class, $result);

        $response = (new ResponseTestCase($this, response('foo')))->dump('original');

        $this->assertInstanceOf(ResponseTestCase::class, $response);
        $this->assertSame('foo', $result);

        VarDumper::setHandler($previousHandler);
    }

    /**
     * @test
     */
    public function dumps_the_exception()
    {
        $result = null;
        $exception = new Exception('foo');
        $exception->bar = 'baz';
        $previousHandler = VarDumper::setHandler(function ($var) use (&$result) {
            $result = $var;
        });

        $response = (new ResponseTestCase($this, $exception))->dump();

        $this->assertInstanceOf(ResponseTestCase::class, $response);
        $this->assertInstanceOf(Exception::class, $result);

        $response = (new ResponseTestCase($this, $exception))->dump('bar');

        $this->assertInstanceOf(ResponseTestCase::class, $response);
        $this->assertSame('baz', $result);

        VarDumper::setHandler($previousHandler);
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
