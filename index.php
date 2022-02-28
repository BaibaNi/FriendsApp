<?php
//echo '<pre>';

require_once 'vendor/autoload.php';

session_start();

use App\Controllers\MainController;
use App\Controllers\UsersController;
use App\Controllers\ArticlesController;
use App\Controllers\AccessController;
use App\View;
use App\Redirect;
use Twig\Environment;
use Twig\Extra\CssInliner\CssInlinerExtension;
use Twig\Loader\FilesystemLoader;


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
//---MAIN VIEW
    $r->addRoute('GET', '/', [MainController::class, 'index']);


//---USERS
    $r->addRoute('GET', '/users', [UsersController::class, 'index']);
    $r->addRoute('GET', '/users/{id:\d+}', [UsersController::class, 'show']);
//---register
    $r->addRoute('POST', '/users', [UsersController::class, 'register']);
    $r->addRoute('GET', '/users/register', [UsersController::class, 'getRegister']);


//---LOGIN
    $r->addRoute('GET', '/users/welcome/{id:\d+}', [AccessController::class, 'getWelcome']);
    $r->addRoute('POST', '/users/welcome', [AccessController::class, 'login']);
    $r->addRoute('GET', '/users/login', [AccessController::class, 'getLogin']);


//---LOGOUT
    $r->addRoute('POST', '/', [AccessController::class, 'logout']);


//---ARTICLES
    $r->addRoute('GET', '/articles', [ArticlesController::class, 'index']);
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticlesController::class, 'show']);
//---create
    $r->addRoute('POST', '/articles', [ArticlesController::class, 'store']);
    $r->addRoute('GET', '/articles/create', [ArticlesController::class, 'create']);
//---delete
    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticlesController::class, 'delete']);
//---edit/update
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticlesController::class, 'update']);
    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticlesController::class, 'edit']);

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
        $twig->addExtension(new CssInlinerExtension());
        $twig->addGlobal('session', $_SESSION);

        if($response instanceof View)
        {
            echo $twig->render($response->getPath() . '.html', $response->getVariables());
        }

        if($response instanceof Redirect)
        {
            header('Location: ' . $response->getLocation());
            exit;
        }

        break;
}
?>
