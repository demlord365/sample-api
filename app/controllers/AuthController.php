<?php

namespace App\controllers;


use App\services\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator as Validator;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;

class AuthController extends AbstractController
{
    private UserService $userService;
    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function signIn(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        $username = (string) $data['username'];
        $email = (string) $data['email'];

        if (!Validator::alnum()->length(2, 40)->validate($username)) {
            $this->jsonResponse(['error' => 'Invalid username'], 400);
        }

        if (!Validator::email()->validate($email)) {
            $this->jsonResponse(['error' => 'Invalid email'], 400);
        }

        $userInfo = $this->userService->getUserByEmail($email);
        //generate token
        $token = $this->generateAccessToken();

        if (empty($userInfo)) {//if user doesn't exist
            $this->userService->createUser($username, $email, $token);
        } else {
            $this->userService->updateUser($userInfo['id'], ['token' => $token]);
        }

       return $this->jsonResponse(['access_token' => $token]);

    }

    private function generateAccessToken(): string
    {
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(md5(uniqid('JWT').time())));

        $token = $config->builder()
            ->permittedFor('http://localhost/')
            ->getToken($config->signer(), $config->signingKey());


        return $token->toString();
    }
}