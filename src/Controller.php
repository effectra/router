<?php


declare(strict_types=1);

namespace Effectra\Router;

use Effectra\Router\Exception\InvalidCallbackException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Controller
 *
 * Represents a controller that handles requests and generates responses.
 */
class Controller
{
    protected ServerRequestInterface $request;
    protected ResponseInterface $response;
    protected $callback;
    protected array $args;

    /**
     * Controller constructor.
     *
     * @param ServerRequestInterface $request The server request.
     * @param ResponseInterface $response The response.
     * @param array $args The arguments.
     * @param null|callable $callback The callback function to handle the request.
     *
     * @return void
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response, array $args, ?callable $callback = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $this->callback = $callback;
    }

    /**
     * Get the server request.
     *
     * @return ServerRequestInterface The server request.
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Set the server request.
     *
     * @param ServerRequestInterface $request The server request.
     *
     * @return void
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Get the response.
     *
     * @return ResponseInterface The response.
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the response.
     *
     * @param ResponseInterface $response The response.
     *
     * @return void
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * Get the arguments.
     *
     * @return array The arguments.
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Set the arguments.
     *
     * @param array $args The arguments.
     *
     * @return void
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    /**
     * Convert the controller to an array.
     *
     * @return array The controller as an array.
     */
    public function toArray(): array
    {
        return [
            $this->request,
            $this->response,
            (object) $this->args
        ];
    }

    /**
     * Set the callback function to handle the request.
     *
     * @param callable $callback The callback function.
     *
     * @return void
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Get the callback function.
     *
     * @return callable|null The callback function or null if not set.
     */
    public function getCallback(): callable|null
    {
        return $this->callback;
    }

    /**
     * Handle the request and return the response.
     *
     * @throws InvalidCallbackException If the callback is not set.
     *
     * @return ResponseInterface|string The response returned from controller.
     */
    public function handle(): ResponseInterface|string
    {
        $this->args = array_merge($this->args, $this->request->getQueryParams());

        if (!$this->callback) {
            throw new InvalidCallbackException("Error Processing Response");
        }

        $response = call_user_func_array($this->callback, $this->toArray());

        return $response;
    }
}
