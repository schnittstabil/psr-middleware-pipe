# Schnittstabil\Psr\Middleware\Pipe [![Build Status](https://travis-ci.org/schnittstabil/psr-middleware-pipe.svg?branch=master)](https://travis-ci.org/schnittstabil/psr-middleware-pipe) [![Coverage Status](https://coveralls.io/repos/schnittstabil/psr-middleware-pipe/badge.svg?branch=master&service=github)](https://coveralls.io/github/schnittstabil/psr-middleware-pipe?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/schnittstabil/psr-middleware-pipe/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/schnittstabil/psr-middleware-pipe/?branch=master)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/14087e8f-0034-4274-8c84-b0f70c76c926/big.png)](https://insight.sensiolabs.com/projects/14087e8f-0034-4274-8c84-b0f70c76c926)

> :bomb: **EXPERIMENTAL – `callables+interface` version of PSR-15** :bomb:
>
> [PSR-15](https://github.com/middlewares/awesome-psr15-middlewares) middleware pipe


## Install

```
composer require schnittstabil/psr-middleware-pipe
```


## Usage

```php
use Schnittstabil\Psr\Middleware\Pipe;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// create your middlewares
$middlewares = [];

$middlewares[] = function (ServerRequestInterface $request, callable $delegate):ResponseInterface {
    // delegate $request to the next request handler, i.e. the middleware right below
    $response = $delegate($request);

    return $response->withHeader('X-PoweredBy', 'Unicorns');
};

$middlewares[] = function (ServerRequestInterface $request, callable $delegate):ResponseInterface {
    // delegate $request to the next request handler, i.e. the $finalHandler below
    $response = $delegate($request);

    return $response->withHeader('content-type', 'application/json; charset=utf-8');
};

// create the middleware pipe
$pipe = new Pipe(...$middlewares);

// create a handler for requests which reached the end of the pipe
$finalHandler = function (ServerRequestInterface $request):ResponseInterface {
    return new \Zend\Diactoros\Response();
};

// and dispatch a request
$response = $pipe(new \Zend\Diactoros\ServerRequest(), $finalHandler);
```


## API

### `Schnittstabil\Psr\Middleware\Pipe implements ServerMiddlewareInterface`

#### `Pipe::__construct`

```php
/**
 * Constructs a PSR-15 middleware pipe.
 *
 * @param callable[]|ServerMiddlewareInterface[] $middlewares the middlewares, which requests pass through
 */
public function __construct(callable ...$middlewares)
```

#### Inherited from `ServerMiddlewareInterface::__invoke`

```php
/**
 * Process an incoming server request and return a response, optionally delegating
 * the response utilizing $delegate.
 *
 * @param ServerRequestInterface     $request
 * @param DelegateInterface|callable $delegate
 *
 * @return ResponseInterface
 */
public function __invoke(ServerRequestInterface $request, callable $delegate)
```


## License

MIT © [Michael Mayer](http://schnittstabil.de)
