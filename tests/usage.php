#!/usr/bin/env php
<?php

namespace Schnittstabil;

require __DIR__.'/../vendor/autoload.php';

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

exit($response->getHeader('X-PoweredBy')[0] === 'Unicorns' ? 0 : 1);
