<?php
echo '<pre>';

use App\Controllers\UsersController;
use App\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Doctrine\DBAL\DriverManager;

require_once 'vendor/autoload.php';



$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/articles', [UsersController::class, 'index']);
    $r->addRoute('GET', '/articles/{id:\d+}', [UsersController::class, 'show']); // {id} must be a number (\d+)
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        var_dump('404 Not Found');
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        var_dump('405 Method Not Allowed');
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller = $routeInfo[1][0];
        $method = $routeInfo[1][1];
        $vars = $routeInfo[2];

        /** @var View $response */
        $response = (new $controller)->$method($vars);

        $loader = new FilesystemLoader('app/Views');
        $twig = new Environment($loader);

        echo $twig->render($response->getPath(), $response->getVariables());

        break;
}

// ---------------------------------------------------------------------------------------------------------------------

