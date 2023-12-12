<?php

declare(strict_types=1);

namespace Effectra\Router;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

trait Middleware
{
    /**
     * @var MiddlewareInterface[] $middleware
     */
    protected array $middleware;

    /**
     * Set the middleware to be applied to all routes.
     *
     * @param string $middlewareClass The fully-qualified class name of the middleware to be applied.
     * @return self
     * @throws \InvalidArgumentException if the middleware class is not valid.
     */
    public function middleware(string $middlewareClass): self
    {
        $this->routes[$this->countRoutesLength()]['middleware'][] = $middlewareClass;

        return $this;
    }

    /**
     * count Routes Length
     * @return int
     */
    private function countRoutesLength()
    {
        if(empty($this->routes)){
            return 0;
        }
        return count($this->routes) - 1;
    }

    /**
     * Run the middleware stack for a given request and handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface The response from the middleware stack.
     */
    protected function runMiddleware(ServerRequestInterface $request,RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

}
