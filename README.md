# ⛽ Octane Testbench

[![Author][ico-author]][link-author]
[![PHP Version][ico-php]][link-php]
[![Laravel Version][ico-laravel]][link-laravel]
[![Octane Compatibility][ico-octane]][link-octane]
[![Build Status][ico-actions]][link-actions]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![PSR-12][ico-psr12]][link-psr12]
[![Total Downloads][ico-downloads]][link-downloads]

Set of utilities to test Laravel applications powered by Octane.


## Install

Via Composer:

``` bash
composer require cerbero/octane-testbench
```

In `tests/TestCase.php`, use the `TestsOctaneApplication` trait:

```php
use Cerbero\OctaneTestbench\TestsOctaneApplication;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use TestsOctaneApplication;
}
```

Now all tests extending this class, even previously created tests, can run on Octane.

## Usage

* [Requests and responses](#requests-and-responses)
* [Concurrency](#concurrency)
* [Cache](#cache)
* [Tables](#tables)
* [Events](#events)
* [Container](#container)

In a nutshell, Octane Testbench

1. is progressive: existing tests keep working, making Octane adoption easier for existing Laravel apps
1. stubs out workers and clients: tests don't need a Swoole or RoadRunner server to run
1. preserves the application state after a request, so assertions can be performed after the response
1. offers fluent assertions tailored to Octane:

```php
public function test_octane_application()
{
    $this
        ->assertOctaneCacheMissing('foo')
        ->assertOctaneTableMissing('example', 'row')
        ->assertOctaneTableCount('example', 0)
        ->expectsConcurrencyResults([1, 2, 3])
        ->get('octane/route')
        ->assertOk()
        ->assertOctaneCacheHas('foo', 'bar')
        ->assertOctaneTableHas('example', 'row.votes', 123)
        ->assertOctaneTableCount('example', 1);
}
```


### Requests and responses

HTTP requests are performed with the [same methods](https://laravel.com/docs/http-tests#making-requests) we would normally call to test any Laravel application, except they will work for both standard and Octane routes:

```php
Route::get('web-route', fn () => 123);

Octane::route('POST', '/octane-route', fn () => new Response('foo'));


public function test_web_route()
{
    $this->get('web-route')->assertOk()->assertSee('123');
}

public function test_octane_route()
{
    $this->post('octane-route')->assertOk()->assertSee('foo');
}
```

Responses are wrapped in a `ResponseTestCase` instance that lets us call [response assertions](https://laravel.com/docs/http-tests#available-assertions), any assertion of the [Laravel testing suite](https://laravel.com/docs/testing) and the following exception assertions:

```php
$this
    ->get('failing-route')
    ->assertException(Exception::class) // assert exception instance
    ->assertException(new Exception('message')) // assert exception instance and message
    ->assertExceptionMessage('message'); // assert exception message
```

Furthermore, responses and exceptions can be debugged by calling the `dd()` and `dump()` methods:

```php
$this
    ->get('failing-route')
    ->dump() // dump the whole response/exception
    ->dump('original') // dump only a specific property
    ->dd() // dump-and-die the whole response/exception
    ->dd('headers'); // dump-and-die only a specific property
```


### Concurrency

[Concurrency](https://laravel.com/docs/octane#concurrent-tasks) works fine during tests. However, PHP 8 forbids the serialization of reflections (hence mocks) and concurrent tasks are serialized before being dispatched. If tasks involve mocks, we can fake the concurrency:

```php
// code to test:
Octane::concurrently([
    fn () => $mockedService->run(),
    fn () => 123,
]);

// test:
$this
    ->mocks(Service::class, ['run' => 'foo'])
    ->expectsConcurrency()
    ->get('route');
```
In the test above we are running tasks sequentially without serialization, allowing mocked methods to be executed (we will see more about [mocks](#container) later).

If we need more control over how concurrent tasks run, we can pass a closure to `expectsConcurrency()`. For example, the test below runs only the first task:

```php
$this
    ->expectsConcurrency(fn (array $tasks) => [ $tasks[0]() ])
    ->get('route');
```

To manipulate the results of concurrent tasks, we can use `expectsConcurrencyResults()`:

```php
$this
    ->expectsConcurrencyResults([$firstTaskResult, $secondTaskResult])
    ->get('route');
```

Finally we can make concurrent tasks fail to test our code when something wrong happens:

```php
$this
    ->expectsConcurrencyException() // tasks fail due to a generic exception
    ->get('route');

$this
    ->expectsConcurrencyException(new Exception('message')) // tasks fail due to a specific exception
    ->get('route');

$this
    ->expectsConcurrencyTimeout() // tasks fail due to a timeout
    ->get('route');
```


### Cache

Octane Testbench provides the following assertions to test the [Octane cache](https://laravel.com/docs/octane#the-octane-cache):

```php
$this
    ->assertOctaneCacheMissing($key) // assert the key is not set
    ->get('route')
    ->assertOctaneCacheHas($key) // assert the key is set
    ->assertOctaneCacheHas($key, $value); // assert the key has the given value
```


### Tables

[Octane tables](https://laravel.com/docs/octane#tables) can be tested with the following assertions:

```php
$this
    ->assertOctaneTableMissing($table, $row) // assert the row is not present in the table
    ->assertOctaneTableCount($table, 0) // assert the number of rows in the table
    ->get('route')
    ->assertOctaneTableHas($table, $row) // assert the row is present in the table
    ->assertOctaneTableHas($table, 'row.column' $value) // assert the column in the row has the given value
    ->assertOctaneTableCount($table, 1);
```


### Events

By default listeners for the Octane `RequestReceived` event are disabled to perform assertions on the application state. However we can register listeners for any Octane event if need be:

```php
$this
    ->listensTo(RequestReceived::class, $listener1, $listener2) // register 2 listeners for RequestReceived
    ->get('route');

$this
    ->stopsListeningTo(TaskReceived::class, $listener1, $listener2) // unregister 2 listeners for TaskReceived
    ->get('route');
```


### Container

Octane Testbench also introduces the following helpers to bind and [mock services](https://docs.mockery.io/en/latest/reference/index.html) at the same time while preserving a fluent syntax:

```php
$this
    ->mocks(Service::class, ['expectedMethod' => $expectedValue]) // mock with simple expectations
    ->mocks(Service::class, fn ($mock) => $mock->shouldReceive('method')->twice()) // mock with advanced expectations
    ->partiallyMocks(Service::class, ['expectedMethod' => $expectedValue]) // same as above but partial
    ->partiallyMocks(Service::class, fn ($mock) => $mock->shouldReceive('method')->twice())
    ->get('route');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email andrea.marco.sartori@gmail.com instead of using the issue tracker.

## Credits

- [Andrea Marco Sartori][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-author]: https://img.shields.io/static/v1?label=author&message=cerbero90&color=50ABF1&logo=twitter&style=flat-square
[ico-php]: https://img.shields.io/packagist/php-v/cerbero/octane-testbench?color=%234F5B93&logo=php&style=flat-square
[ico-laravel]: https://img.shields.io/static/v1?label=laravel&message=%E2%89%A58.0&color=ff2d20&logo=laravel&style=flat-square
[ico-octane]: https://img.shields.io/static/v1?label=octane&message=compatible&color=ff2d20&logo=laravel&style=flat-square
[ico-version]: https://img.shields.io/packagist/v/cerbero/octane-testbench.svg?label=version&style=flat-square
[ico-actions]: https://img.shields.io/github/workflow/status/cerbero90/octane-testbench/build?style=flat-square&logo=github
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-psr12]: https://img.shields.io/static/v1?label=compliance&message=PSR-12&color=blue&style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/cerbero90/octane-testbench.svg?style=flat-square&logo=scrutinizer
[ico-code-quality]: https://img.shields.io/scrutinizer/g/cerbero90/octane-testbench.svg?style=flat-square&logo=scrutinizer
[ico-downloads]: https://img.shields.io/packagist/dt/cerbero/octane-testbench.svg?style=flat-square

[link-author]: https://twitter.com/cerbero90
[link-php]: https://www.php.net
[link-laravel]: https://laravel.com
[link-octane]: https://github.com/laravel/octane
[link-packagist]: https://packagist.org/packages/cerbero/octane-testbench
[link-actions]: https://github.com/cerbero90/octane-testbench/actions?query=workflow%3Abuild
[link-psr12]: https://www.php-fig.org/psr/psr-12/
[link-scrutinizer]: https://scrutinizer-ci.com/g/cerbero90/octane-testbench/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/cerbero90/octane-testbench
[link-downloads]: https://packagist.org/packages/cerbero/octane-testbench
[link-contributors]: ../../contributors
