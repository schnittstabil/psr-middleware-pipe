<?php

namespace Schnittstabil\Psr\Middleware\Helpers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Interop\Http\Middleware\DelegateInterface;

class FinalHandler implements DelegateInterface
{
    protected $body;

    public function __construct(string $body)
    {
        $this->body = $body;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(RequestInterface $request):ResponseInterface
    {
        return new Response($this->body);
    }
}
