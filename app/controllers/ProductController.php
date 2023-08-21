<?php

namespace App\controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\services\ProductService;
use Respect\Validation\Validator as Validator;

class ProductController extends AbstractController
{
    private ProductService $productService;
    private int $itemId;
    private int $userId;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function buyItem(ServerRequestInterface $request): ResponseInterface
    {
        $validationErrors = $this->validateFields($request, ['item_id']);

        if (!empty($validationErrors)) {
            return $this->jsonResponse(['errors' => $validationErrors], 400);
        }

        $userId = $request->getAttribute('user_id');
        $itemId = (int) $request->getParsedBody()['item_id'];

        $productStatus = $this->productService->getProductStatus($userId, $itemId);
        if ($productStatus === 'purchase') {
            return $this->jsonResponse(['error' => 'Product already purchased'], 400);
        } elseif ($productStatus === 'rent') {
            return $this->jsonResponse(['error' => 'Product already rented'], 400);

        }


        $this->productService->buyProduct($userId, $itemId);


        return $this->jsonResponse(['success' => "Product {$itemId} was bought"], 200);
    }

    public function rentItem(ServerRequestInterface $request): ResponseInterface
    {

        $validationErrors = $this->validateFields($request, ['item_id', 'rent_start', 'rent_end']);

        if (!empty($validationErrors)) {
            return $this->jsonResponse(['errors' => $validationErrors], 400);
        }


        $userId = $request->getAttribute('user_id');
        $itemId = (int) $request->getParsedBody()['item_id'];
        $rentStart = (string) $request->getParsedBody()['rent_start'];
        $rentEnd = (string) $request->getParsedBody()['rent_end'];

        $productStatus = $this->productService->getProductStatus($userId, $itemId);

        if ($productStatus === 'purchase') {
            return $this->jsonResponse(['error' => 'Product already purchased'], 400);
        } elseif ($productStatus === 'rent') {
            return $this->jsonResponse(['error' => 'Product already rented'], 400);

        }

        $this->productService->rentProduct($userId, $itemId, $rentStart, $rentEnd);

        return $this->jsonResponse(['success' => "Product {$itemId} was rented"], 200);

    }

    public function extendRent(ServerRequestInterface $request): ResponseInterface
    {
        $validationErrors = $this->validateFields($request, ['item_id', 'rent_end']);

        if (!empty($validationErrors)) {
            return $this->jsonResponse(['errors' => $validationErrors], 400);
        }


        $userId = $request->getAttribute('user_id');
        $itemId = (int) $request->getParsedBody()['item_id'];
        $rentEnd = (string) $request->getParsedBody()['rent_end'];

        $rentInfo = $this->productService->getRentInfo($userId, $itemId);

        if (empty($rentInfo)) {
            return $this->jsonResponse(['error' => 'Product not rented'], 400);
        }

        if ((strtotime($rentInfo['rent_end']) - strtotime($rentEnd)) > 86400) {
            return $this->jsonResponse(['error' => 'The rent can be extended for no more than 24 hours'], 400);
        }


        $this->productService->extendRent($userId, $itemId, $rentEnd);

        return $this->jsonResponse(['success' => "The rent for an item {$itemId} has been successfully extend"], 200);

    }

    public function checkItemStatus(ServerRequestInterface $request): ResponseInterface
    {
        $validationErrors = $this->validateFields($request, ['item_id']);

        if (!empty($validationErrors)) {
            return $this->jsonResponse(['errors' => $validationErrors], 400);
        }

        $userId = $request->getAttribute('user_id');
        $itemId = (int) $request->getParsedBody()['item_id'];

        $productStatus = $this->productService->getProductStatus($userId, $itemId);

        if ($productStatus == 'rent') {
            //checking if rent still active
            $rentInfo = $this->productService->getRentInfo($userId, $itemId);
            if (strtotime($rentInfo['end_rent']) < time()) {
                //rent is expired
                $this->productService->deleteRent($userId, $itemId);
                return $this->jsonResponse(['status' => 'Rent is expired'], 200);
            }

        } elseif(empty($productStatus)) {
            return $this->jsonResponse(['error' => 'Product not found'], 404);
        }

        $productCode = $this->generateUniqueCode();
        $this->productService->updateProductCode($userId, $itemId, $productCode);

        return $this->jsonResponse([
            'status' => $productStatus,
            'product_code' => $productCode
        ], 200);
    }

    private function getItemId(ServerRequestInterface $request): int
    {
        return (int)$request->getAttribute('item_id');
    }

    private function generateUniqueCode(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function validateFields(ServerRequestInterface $request, array $requiredFields): array
    {
        $errors = [];
        foreach ($requiredFields as $field) {
            if (!isset($request->getParsedBody()[$field])) {
                $errors[] = "{$field} is required";
            } elseIf($field == 'item_id' && !$this->productService->isProductExist($request->getParsedBody()[$field])) {
                $errors[] = "Product not found";
            } elseif ($field == 'rent_start' && !Validator::dateTime('Y-m-d H:i:s')->validate($request->getParsedBody()[$field])) {
                $errors[] = "Invalid rent_start";
            } elseif ($field == 'rent_end' && !Validator::dateTime('Y-m-d H:i:s')->validate($request->getParsedBody()[$field])) {
                $errors[] = "Invalid rent_end";
            } elseif ($field == 'rent_end' && $request->getParsedBody()['rent_start'] > $request->getParsedBody()['rent_end']) {
                $errors[] = "rent_end must be greater than rent_start";
            } elseif ($field == 'rent_end' && strtotime($request->getParsedBody()['rent_end']) > (time() + 86400)) {
                $errors[] = "Rent period can not be more than 24 hours";
            } elseif ($field == 'rent_end' && strtotime($request->getParsedBody()['rent_end']) < time()) {
                $errors[] = "Rent period can not be in the past";

            }
        }

        return $errors;
    }

}
