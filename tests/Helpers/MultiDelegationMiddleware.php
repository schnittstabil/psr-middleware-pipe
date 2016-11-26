<?php

namespace Schnittstabil\Psr\Middleware\Helpers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;

class MultiDelegationMiddleware implements ServerMiddlewareInterface
{
    protected $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function __invoke(ServerRequestInterface $request, callable $delegate):ResponseInterface
    {
        for ($i = 0; $i < $this->count; ++$i) {
            $response = $delegate($request);
        }

        return $response;
    }
}
