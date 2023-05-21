<?php

declare(strict_types=1);

namespace Effectra\Router;

use Exception;

trait Register
{
    /**
     * An array of registered routes.
     *
     * @var array
     */
    public array $routes = [];
    /**
     * An array of route names to be excluded from the route list.
     *
     * @var array
     */
    public array $exclude_names = [
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


    /**
     * Correct a route by replacing multiple slashes and backslashes with a single slash.
     *
     * @param string $route The route to correct.
     * @return string The corrected route.
     */
    private function correctRoute(string $route): string
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
        $preRoute = rtrim($preRoute, '/') . '/';
        $routes = $this->routes;
        $this->routes = [];

        foreach ($routes as $method => $routeMap) {
            foreach ($routeMap as $pattern => $callback) {
                $this->routes[$method][$this->correctRoute($preRoute . $pattern)] = $callback;
            }
        }
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

    /**
     * Get the registered routes in a structured format.
     *
     * @return array An array of registered routes with method, pattern, and controller information.
     */
    public function getRegisteredRoutes(): array
    {
        $registeredRoutes = [];

        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $pattern => $callback) {
                $controller = '';
                if (is_array($callback)) {
                    $firstParam = reset($callback);

                    if (is_object($firstParam)) {
                        $controller = get_class($firstParam);
                    }
                }

                $registeredRoutes[] = [
                    'method' => $method,
                    'pattern' => $pattern,
                    'controller' => $controller ?: 'Closure',
                ];
            }
        }

        return $registeredRoutes;
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
        $this->routes[$method][$this->remakeRoute($pattern)] = $callback;
        return $this;
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
     * Check if the number of segments in the URL matches the number of segments in the pattern.
     *
     * @param string $url The URL to compare.
     * @param string $pattern The pattern to compare.
     * @return bool True if the number of segments is equal, false otherwise.
     */
    private function countSegmentsIsEquals(string $url, string $pattern): bool
    {
        return count($this->segmentRoute($url)) === count($this->segmentRoute($pattern));
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
     * Check if the pattern contains route segments.
     *
     * @param string $pattern The pattern to check.
     * @return bool True if the pattern contains segments, false otherwise.
     */
    private function checkIfIsRouteWithSegment(string $pattern): bool
    {
        return !empty($this->getSegmentFromPattern($pattern));
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
     * Get the matching route pattern if the method and URL match.
     *
     * @param string $method The HTTP method.
     * @param string $url The URL to match.
     * @return string|null The matching route pattern, or null if no match is found.
     * @throws Exception If no routes are registered.
     */
    public function getRouteIfTheSame(string $method, string $url): ?string
    {
        if (empty($this->routes())) {
            throw new Exception('No Routes Registered!');
        }

        foreach ($this->routes[$method] as $pattern => $callback) {
            if ($this->countSegmentsIsEquals($url, $pattern) && $pattern !== '/') {
                $segment = $this->clearPrefixName($this->removeArgumentFromPattern($this->segmentRoute($pattern)));
                $clean = $this->clearPrefixName($this->segmentRoute($url));

                $s_1 = implode('', $segment);
                $s_2 = implode('', array_slice($clean, 0, count($segment)));

                if ($s_1 === $s_2) {
                    return $pattern;
                }
            }
        }

        return null;
    }


    /**
     * Removes arguments from the pattern array that do not contain '{'.
     *
     * @param array $patternArray The pattern array to process.
     * @return array The modified pattern array with arguments removed.
     */
    private function removeArgumentFromPattern(array $patternArray): array
    {
        return array_filter($patternArray, fn ($value) => !str_contains($value, '{'));
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

    /**
     * Find the correct action to execute based on the given URI path and request method.
     *
     * @param string $uri_path A string representing the URI path that the request is being made to.
     * @param string $method A string representing the HTTP request method (e.g. GET, POST, PUT, DELETE, etc.)
     *
     * @return mixed function representing the action that should be taken for the given URI path and request method, or null if no action is found.
     */
    public function getAction(string $uri_path, string $method)
    {
        $route = $this->remakeRoute($uri_path);

        $requestMethod = strtolower($method);

        if ($uri_path == '/') {
            return $this->routes[$requestMethod]['/'];
        }

        $action = $this->routes[$requestMethod][$route] ?? null;

        if (!$action) {
            if ($pattern = $this->getRouteIfTheSame($requestMethod, $route)) {
                if (
                    $this->checkIfIsRouteWithSegment($pattern) &&
                    $this->countSegmentsIsEquals($route, $pattern)
                ) {
                    $args = $this->getArguments($route, $pattern);
                    $this->addArguments($args);

                    $action = $this->routes[$requestMethod][$pattern];
                }
            }
        }

        return $action;
    }
}
