<?php

declare(strict_types=1);

namespace Effectra\Router;

use Exception;

/**
 * The Callback class provides methods to retrieve callback functions from various action formats.
 */
class Callback
{
    protected $container = null;

    protected bool $isUseContainer = false;

    /**
     * set router container for binding and injected dependencies of controller class
     * @param $container
     * @return void
     */
    public function setContainer($container): void
    {
        $this->container = $container;
        $this->isUseContainer = true;
    }
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
            $class = $closure[0];
            $method = $closure[1];
            return $this->closure([$class, $method]);
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
     * @throws Exception If the class or method does not exist.
     */
    public function closure(array $action): callable
    {
        [$class, $method] = $action;

        if (class_exists($class)) {
            if ($this->isUseContainer) {
                $reflection = new \ReflectionClass($class);
                $constructor = $reflection->getConstructor();
                // Get the parameters of the constructor
                $parameters = $constructor->getParameters();
                $dependencies = [];
                foreach ($parameters as $parameter) {
                    $parameterType = $parameter->getType();

                    // Check if the parameter has a type
                    if ($parameterType !== null) {
                        // Get the name of the dependency class
                        $dependencyClass = $parameterType->getName();

                        // Resolve the dependency instance from the container
                        $dependencyInstance = $this->container->get($dependencyClass);

                        // Add the dependency instance to the array
                        $dependencies[] = $dependencyInstance;
                    }
                }
                $classInstance = new $class($dependencies);
            } else {
                $classInstance = new $class();
            }

            if (method_exists($classInstance, $method)) {
                return [$classInstance, $method];
            } else {
                throw new Exception("Method not found: {$class}::{$method}");
            }
        } else {
            throw new Exception("Controller class {$class} not found");
        }
    }

    /**
     * Get the callback for the given class.
     *
     * @param string $class The class name.
     *
     * @return callable The callback function.
     *
     * @throws Exception If the class is not found or does not have an __invoke or index method.
     */
    public function class(string $class): callable
    {
        if (class_exists($class)) {
            if (method_exists($class, '__invoke')) {
                return new $class();
            } elseif (method_exists($class, 'index')) {
                return $this->closure([$class, 'index']);
            } else {
                throw new Exception("Class '{$class}' does not have an __invoke or index method.");
            }
        } else {
            throw new Exception("Class '{$class}' not found.");
        }
    }
}
