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
     * @param string|MiddlewareInterface $middlewareClass The fully-qualified class name of the middleware to be applied.
     * @return self
     * @throws \InvalidArgumentException if the middleware class is not valid.
     */
    public function middleware(string|MiddlewareInterface $middlewareClass): self
    {
        if (!is_subclass_of($middlewareClass, MiddlewareInterface::class)) {
            throw new InvalidArgumentException("{$middlewareClass} is not a valid middleware class.");
        }
        $this->middleware[] = $middlewareClass;

        return $this;
    }

    /**
     * Run the middleware stack for a given request and handler.
     *
     * @param MiddlewareInterface[] $middlewareStack The middleware stack to be run.
     * @param ServerRequestInterface $request The incoming HTTP request.
     * @param RequestHandlerInterface $handler The request handler for the route.
     * @return ResponseInterface The response from the middleware stack.
     */
    protected function runMiddleware(array $middlewareStack, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

}
