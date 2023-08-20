<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use League\Route\RouteGroup;
use League\Route\Router;
use Laminas\Diactoros\Response\JsonResponse;
use App\utilities\Log\Log;


$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$router = new Router();

$router->map('POST', 'auth/sign-in', 'App\controllers\AuthController::signIn');

$router->group('/api', function (RouteGroup $routeGroup) {

    $routeGroup->map('POST', '/product/buy_item', 'App\controllers\ProductController::buyItem');
    $routeGroup->map('POST', '/product/rent', 'App\controllers\ProductController::rentItem');
    $routeGroup->map('POST', '/rent/extend', 'App\controllers\ProductController::extendRent');
    $routeGroup->map('POST', '/product/check_status', 'App\controllers\ProductController::checkItemStatus');

})->middleware(new App\middlewares\AuthMiddleware());

$router->map('GET', '*', function () {
    return new JsonResponse(['error' => 'Url not found'], 404);
});

$router->map('POST', '*', function () {
    return new JsonResponse(['error' => 'Url not found'], 404);
});

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals();
$response = $router->dispatch($request);

(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);