<?php

declare(strict_types=1);

namespace Effectra\Router;

use Effectra\Http\Message\Stream;
use Effectra\Http\Server\RequestHandler;
use Effectra\Router\Exception\InvalidCallbackException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

trait Dispatcher
{

    /**
     * @var Callback The callback instance.
     */
    protected Callback $callback;

    /**
     * @var RequestInterface The request object.
     */
    private RequestInterface $request;

    /**
     * @var array The route arguments.
     */
    private array $args = [];

    /**
     * @var mixed The value for the not found route.
     */
    protected $notFound;

    /**
     * @param ResponseInterface $response The response object.
     */

    public function __construct(
        protected ResponseInterface  $response
    ) {
        $this->callback = new Callback();
        $this->args = [];
    }

    /**
     * Dispatches the current request to the appropriate controller action and returns the HTTP response.
     *
     * @param ServerRequestInterface $request The HTTP request to dispatch.
     *
     * @return ResponseInterface The HTTP response returned by the controller action.
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        // Extract the URI path and HTTP method from the request.
        $uri_path = $request->getUri()->getPath();
        $method = strtolower($request->getMethod());

        if (!empty($this->middleware)) {
            // handle middleware
            $this->response = $this->runMiddleware($this->middleware, $request, new RequestHandler($this->middleware));
        }

        // Add any query string arguments to the internal argument list.
        $this->addArguments($request->getQueryParams());

        // Determine the appropriate controller action for the request.
        $action = $this->getAction($uri_path, $method);

        // Get the callback function for the selected controller action.
        $callback = $this->callback->getCallback($action);

        // Pass the request, response, and arguments to the controller action.
        $pass = $this->pass((object) $this->args);

        // If no valid callback was found, return a 404 Not Found response.
        if (!$callback) {
            if (!$this->notFound) {
                $content = new Stream(HtmlRender::notFoundHTML());

                return $this->response
                    ->withStatus(404)
                    ->withBody($content);
            }
            return call_user_func($this->notFound, $pass);
        }

        // Execute the controller action and return the resulting response.
        $response = $this->process($callback, $pass);

        return $response;
    }

    /**
     * Adds the specified arguments to the internal argument list.
     *
     * @param array $args The arguments to add.
     *
     * @return void
     */
    public function addArguments(array $args): void
    {
        // Merge the specified arguments with the existing arguments.
        $this->args = array_merge($this->args, $args);
    }
    /**
     * Sets the HTTP request to be sent to the controller.
     *
     * @param RequestInterface $response The HTTP response to send to the client.
     *
     * @return void
     */
    public function addRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }
    /**
     * Sets the HTTP response to be sent to the controller.
     *
     * @param ResponseInterface $response The HTTP response to send to the client.
     *
     * @return void
     */
    public function addResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
    /**
     * Passes the server request to another part of the application for further processing.
     *
     * @param ServerRequestInterface $server_request The incoming server request object.
     * @param ResponseInterface $response The response object to use for generating a response.
     * @param array $args An array of route parameters extracted from the request URI.
     *
     * @return array An array containing the new request object, the original response object, and the route parameters.
     */
    public function pass(array|object $args)
    {
        return [
            $this->request,
            $this->response,
            $args
        ];
    }

    /**
     * Calls the specified callback with the given parameters and returns the response.
     *
     * @param callable $callback The callback to call.
     * @param array $pass The parameters to pass to the callback.
     *
     * @return ResponseInterface The response returned by the callback.
     */
    public function process(callable $callback, array $pass): ResponseInterface
    {
        $response = call_user_func_array($callback, $pass);

        if (!$response) {
            throw new InvalidCallbackException("Error Processing Response");
        }
        if (is_string($response)) {
            $response = $this->response
                ->withStatus(200)
                ->withBody(new Stream($response))
                ->withHeader('Content-type', ['text/html; charset=UTF-8']);
        }
        return  $response;
    }

    /**
     * Sets the specified callback as the response returned when a route is not found.
     *
     * @param callable $response The callback to set as the not found response.
     *
     * @return void
     */
    public function setNotFound(callable $response): void
    {
        $this->notFound = $response;
    }


    /**
     * Sets the specified callback as the response returned when an internal server error occurs.
     *
     * @param callable $response The callback to set as the internal server error response.
     *
     * @return void
     */
    public function setInternalServerError(callable $response): void
    {
        $this->internalServerError = $response;
    }
}
