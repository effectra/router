# Effectra Router Library

Effectra PHP Router is a lightweight and flexible routing library for PHP applications. It provides a convenient way to define routes and dispatch incoming requests to the appropriate controllers or callbacks.

## Installation

You can install the Effectra PHP Router library via Composer. Run the following command in your project directory:

```
composer require effectra/router
```

## Usage

To get started with Effectra PHP Router, follow these steps:

1. Include the autoloader if you haven't already done so:

```php
require_once 'vendor/autoload.php';
```

2. Import the necessary classes:

```php
use Effectra\Router\Route;
use Effectra\Router\RouteGroup;

use Effectra\Http\Foundation\RequestFoundation;
use Effectra\Http\Message\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
```

3. Create an instance of the `Route` class:

```php
//pass response class to the router
$response = new Response();

$route = new Route($response);
```

4. Define routes using the available methods:

```php
$route->get('/home', function () {
    return 'Welcome to the home page!';
});

$route->post('/contact', 'StaticController@login');

$router->get('/', [HomeController::class, 'index']);

$router->get('/users/', [UserController::class, 'index']);


$router->post('/signin', function (RequestInterface $request, ResponseInterface $response) {
    return $response->json([
        'message' => 'hello world'
    ]);
});

$route->crud('/report', ReportController::class, 'read|readOne|create|delete|deleteAll|search');

$route->auth('/auth/', AuthController::class);

$route->group('/file/upload/', UploadController::class, function(RouteGroup $router){
    $router->post('audio','createAudio');
    $router->post('video','createVideo');
});
```

5. Dispatch the incoming request:

```php

$request = RequestFoundation::createFromGlobals();

$route->dispatch($request);
```

### Route Methods

The `Route` class provides several methods to define routes:

- `get($pattern, $callback)`: Defines a GET route.
- `post($pattern, $callback)`: Defines a POST route.
- `put($pattern, $callback)`: Defines a PUT route.
- `delete($pattern, $callback)`: Defines a DELETE route.
- `patch($pattern, $callback)`: Defines a PATCH route.
- `options($pattern, $callback)`: Defines an OPTIONS route.
- `any($pattern, $callback)`: Defines a route that matches any HTTP method.
- `register($method, $pattern, $callback)`: Defines a custom route with a specific HTTP method.
- `routes()`: Returns an array of defined routes.

### Middleware

The `Route` class supports middleware for route groups. You can use the `middleware` method to add middleware to a group of routes:

```php
$router->get('/users/{id}', [UserController::class, 'show'])->middleware(new AuthMiddleware());

```

### Error Handling

The `Route` class provides methods to handle 404 Not Found and 500 Internal Server Error responses:

```php
$route->setNotFound(function () {
    // Handle 404 Not Found response
});

$route->setInternalServerError(function () {
    // Handle 500 Internal Server Error response
});
```

## Contributing

Contributions to the Effectra PHP Router library are welcome! If you encounter any issues or have suggestions for improvements, please feel free to open an issue or submit a pull request.

## License

The Effectra PHP Router library is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---