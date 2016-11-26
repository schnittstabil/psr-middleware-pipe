<?php

namespace Schnittstabil\Psr\Middleware\Helpers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;

class CounterMiddleware implements ServerMiddlewareInterface
{
    protected $index;

    public function __construct(int $index = 0)
    {
        $this->index = $index;
    }

    public function __invoke(ServerRequestInterface $request, callable $delegate):ResponseInterface
    {
        $response = $delegate($request);
        $response->getBody()->write((string) $this->index++);

        return $response;
    }
}
