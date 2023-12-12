<?php

declare(strict_types=1);

namespace Effectra\Router;

use Bmt\PluralConverter\PluralConverter;
use Psr\Http\Server\MiddlewareInterface;

trait Utils
{
    /**
     * @var ((string|false)[]|(string|true)[])[] $authRoutes
     */
    private static  $authRoutes = [
        'login' => [
            'pattern' => 'login',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'logout' => [
            'pattern' => 'logout',
            'method' => 'post',
            'useMiddleware' => true
        ],
        'register' => [
            'pattern' => 'register',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'tokenVerify' => [
            'pattern' => 'token/verify',
            'method' => 'get',
            'useMiddleware' => false
        ],
        'forgotPassword' => [
            'pattern' => 'forgot-password',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'resetPassword' => [
            'pattern' => 'reset-password/{token}',
            'method' => 'get',
            'useMiddleware' => false
        ],
        'profile' => [
            'pattern' => 'profile',
            'method' => 'get',
            'useMiddleware' => true
        ],
        'profileUpdate' => [
            'pattern' => 'profile/update',
            'method' => 'put',
            'useMiddleware' => true
        ],
        'profileChangePassword' => [
            'pattern' => 'profile/change-password',
            'method' => 'put',
            'useMiddleware' => true
        ],
        'emailVerify' => [
            'pattern' => 'email/verify/{id}/{hash}',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'emailResend' => [
            'pattern' => 'email/resend',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'twoFactorAuthentication' => [
            'pattern' => 'two-factor-authentication',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'twoFactorRecoveryCodes' => [
            'pattern' => 'two-factor-recovery-codes',
            'method' => 'get',
            'useMiddleware' => true
        ],
        'loginOAuth' => [
            'pattern' => 'login/{provider}',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'loginOAuthCallback' => [
            'pattern' => 'login/{provider}/callback',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'deactivateAccount' => [
            'pattern' => 'deactivate-account',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'impersonate' => [
            'pattern' => 'impersonate/{user_id}',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'stopImpersonating' => [
            'pattern' => 'stop-impersonating',
            'method' => 'post',
            'useMiddleware' => false
        ],
        'termsOfService' => [
            'pattern' => 'terms-of-service',
            'method' => 'get',
            'useMiddleware' => false
        ],
        'privacyPolicy' => [
            'pattern' => 'privacy-policy',
            'method' => 'get',
            'useMiddleware' => false
        ],
    ];

    /**
     * An array of valid HTTP methods.
     *
     * @var array
     */
    private array $methods = ['get', 'post', 'put', 'delete', 'patch', 'options'];
    /**
     * An array of valid CRUD action names.
     *
     * @var array
     */
    private array $actions = ['read', 'readOne', 'create', 'update', 'delete', 'deleteAll', 'search'];

    /**
     * get list of routes that require authentication.
     * @return array
     */
    public static function getAuthRoutes(): array
    {
        return static::$authRoutes;
    }

    /**
     * Define a group of routes that require authentication.
     *
     * @param string $pattern The URL pattern for the group of routes.
     * @param mixed $controller The controller for the group of routes.
     * @param mixed $middleware The middleware for the group of routes.
     * @param array $forget remove routes from authRoutes
     * @return $this
     */
    public function auth(string $pattern, $controller,  $middleware = null, array $forget = []): self
    {

        foreach (static::getAuthRoutes() as $methodName => $attributes) {
            if (!in_array($methodName, $forget)) {
                $methodHttp = $attributes['method'];
                $this->$methodHttp(
                    $this->remakeRoute($pattern) . '/' . $attributes['pattern'],
                    [$controller, $methodName]
                );
                if ($attributes['useMiddleware'] === true && $middleware) {
                    $this->middleware($middleware);
                }
            }
        }

        return $this;
    }

    /**
     * Create a group of routes with a common prefix.
     *
     * @param string $path The common prefix for routes in this group.
     * @param string|object $controller The controller class name or instance.
     * @param callable $routes A callable that defines routes within the group.
     * @return self Returns the current Route instance.
     */
    public function group(string $path, $controller, callable $routes): self
    {
        call_user_func_array($routes, [new RouteGroup($path, $controller, $this)]);
        return $this;
    }

    /**

     *Define a group of CRUD routes that share a common URL prefix.

     *@param string $route The URL prefix for the group of routes.

     *@param mixed $controller The controller object or class that handles the requests.

     *@param string $actions A string of pipe-separated CRUD actions to be generated.

     *@return self Returns the instance of the Router.
     */
    public function crud(string $route, $controller, string $actions, MiddlewareInterface|array $middlewares = []): self
    {
        $route = $this->remakeRoute($route);

        $actions_arr = explode('|', $actions);

        $actions_arr = array_map(fn ($action) => trim($action), $actions_arr);

        foreach ($actions_arr as $action) {
            if (in_array($action, $this->actions)) {
                $converter = new PluralConverter();

                if (method_exists($controller, $action) && $action === 'read') {

                    $this->get($converter->convertToPlural($route), [$controller, 'read']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
                if (method_exists($controller, $action) && $action === 'readPaging') {

                    $this->get($converter->convertToPlural($route), [$controller, 'readPaging']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
                if (method_exists($controller, $action) && $action === 'readOne') {

                    $this->get($route . '/{id}', [$controller, 'readOne']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
                if (method_exists($controller, $action) && $action === 'create') {

                    $this->post($route . '/create', [$controller, 'create']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
                if (method_exists($controller, $action) && $action === 'delete') {

                    $this->delete($route . '/delete/{id}', [$controller, 'delete']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
                if (method_exists($controller, $action) && $action === 'deleteAll') {

                    $this->delete($converter->convertToPlural($route) . '/delete-all', [$controller, 'deleteAll']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
                if (method_exists($controller, $action) && $action === 'update') {

                    $this->put($route . '/update/{id}', [$controller, 'update']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
                if (method_exists($controller, $action) && $action === 'search') {

                    $this->get($converter->convertToPlural($route) . '/search', [$controller, 'search']);
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->middleware($middleware);
                        }
                    }
                }
            }
        }

        return $this;
    }
}
