<?php

namespace Schnittstabil\Psr\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;

/**
 * PSR-15 middleware pipe.
 */
class Pipe implements ServerMiddlewareInterface
{
    /**
     * @var callable[]|ServerMiddlewareInterface[]
     */
    protected $middlewares;

    /**
     * Constructs a PSR-15 middleware pipe.
     *
     * @param callable[]|ServerMiddlewareInterface[] $middlewares the middlewares, which requests pass through
     */
    public function __construct(callable ...$middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, callable $finalHandler)
    {
        return $this->processMiddleware(0, $request, $finalHandler);
    }

    /**
     * Process an incoming server request by delegating it to the middleware specified by $index.
     *
     * @param int                    $index   the $middlewares index
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    private function processMiddleware(int $index, ServerRequestInterface $request, callable $finalHandler)
    {
        if (!array_key_exists($index, $this->middlewares)) {
            return $finalHandler($request);
        }

        $current = $this->middlewares[$index];

        return $current(
            $request,
            function (ServerRequestInterface $req) use ($index, $finalHandler) {
                return $this->processMiddleware($index + 1, $req, $finalHandler);
            }
        );
    }
}
