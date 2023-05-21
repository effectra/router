<?php

declare(strict_types=1);

namespace Effectra\Router;

use Effectra\Http\Contracts\RouterDispatcher;

/**
 * @method   middleware(string|MiddlewareInterface $middlewareClass): self
 * 
 * @method   group(string $common_route, $controller, array $methods): self
 * @method   crud(string $route, $controller, string $actions): self
 * @method   setPreRoute(string $preRoute): void
 * @method   get(string $pattern, array|callable $callback): self
 * @method   post(string $pattern, array|callable $callback): self
 * @method   put(string $pattern, array|callable $callback): self
 * @method   delete(string $pattern, array|callable $callback): self
 * @method   patch(string $pattern, array|callable $callback): self
 * @method   options(string $pattern, array|callable $callback): self
 * @method   any(string $pattern, array|callable $callback): self
 * @method   register(string $method, string $pattern, array|callable $callback): self
 * @method   routes(): array
 * @method   getArguments(string $route_parts, string $pattern_parts): array
 * 
 * @method addArguments(array $args): void
 * @method addRequest(RequestInterface $request): void
 * @method addResponse(ResponseInterface $response): void
 * @method setNotFound(callable $response): void
 * @method setInternalServerError(callable $response): void
 */

class Route implements RouterDispatcher
{
    use Dispatcher, Register, Middleware, Utils;
}
