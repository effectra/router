<?php

declare(strict_types=1);

namespace Effectra\Router;

/**
 * Class RouteGroup
 * @package Effectra\Router
 */
class RouteGroup
{
    /**
     * RouteGroup constructor.
     *
     * @param string $prePath The prefix path for routes in this group.
     * @param string $controller The controller class name.
     * @param Route $router The Route instance for defining routes.
     */
    public function __construct(
        protected string $prePath,
        protected string $controller,
        protected Route $router
    ) {
    }

    /**
     * Define a GET route.
     *
     * @param string $pattern The route pattern.
     * @param string $method The controller method to call.
     * @return void
     */
    public function get(string $pattern, string $method): void
    {
        $this->router->get($this->prePath . $pattern, [$this->controller, $method]);
    }

    /**
     * Define a POST route.
     *
     * @param string $pattern The route pattern.
     * @param string $method The controller method to call.
     * @return void
     */
    public function post(string $pattern, string $method): void
    {
        $this->router->post($this->prePath . $pattern, [$this->controller, $method]);
    }

    /**
     * Define a PUT route.
     *
     * @param string $pattern The route pattern.
     * @param string $method The controller method to call.
     * @return void
     */
    public function put(string $pattern, string $method): void
    {
        $this->router->put($this->prePath . $pattern, [$this->controller, $method]);
    }

    /**
     * Define a DELETE route.
     *
     * @param string $pattern The route pattern.
     * @param string $method The controller method to call.
     * @return void
     */
    public function delete(string $pattern, string $method): void
    {
        $this->router->delete($this->prePath . $pattern, [$this->controller, $method]);
    }

    /**
     * Define a PATCH route.
     *
     * @param string $pattern The route pattern.
     * @param string $method The controller method to call.
     * @return void
     */
    public function patch(string $pattern, string $method): void
    {
        $this->router->patch($this->prePath . $pattern, [$this->controller, $method]);
    }

    /**
     * Define an OPTIONS route.
     *
     * @param string $pattern The route pattern.
     * @param string $method The controller method to call.
     * @return void
     */
    public function options(string $pattern, string $method): void
    {
        $this->router->options($this->prePath . $pattern, [$this->controller, $method]);
    }

    /**
     * Define a route that responds to any HTTP method.
     *
     * @param string $pattern The route pattern.
     * @param string $method The controller method to call.
     * @return void
     */
    public function any(string $pattern, string $method): void
    {
        $this->router->any($this->prePath . $pattern, [$this->controller, $method]);
    }
}
