<?php

declare(strict_types=1);

namespace Effectra\Router;

use Psr\Container\ContainerInterface;

/**
 * class Route
 * a lightweight and flexible routing library for PHP applications. It provides a convenient way to define routes and dispatch incoming requests to the appropriate controllers or callbacks.
 * @package  Effectra\Router
 */
class Route
{
    use Dispatcher, Register, Middleware, Utils;
    
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

}
