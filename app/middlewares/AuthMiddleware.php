<?php

namespace App\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements \Psr\Http\Server\MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //get token from header
        $token = $request->getHeaderLine('Authorization');

        $userService = new \App\services\UserService();

        $userInfo = $userService->getUserByToken($token);

        if (empty($userInfo)) {
            return (new \Laminas\Diactoros\Response\JsonResponse(['error' => 'Unauthorized'], 401));
        }

        return $handler->handle($request->withAttribute('user_id', $userInfo['id']));
    }
}