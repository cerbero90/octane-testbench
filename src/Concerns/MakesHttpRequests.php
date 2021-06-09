<?php

namespace Cerbero\OctaneTestbench\Concerns;

use Cerbero\OctaneTestbench\ResponseTestCase;
use Cerbero\OctaneTestbench\Stubs\ApplicationFactoryStub;
use Cerbero\OctaneTestbench\Stubs\ClientStub;
use Cerbero\OctaneTestbench\Stubs\WorkerStub;
use Illuminate\Http\Request;
use Laravel\Octane\Contracts\Client;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * The trait to make HTTP requests.
 *
 */
trait MakesHttpRequests
{
    /**
     * Call the given URI via Laravel Octane.
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string|null $content
     * @return ResponseTestCase
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $request = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            array_merge($files, $this->extractFilesFromDataArray($parameters)),
            array_replace($this->serverVariables, $server),
            $content,
        );

        return $this->sendToOctane(Request::createFromBase($request));
    }

    /**
     * Send a request to Laravel Octane.
     *
     * @param Request $request
     * @return ResponseTestCase
     */
    public function sendToOctane(Request $request): ResponseTestCase
    {
        $factory = new ApplicationFactoryStub($this->app);
        $client = new ClientStub($this);
        $worker = new WorkerStub($factory, $client);

        $this->app->instance(Client::class, $client);

        $worker->boot();

        return $worker->runRequest($request);
    }
}
