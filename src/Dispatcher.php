<?php

declare(strict_types=1);

namespace Effectra\Router;

use Effectra\Http\Message\Stream;
use Effectra\Http\Server\RequestHandler;
use Effectra\Router\Exception\InvalidCallbackException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

trait Dispatcher
{
    /**
     * @var ServerRequestInterface The request object.
     */
    private ServerRequestInterface $request;

    /**
     * @var ResponseInterface The response object.
     */
    private ResponseInterface $response;

    /**
     * @var array The route arguments.
     */
    private array $args = [];

    /**
     * @var mixed The value for the not found route.
     */
    protected $notFound;

    /**
     * Dispatcher constructor.
     */
    public function __construct()
    {
        $this->callback = new Callback();
    }

    /**
     * set router container for binding and injected dependencies of controller class
     * @param $container
     * @return void
     */
    public function setContainer($container): void
    {
        $this->callback->setContainer($container);
    }

    /**
     * Dispatches the server request and returns a response.
     *
     * @param ServerRequestInterface $request The server request.
     * @return ResponseInterface The response.
     * @throws InvalidCallbackException If there is an error processing the response.
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        // Extract the URI path and HTTP method from the request.
        $uri_path = $request->getUri()->getPath();
        $method = strtolower($request->getMethod());

        // Determine the appropriate controller action for the request.
        $action = $this->getAction($uri_path, $method);

        // Get the callback function for the selected controller action.
        $callback = isset($action['callback']) ? $this->callback->getCallback($action['callback']) : null;


        $controller = new Controller($request, $this->response, $this->args, $callback);

        // send 404 response if no callback
        if (!$callback) {

            if (!$this->notFound) {

               return $this->notFoundResponse();

            }

            $controller->setCallback($this->notFound);
        }

        // handle router middlewares
        if (!empty($action['middleware'])) {

            $handler = new RequestHandler($this->response, $action['middleware']);

            $responseMiddleware = $this->runMiddleware($this->request, $handler);

            $controller->setRequest($handler->getLastRequest());

            $response = $controller->handle();

            $response = $this->compareResponses($this->response, $responseMiddleware) ? $response : $responseMiddleware;
        } else {
            $response = $controller->handle();
        }

        // regenerate response if its string
        if (is_string($response)) {
            $response = $this->stringResponse($response);
        }


        return $response;
    }

    /**
     * Sets the HTTP request to be sent to the controller.
     *
     * @param ServerRequestInterface $request The HTTP request to send to the client.
     * @return void
     */
    public function addRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Sets the HTTP response to be sent to the controller.
     *
     * @param ResponseInterface $response The HTTP response to send to the client.
     * @return void
     */
    public function addResponse(ResponseInterface $response): void
    {
        $this->response = $response;
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
     * Sets the specified callback as the response returned when a route is not found.
     *
     * @param callable $response The callback to set as the not found response.
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
     * @return void
     */
    public function setInternalServerError(callable $response): void
    {
        $this->internalServerError = $response;
    }

    /**
     * Converts the request object.
     *
     * @param  ServerRequestInterface $request The request object to convert.
     * @param  ServerRequestInterface $NewRequest The new request object to update.
     * @return ServerRequestInterface The updated new request object.
     */
    public function convertRequest(ServerRequestInterface $request, ServerRequestInterface $NewRequest): ServerRequestInterface
    {

        $NewRequest = $NewRequest->withMethod($request->getMethod());
        $NewRequest = $NewRequest->withUri($request->getUri());

        foreach ($request->getHeaders() as $key => $value) {
            $NewRequest = $NewRequest->withHeader($key, $value);
        }

        $NewRequest = $NewRequest->withBody($request->getBody());
        $NewRequest = $NewRequest->withProtocolVersion($request->getProtocolVersion());
        $NewRequest = $NewRequest->withQueryParams($request->getQueryParams());
        $NewRequest = $NewRequest->withParsedBody($request->getParsedBody());

        foreach ($request->getAttributes() as $key => $value) {
            $NewRequest = $NewRequest->withAttribute($key, $value);
        }

        return $NewRequest;
    }

    /**
     * Compares two response objects.
     *
     * @param ResponseInterface $response1 The first response object.
     * @param ResponseInterface $response2 The second response object.
     * @return bool True if the responses are equal, false otherwise.
     */
    public function compareResponses(ResponseInterface $response1, ResponseInterface $response2): bool
    {
        // Compare status codes
        if ($response1->getStatusCode() !== $response2->getStatusCode()) {
            return false;
        }

        // Compare headers
        $headers1 = $response1->getHeaders();
        $headers2 = $response2->getHeaders();

        if (count($headers1) !== count($headers2)) {
            return false;
        }

        foreach ($headers1 as $name => $values) {
            if (!isset($headers2[$name]) || $headers1[$name] !== $headers2[$name]) {
                return false;
            }
        }

        // Compare bodies
        $body1 = (string) $response1->getBody();
        $body2 = (string) $response2->getBody();

        return $body1 === $body2;
    }

    /**
     * Generate a response with a string body.
     *
     * @param string $response The response string.
     * @return ResponseInterface The generated response.
     */
    public function stringResponse(string $response): ResponseInterface
    {
        return $response = $this->response
            ->withStatus(200)
            ->withBody(new Stream($response))
            ->withHeader('Content-type', ['text/html; charset=UTF-8']);
    }

    /**
     * Generate a "Not Found" response.
     *
     * @return ResponseInterface The "Not Found" response.
     */
    public function notFoundResponse(): ResponseInterface
    {
        $content = new Stream(HtmlRender::notFoundHTML());

        return $this->response->withStatus(404)->withBody($content);
    }
}
