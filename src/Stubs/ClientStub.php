<?php

namespace Cerbero\OctaneTestbench\Stubs;

use Cerbero\OctaneTestbench\ResponseTestCase;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Octane\Contracts\Client;
use Laravel\Octane\OctaneResponse;
use Laravel\Octane\RequestContext;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * The client stub.
 *
 */
class ClientStub implements Client
{
    /**
     * The response test case.
     *
     * @var ResponseTestCase
     */
    public $response;

    /**
     * Instantiate the class.
     *
     * @param TestCase $testCase
     */
    public function __construct(protected TestCase $testCase)
    {
    }

    /**
     * Marshal the given request context.
     *
     * @param RequestContext $context
     * @return array
     */
    public function marshalRequest(RequestContext $context): array
    {
        return compact('context');
    }

    /**
     * Record the response.
     *
     * @param RequestContext $context
     * @param OctaneResponse $response
     * @return void
     */
    public function respond(RequestContext $context, OctaneResponse $response): void
    {
        $this->response = new ResponseTestCase($this->testCase, $response->response);
    }

    /**
     * Record the error.
     *
     * @param Throwable $e
     * @param Application $app
     * @param Request $request
     * @param RequestContext $context
     * @return void
     */
    public function error(Throwable $e, Application $app, Request $request, RequestContext $context): void
    {
        while (ob_get_level() > 1) {
            ob_end_clean();
        }

        $this->response = new ResponseTestCase($this->testCase, $e);
    }
}
