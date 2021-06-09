<?php

namespace Cerbero\OctaneTestbench\Stubs;

use Cerbero\OctaneTestbench\ResponseTestCase;
use Illuminate\Http\Request;
use Laravel\Octane\RequestContext;
use Laravel\Octane\Worker;

/**
 * The worker stub.
 *
 */
class WorkerStub extends Worker
{
    /**
     * Run the given request
     *
     * @param Request $request
     * @return ResponseTestCase
     */
    public function runRequest(Request $request): ResponseTestCase
    {
        $this->app->instance('request', $request);

        $data = $this->client->marshalRequest(new RequestContext(compact('request')));

        $this->handle($data['context']->request, $data['context']);

        return $this->client->response;
    }
}
