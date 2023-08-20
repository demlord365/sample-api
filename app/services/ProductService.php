<?php

namespace App\services;

use App\utilities\Log\Log;

class ProductService extends AbstractService
{
    /**
     * @param int $productId
     * @return bool
     */
    public function isProductExist(int $productId): bool
    {
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $productId]);
        $product = $stmt->fetch();
        return $product ? true : false;
    }

    public function buyProduct(int $userId, int $productId): void
    {
        $purchaseSql = "INSERT INTO purchases (user_id, product_id, purchase_time) VALUES (:user_id, :product_id, :purchase_time)";
        $productStatusSql = "REPLACE INTO product_status set user_id=:user_id, product_id=:product_id, status='purchase'";
        try {
            $this->db->getPdoInstance()->beginTransaction();

            $this->db->query($purchaseSql, [
                'user_id' => $userId,
                'product_id' => $productId,
                'purchase_time' => date('Y-m-d H:i:s'),
            ]);

            $this->db->query($productStatusSql, [
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            $this->db->getPdoInstance()->commit();
        } catch (\PDOException $e) {
            $this->db->getPdoInstance()->rollBack();
            Log::logMessage('error', 'Connection failed: ' . $e->getMessage(), 5);
        }
    }

    public function rentProduct(int $userId, int $productId, string $startRent, string $endRent): void
    {
        $rentSql = "INSERT INTO rents (user_id, product_id, start_rent, end_rent) VALUES (:user_id, :product_id, :start_rent, :end_rent)";
        $productStatusSql = "REPLACE INTO product_status set user_id=:user_id, product_id=:product_id, status='rent'";
        try {
            $this->db->getPdoInstance()->beginTransaction();

            $this->db->query($rentSql, [
                'user_id' => $userId,
                'product_id' => $productId,
                'start_rent' => $startRent,
                'end_rent' => $endRent,
            ]);

            $this->db->query($productStatusSql, [
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            $this->db->getPdoInstance()->commit();
        } catch (\PDOException $e) {
            $this->db->getPdoInstance()->rollBack();
            Log::logMessage('error', 'Connection failed: ' . $e->getMessage(), 5);
        }
    }

    public function extendRent(int $userId, int $productId, string $endRent): void
    {
        $sql = "UPDATE rents SET end_rent = :end_rent WHERE user_id = :user_id AND product_id = :product_id";
        try {
            $this->db->query($sql, [
                'user_id' => $userId,
                'product_id' => $productId,
                'end_rent' => $endRent,
            ]);
        } catch (\PDOException $e) {
            Log::logMessage('error', 'Connection failed: ' . $e->getMessage(), 5);
        }
    }

    public function getProductStatus(int $userId, int $productId): string
    {
        $sql = "SELECT status FROM product_status WHERE product_id = :product_id and user_id = :user_id";
        $stmt = $this->db->query($sql, ['product_id' => $productId, 'user_id' => $userId]);
        $productStatus = $stmt->fetch();
        return $productStatus['status'] ?? '';
    }

    public  function getRentInfo(int $userId, int $productId): array
    {
        $sql = "SELECT * FROM rents WHERE product_id = :product_id and user_id = :user_id";
        $stmt = $this->db->query($sql, ['product_id' => $productId, 'user_id' => $userId]);
        $rentInfo = $stmt->fetch();

        return !$rentInfo? [] : $rentInfo;
    }

    public function deleteRent(int $userId, int $productId): void
    {
        $rentSql = "DELETE FROM rents WHERE product_id = :product_id and user_id = :user_id";
        $productStatusSql = "Delete FROM product_status WHERE product_id = :product_id and user_id = :user_id";
        try {
            $this->db->getPdoInstance()->beginTransaction();

            $this->db->query($rentSql, ['product_id' => $productId, 'user_id' => $userId]);
            $this->db->query($productStatusSql, ['product_id' => $productId, 'user_id' => $userId]);

            $this->db->getPdoInstance()->commit();
        } catch (\PDOException $e) {
            $this->db->getPdoInstance()->rollBack();
            Log::logMessage('error', 'Connection failed: ' . $e->getMessage(), 5);
        }
    }

    public function updateProductCode(int $userId, int $productId, string $productCode): void
    {
        $sql = "UPDATE product_status SET product_code = :product_code WHERE user_id = :user_id AND product_id = :product_id";

        try {
            $this->db->query($sql, [
                'user_id' => $userId,
                'product_id' => $productId,
                'product_code' => $productCode,
            ]);
        } catch (\PDOException $e) {
            Log::logMessage('error', 'Connection failed: ' . $e->getMessage(), 5);
        }
    }


}