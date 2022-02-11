<?php

namespace Cerbero\OctaneTestbench;

use Illuminate\Http\Response;
use Illuminate\Testing\Assert;
use Illuminate\Testing\Fluent\Concerns\Debugging;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

/**
 * The response test case.
 *
 */
class ResponseTestCase extends TestResponse
{
    /**
     * The exception thrown in the response.
     *
     * @var Throwable
     */
    public $exception;

    /**
     * Instantiate the class.
     *
     * @param TestCase $testCase
     * @param SymfonyResponse|Throwable $response
     */
    public function __construct(protected TestCase $testCase, SymfonyResponse|Throwable $response)
    {
        if ($response instanceof Throwable) {
            $this->exception = $response;
        } else {
            parent::__construct($this->toIlluminateResponse($response));
        }
    }

    /**
     * Turn the given Symfony response into an Illuminate response
     *
     * @param SymfonyResponse $response
     * @return Response
     */
    protected function toIlluminateResponse(SymfonyResponse $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->allPreserveCase(),
        );
    }

    /**
     * Assert that the thrown exception matches the given exception
     *
     * @param Throwable|string $exception
     * @return static
     */
    public function assertException(Throwable|string $exception): static
    {
        $class = is_string($exception) ? $exception : $exception::class;

        Assert::assertInstanceOf($class, $this->exception);

        return is_string($exception) ? $this : $this->assertExceptionMessage($exception->getMessage());
    }

    /**
     * Assert that the thrown exception message matches the given message
     *
     * @param string $message
     * @return static
     */
    public function assertExceptionMessage(string $message): static
    {
        Assert::assertSame($message, $this->exception->getMessage());

        return $this;
    }

    /**
     * Retrieve the response to inspect.
     *
     * @param string|null  $key
     * @return mixed
     */
    protected function prop(string $key = null)
    {
        $target = $this->exception ?: $this->baseResponse;

        return data_get($target, $key);
    }

    /**
     * Handle dynamic calls into macros or pass missing methods to the response or test case.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            parent::__call($method, $args);
        } elseif ($this->baseResponse && method_exists($this->baseResponse, $method)) {
            return $this->baseResponse->$method(...$args);
        } else {
            $reflection = new ReflectionMethod($this->testCase, $method);
            $reflection->setAccessible(true);
            $reflection->invokeArgs($this->testCase, $args);
        }

        return $this;
    }
}
