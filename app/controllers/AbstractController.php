<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

abstract class AbstractController
{
    /**
     * @param array $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return new JsonResponse($data, $statusCode);
    }
}