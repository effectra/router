<?php

declare(strict_types=1);

namespace Effectra\Router;


trait Register
{
    /**
     * An array of registered routes.
     *
     * @var array
     */
    protected array $routes = [];
    /**
     * An array of route names to be excluded from the route list.
     *
     * @var array
     */
    protected array $exclude_names = [
        'api',
    ];

    /**
     * Remake a route by correcting slashes, removing query parameters, and trimming leading/trailing slashes.
     *
     * @param string $route The route to remake.
     * @return string The remade route.
     */
    private function remakeRoute(string $route): string
    {
        $route = $this->correctRoute($route);
        $route = explode('?', $route)[0];

        if ($route === '/') {
            return '/';
        }

        return trim(rtrim($route, '/'), '/');
    }

    public function routeId($method, $pattern)
    {
        // Replace dynamic segments with a placeholder
        $pattern = preg_replace('/\{(\w+)\}/', '{_}', $pattern);

        // Generate an ID by concatenating the method name and modified pattern
        $id = $method . '_' . $pattern;

        // Remove any special characters or slashes from the ID
        $id = preg_replace('/[^a-zA-Z0-9]/', '', $id);

        return $id;
    }

    /**
     * Correct a route by replacing multiple slashes and backslashes with a single slash.
     *
     * @param string $route The route to correct.
     * @return string The corrected route.
     */
    public function correctRoute(string $route): string
    {
        return $route = str_replace(['//', '\\', '///'], '/', $route);
    }


    /**
     * Set a prefix route that will be added to all registered routes.
     *
     * @param string $preRoute The prefix route.
     * @return void
     */
    public function setPreRoute(string $preRoute): void
    {
        $preRoute = rtrim($preRoute, '/');
        $newRoutes = [];
        foreach ($this->routes as $route) {
            $id = $this->routeId($route['method'], $preRoute . $route['pattern']);
            $route['id'] = $id;
            $route['pre_pattern'] = $preRoute;
            $route['length'] =  $this->getLength($preRoute . '/' . $route['pattern']);
            $newRoutes[] = $route;
        }
        $this->routes = $newRoutes;
    }



    /**
     * Register a GET route.
     *
     * @param string $pattern The route pattern.
     * @param $callback The callback associated with the route.
     * @return self
     */
    public function get(string $pattern, $callback): self
    {
        return $this->register('get', $pattern, $callback);
    }

    /**
     * Register a POST route.
     *
     * @param string $pattern The route pattern.
     * @param $callback The callback associated with the route.
     * @return self
     */
    public function post(string $pattern, $callback): self
    {
        return $this->register('post', $pattern, $callback);
    }

    /**
     * Register a PUT route.
     *
     * @param string $pattern The route pattern.
     * @param $callback The callback associated with the route.
     * @return self
     */
    public function put(string $pattern, $callback): self
    {
        return $this->register('put', $pattern, $callback);
    }

    /**
     * Register a DELETE route.
     *
     * @param string $pattern The route pattern.
     * @param $callback The callback associated with the route.
     * @return self
     */
    public function delete(string $pattern, $callback): self
    {
        return $this->register('delete', $pattern, $callback);
    }

    /**
     * Register a PATCH route.
     *
     * @param string $pattern The route pattern.
     * @param $callback The callback associated with the route.
     * @return self
     */
    public function patch(string $pattern, $callback): self
    {
        return $this->register('patch', $pattern, $callback);
    }

    /**
     * Register an OPTIONS route.
     *
     * @param string $pattern The route pattern.
     * @param $callback The callback associated with the route.
     * @return self
     */
    public function options(string $pattern, $callback): self
    {
        return $this->register('options', $pattern, $callback);
    }

    /**
     * Register a route for all request methods.
     *
     * @param string $pattern The route pattern.
     * @param $callback The callback associated with the route.
     * @return self
     */
    public function any(string $pattern, $callback): self
    {
        foreach ($this->methods as $method) {
            $this->register($method, $pattern, $callback);
        }

        return $this;
    }

    public function name(string $name): self
    {
        $this->routes[count($this->routes) - 1]['name'] = $name;
        return $this;
    }

    /**
     * Register a route with the given method, pattern, and callback.
     *
     * @param string $method The HTTP method.
     * @param string $pattern The route pattern.
     * @param $callback The callback function or array to handle the route.
     * @return self The current instance of the class.
     */
    public function register(string $method, string $pattern, $callback): self
    {
        $id = $this->routeId($method, $pattern);
        $args = $this->getSegmentFromPattern($pattern);
        $this->routes[] = [
            'id' => $id,
            'name' => '',
            'method' => $method,
            'pre_pattern' => '',
            'length' => $this->getLength($pattern),
            'full_pattern' => $this->remakeRoute($pattern),
            'pattern' => $this->remakeRoute($pattern),
            'args' => $args,
            'callback' => $callback,
            'callback_type' => $this->callbackType($callback),
            'controller' => $this->callbackType($callback) === 'closure' ? $callback[0] : null,
            'controller_method' => $this->callbackType($callback) === 'closure' ? $callback[1] : null,
            'middleware' => []
        ];
        return $this;
    }

    public function getLength($pattern)
    {
        return count(explode('/', $pattern));
    }

    public function callbackType($callback)
    {
        if (is_callable($callback)) {
            return 'callable';
        }

        if (is_array($callback)) {
            return 'closure';
        }

        if (is_string($callback)) {
            return 'string';
        }

        return 'undefined';
    }

    /**
     * Get the registered routes.
     *
     * @return array The registered routes.
     */
    public function routes(): array
    {
        return $this->routes;
    }


    /**
     * Get segment from pattern
     * @param string $pattern
     * @return array
     */
    private function getSegmentFromPattern(string $pattern): array
    {
        preg_match_all('/\{(.*?)\}/', $pattern, $matches);
        return $matches[1];
    }

    /**
     * Get the arguments from the route parts based on the pattern parts.
     *
     * @param string $route_parts The route parts.
     * @param string $pattern_parts The pattern parts.
     * @return array The arguments extracted from the route parts.
     */
    public function getArguments(string $route_parts, string $pattern_parts): array
    {
        $args = [];
        $parts = $this->segmentRoute($pattern_parts);
        for ($i = 0; $i < count($parts); $i++) {
            if (str_contains($parts[$i], '{')) {
                $args[$this->getSegmentFromPattern($parts[$i])[0]] = $this->segmentRoute($route_parts)[$i];
            }
        }
        return $args;
    }


    /**
     * Segment the given route into an array of segments.
     *
     * @param string $route The route to segment.
     * @return array The array of segments.
     */
    public function segmentRoute(string $route): array
    {
        return explode('/', $route);
    }


    /**
     * Clear the prefix names from the given data.
     *
     * @param array $data The data to be filtered.
     * @return array The filtered data with prefix names removed.
     */
    public function clearPrefixName(array $data): array
    {
        return array_filter($data, fn ($name) => !in_array($name, $this->exclude_names));
    }

    /**
     * Set the excluded pattern(s) for filtering.
     *
     * @param string|array $pattern The pattern(s) to exclude.
     * @return void
     */
    public function setExcludedPattern(string|array $pattern): void
    {
        $patterns = is_array($pattern) ? $pattern : [$pattern];
        $this->exclude_names = array_merge($this->exclude_names, $patterns);
    }


    public function getAction(string $uri_path, string $method)
    {
        $path = $this->remakeRoute($uri_path);

        $requestMethod = strtolower($method);

        $length = $this->getLength($path);


        foreach ($this->routes as $route) {
            $fullPattern = $this->remakeRoute($route['pre_pattern'] . '/' . $route['pattern']);

            if ($requestMethod === $route['method']) {
                if ($fullPattern === $path) {
                    return $route;
                }
                if (count($route['args']) !== 0 && $length === $route['length']) {
                    if ($this->matchRoutePattern($path, $fullPattern)) {
                        $args = $this->getArguments($path, $fullPattern);
                        $route['args'] = $args;
                        $this->addArguments($args);
                        return $route;
                    }
                }
            }
        }

        return null;
    }
    /**
     * Match a given path against a route pattern.
     *
     * @param string $path The path to match.
     * @param string $pattern The route pattern to match against.
     * @return bool True if the path matches the pattern, false otherwise.
     */
    public function matchRoutePattern($path, $pattern)
    {
        // Escape the special characters in the pattern
        $pattern = preg_quote($pattern, '/');

        // Replace "{id}" in the pattern with a regular expression to match any number
        $pattern = str_replace('\{id\}', '(\d+)', $pattern);

        // Replace "{type}" in the pattern with a regular expression to match any word characters
        $pattern = str_replace('\{type\}', '(\w+)', $pattern);

        // Perform the regex match
        if (preg_match('/^' . $pattern . '$/', $path, $matches)) {
            // Match found
            return true;
        } else {
            // No match found
            return false;
        }
    }
}
