<?php

declare(strict_types=1);

namespace Effectra\Router;

use Psr\Container\ContainerInterface;

/**
 * Class Resolver
 *
 * A simple class resolver that supports dependency injection through a PSR-11 container.
 */
class Resolver
{
    /**
     * @var ContainerInterface|null The dependency injection container.
     */
    protected static ?ContainerInterface $container = null;

    /**
     * @var bool Flag indicating whether the container is in use.
     */
    protected static bool $isUseContainer = false;

    /**
     * Set the dependency injection container for the Resolver class.
     *
     * @param ContainerInterface $container The dependency injection container to set.
     * @return void
     */
    public static function setContainer(ContainerInterface $container): void
    {
        static::$container = $container;
        static::$isUseContainer = true;
    }

    /**
     * Resolve an instance of a class, supporting dependency injection through the container.
     *
     * @param string $class The class to resolve.
     * @param mixed ...$args Accepts a variable number of arguments which are passed to the class constructor, much like {@see call_user_func}
     * @return object An instance of the resolved class.
     * @throws \RuntimeException If the class has dependencies but no container is provided.
     */
    public static function resolveClass($class,...$args): object
    {
        if (static::$isUseContainer) {
            return static::$container->get($class);
        } else {
            $reflectionClass = new \ReflectionClass($class);

            $constructor = $reflectionClass->getConstructor();

            if (null === $constructor) {
                return $reflectionClass->newInstance($args);
            }
            throw new \RuntimeException("You must use psr/container to resolve class dependencies in $class");
        }
    }
}
