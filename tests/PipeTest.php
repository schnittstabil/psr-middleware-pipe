<?php

namespace Schnittstabil\Psr\Middleware;

use Exception;
use Schnittstabil\Psr\Middleware\Helpers\CounterMiddleware;
use Schnittstabil\Psr\Middleware\Helpers\FinalHandler;
use Schnittstabil\Psr\Middleware\Helpers\Response;
use Schnittstabil\Psr\Middleware\Helpers\MultiDelegationMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;

class PipeTest extends \PHPUnit_Framework_TestCase
{
    use \VladaHejda\AssertException;

    public function testEmptyPipesShouldBeValid()
    {
        $finalHandler = new FinalHandler('Final!');

        $sut = new Pipe();
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Final!', (string) $response->getBody());
    }

    public function testSingleMiddelwareShouldBeValid()
    {
        $finalHandler = new FinalHandler('Final!');
        $middleware = new CounterMiddleware(0);

        $sut = new Pipe($middleware);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Final!0', (string) $response->getBody());
    }

    public function testDoubleMultipleMiddlewareShouldBeValid()
    {
        $finalHandler = new FinalHandler('Final!');
        $middleware0 = new CounterMiddleware(0);
        $middleware9 = new CounterMiddleware(9);

        $sut = new Pipe($middleware0, $middleware9);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Final!90', (string) $response->getBody());
    }

    public function testMiddlewareReuseShouldBeValid()
    {
        $finalHandler = new FinalHandler('Final!');
        $middleware = new CounterMiddleware(0);

        $sut = new Pipe($middleware, $middleware);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Final!01', (string) $response->getBody());
    }

    public function testMultipleMiddlewaresShouldBeValid()
    {
        $finalHandler = new FinalHandler('Final!');
        $middlewares = [
            new CounterMiddleware(3),
            new CounterMiddleware(2),
            new CounterMiddleware(1),
            new CounterMiddleware(0),
        ];

        $sut = new Pipe(...$middlewares);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Final!0123', (string) $response->getBody());
    }

    public function testMultiDelegationMiddlewaresShouldBeValid()
    {
        $finalHandler = new FinalHandler('Final!');
        $middlewares = [
            new MultiDelegationMiddleware(42),
            new CounterMiddleware(1),
        ];

        $sut = new Pipe(...$middlewares);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Final!42', (string) $response->getBody());
    }

    public function testCallbackMiddelwareShouldBeValid()
    {
        $finalHandler = new FinalHandler('Final!');
        $middleware = function (ServerRequestInterface $request, callable $delegate) {
            static $index = 0;

            $response = $delegate($request);
            $response->getBody()->write((string) $index++);

            return $response;
        };

        $sut = new Pipe($middleware);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Final!0', (string) $response->getBody());
    }

    public function testMiddlewaresCanHandleCoreExceptions()
    {
        $finalHandler = function (RequestInterface $request):ResponseInterface {
            throw new Exception('Oops, something went wrong!', 500);
        };
        $middlewares = [
            function (ServerRequestInterface $request, callable $delegate) {
                try {
                    $response = $delegate($request);
                } catch (Exception $e) {
                    return new Response('Catched: '.$e->getMessage(), $e->getCode());
                }
            },
        ];

        $sut = new Pipe(...$middlewares);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Catched: Oops, something went wrong!', (string) $response->getBody());
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testMiddlewaresCanHandleMiddlewareExceptions()
    {
        $finalHandler = new FinalHandler('Final!');
        $middlewares = [
            function (ServerRequestInterface $request, callable $delegate) {
                try {
                    $response = $delegate($request);
                } catch (Exception $e) {
                    return new Response('Catched: '.$e->getMessage(), $e->getCode());
                }
            },
            function (ServerRequestInterface $request, callable $delegate) {
                $response = $delegate($request);
                throw new Exception('Oops, something went wrong!', 500);
            },
        ];

        $sut = new Pipe(...$middlewares);
        $response = $sut(new ServerRequest(), $finalHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Catched: Oops, something went wrong!', (string) $response->getBody());
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testMiddlewareCallerHaveToHandleCoreExceptions()
    {
        $finalHandler = function (ServerRequestInterface $request):ResponseInterface {
            throw new Exception('Oops, something went wrong!', 500);
        };
        $middlewares = [
            function (ServerRequestInterface $request, callable $delegate) {
                $response = $delegate($request);

                return $response;
            },
        ];

        $sut = new Pipe(...$middlewares);

        $this->assertException(function () use ($sut, $finalHandler) {
            $sut(new ServerRequest(), $finalHandler);
        }, Exception::class, 500, 'Oops, something went wrong!');
    }

    public function testMiddlewareCallerHaveToHandleMiddlewareExceptions()
    {
        $finalHandler = new FinalHandler('Final!');
        $middlewares = [
            function (ServerRequestInterface $request, callable $delegate) {
                return $delegate($request);
            },
            function (ServerRequestInterface $request, callable $delegate) {
                $response = $delegate($request);
                throw new Exception('Oops, something went wrong!', 500);
            },
        ];

        $sut = new Pipe(...$middlewares);

        $this->assertException(function () use ($sut, $finalHandler) {
            $sut(new ServerRequest(), $finalHandler);
        }, Exception::class, 500, 'Oops, something went wrong!');
    }
}
