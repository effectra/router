<?php

declare(strict_types=1);

namespace Effectra\Router;

use Bmt\PluralConverter\PluralConverter;
use Exception;

trait Utils
{
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
     * Define a group of routes that require authentication.
     *
     * @param string $pattern The URL pattern for the group of routes.
     * @param mixed $controller The controller for the group of routes.
     * @return $this
     */
    public function auth(string $pattern, $controller): self
    {
        $patterns = [
            'login', 'logout', 'register',
            'verify/token', 'verify/code', 'verify/url',
            'reset-password',
            'send/code-email', 'send/code-phone', 'send/active-url'
        ];
        $methods = [
            'login', 'logout', 'register',
            'verifyToken', 'verifyCode', 'verifyUrl',
            'resetPassword',
            'sendCodeEmail', 'sendCodePhone', 'sendActiveUrl'
        ];
        for ($__i__ = 0; $__i__ < count($patterns); $__i__++) {
            $this->post($this->remakeRoute($pattern) . '/' . $patterns[$__i__], [$controller, $methods[$__i__]]);
        }
        return $this;
    }
    /**
     * Define a group of routes that share a common URL prefix.
     *
     * @param string $common_route The common URL prefix for the group of routes.
     * @param mixed $controller The controller for the group of routes.
     * @param array $methods An array of HTTP methods and route patterns for the group of routes.
     * @return $this
     * @throws Exception if the route

     */
    public function group(string $common_route, $controller, array $methods): self
    {
        $common_route = $this->remakeRoute($common_route);

        foreach ($methods as $m) {

            if (!is_array($m)) {
                throw new Exception("Only array passed, like ['get|info/{id}' => 'info'] ");
            }

            if (!str_contains(key($m), '|')) {
                throw new Exception("Separator '|' not found");
            }

            $ext_route = explode('|', key($m));

            $action = $m[key($m)];

            $method = trim($ext_route[0]);

            if (!in_array($method, $this->methods)) {

                throw new Exception("Http Method not exists !");
            }

            $route = trim(end($ext_route));

            if (method_exists($controller, $action)) {
                $this->$method($common_route . '/' . $route, [$controller, $action]);
            }
        }
        return $this;
    }
    /**

     *Define a group of CRUD routes that share a common URL prefix.

     *@param string $route The URL prefix for the group of routes.

     *@param mixed $controller The controller object or class that handles the requests.

     *@param string $actions A string of pipe-separated CRUD actions to be generated.

     *@return self Returns the instance of the Router.
     */
    public function crud(string $route, $controller, string $actions): self
    {
        $route = $this->remakeRoute($route);

        $actions_arr = explode('|', $actions);

        $actions_arr = array_map(fn ($action) => trim($action), $actions_arr);

        foreach ($actions_arr as $action) {
            if (in_array($action, $this->actions)) {
                $converter = new PluralConverter();

                if (method_exists($controller, $action) && $action === 'read') {
                    $this->get($converter->convertToPlural($route), [$controller, 'read']);
                }
                if (method_exists($controller, $action) && $action === 'readOne') {

                    $this->get($route . '/{id}', [$controller, 'readOne']);
                }
                if (method_exists($controller, $action) && $action === 'create') {

                    $this->post($route . '/create', [$controller, 'create']);
                }
                if (method_exists($controller, $action) && $action === 'delete') {

                    $this->delete($route . '/delete/{id}', [$controller, 'delete']);
                }
                if (method_exists($controller, $action) && $action === 'deleteAll') {

                    $this->delete($converter->convertToPlural($route) . '/delete-all', [$controller, 'deleteAll']);
                }
                if (method_exists($controller, $action) && $action === 'update') {

                    $this->put($route . '/update/{id}', [$controller, 'update']);
                }
                if (method_exists($controller, $action) && $action === 'search') {
                    $this->get($converter->convertToPlural($route) . '/search', [$controller, 'search']);
                }
            }
        }

        return $this;
    }
}
