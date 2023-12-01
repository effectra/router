<?php

declare(strict_types=1);

namespace Effectra\Router;

use Effectra\Contracts\Http\RouterDispatcher;
use Psr\Container\ContainerInterface;

/**
 * @method  \Effectra\Router\Middleware middleware(string|MiddlewareInterface $middlewareClass): self
 * 
 * @method  \Effectra\Router\Utils group(string $common_route, $controller, array $methods): self
 * @method  \Effectra\Router\Utils crud(string $route, $controller, string $actions, ?MiddlewareInterface|array $middleware = null): self
 * @method  \Effectra\Router\Utils auth(string $pattern, $controller): self
 * 
 * @method  \Effectra\Router\Register setPreRoute(string $preRoute): void
 * @method  \Effectra\Router\Register get(string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register post(string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register put(string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register delete(string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register patch(string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register options(string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register any(string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register register(string $method, string $pattern, array|callable $callback): self
 * @method  \Effectra\Router\Register routes(): array
 * @method  \Effectra\Router\Register name(): self
 * @method  \Effectra\Router\Register getArguments(string $route_parts, string $pattern_parts): array
 * 
 * @method \Effectra\Router\Dispatcher addArguments(array $args): void
 * @method \Effectra\Router\Dispatcher addRequest(ServerRequestInterface $request): void
 * @method \Effectra\Router\Dispatcher addResponse(ResponseInterface $response): void
 * @method \Effectra\Router\Dispatcher setNotFound(callable $response): void
 * @method \Effectra\Router\Dispatcher setInternalServerError(callable $response): void
 */

class Route
{
    /**
     * Set the dependency injection container for the Resolver class.
     *
     * @param \Psr\Container\ContainerInterface $container The dependency injection container to set.
     * @return void
     * @throws \RuntimeException If the Resolver class is not initialized.
     */
    public static function setContainer(ContainerInterface $container): void
    {
        Resolver::setContainer($container);
    }

    use Dispatcher, Register, Middleware, Utils;
}
