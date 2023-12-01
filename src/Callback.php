<?php

declare(strict_types=1);

namespace Effectra\Router;

use Effectra\Router\Exception\InvalidCallbackException;

/**
 * The Callback class provides methods to retrieve callback functions from various action formats.
 */
class Callback
{

    /**
     * Get the callback for the given action.
     *
     * @param mixed $action The action to get the callback for.
     *
     * @return callable|null The callback function or null if no valid callback found.
     */
    public function getCallback($action): ?callable
    {
        if (is_callable($action)) {
            return $action;
        }

        if (is_array($action)) {
            return $this->closure($action);
        }

        if (is_string($action)) {
            return $this->string($action);
        }

        return null;
    }

    /**
     * Get the closure callback for the given action string.
     *
     * @param string $action The action string.
     *
     * @return callable The closure callback.
     */
    public function string(string $action): callable
    {
        if (strpos($action, '@')) {
            $closure = explode('@', $action);
            return $this->closure($closure);
        }

        return function ($action) {
            return $action;
        };
    }

    /**
     * Get the closure callback for the given action array.
     *
     * @param array $action The action array containing class and method.
     *
     * @return callable The closure callback.
     *
     * @throws InvalidCallbackException If the class or method does not exist.
     */
    public function closure(array $action): callable
    {
        [$class, $method] = $action;

        if (class_exists($class)) {

            $classInstance = Resolver::resolveClass($class);

            if (method_exists($classInstance, $method)) {
                return [$classInstance, $method];
            } else {
                throw new InvalidCallbackException("Method not found: {$class}::{$method}");
            }
        } else {
            throw new InvalidCallbackException("Controller class {$class} not found");
        }
    }

    /**
     * Get the callback for the given class.
     *
     * @param string $class The class name.
     *
     * @return callable The callback function.
     *
     * @throws InvalidCallbackException If the class is not found or does not have an __invoke or index method.
     */
    public function class(string $class): callable
    {
        if (class_exists($class)) {
            if (method_exists($class, '__invoke')) {
                return Resolver::resolveClass($class)();
            } elseif (method_exists($class, 'index')) {
                return $this->closure([$class, 'index']);
            } else {
                throw new InvalidCallbackException("Class '{$class}' does not have an __invoke or index method.");
            }
        } else {
            throw new InvalidCallbackException("Class '{$class}' not found.");
        }
    }
}
