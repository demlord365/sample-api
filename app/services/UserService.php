<?php

namespace App\services;

use App\utilities\Log\Log;
class UserService extends AbstractService
{
    /**
     * @param string $email
     * @return bool
     */
    public function getUserByEmail(string $email): array
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->query($sql, ['email' => $email]);
        $user = $stmt->fetch();

        return !$user ? [] : $user;
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $token
     * @return void
     */
    public function createUser(string $username, string $email, string $token): void
    {
        $sql = "INSERT INTO users (username, email, token) VALUES (:username, :email, :token)";
        try {
            $this->db->query($sql, [
                'username' => $username,
                'email' => $email,
                'token' => $token
            ]);
        } catch (\PDOException $e) {
            Log::logMessage('error', 'Connection failed: ' . $e->getMessage(), 5);
        }
    }

    public function updateUser(int $userId, array $data):void
    {
        $placeholders = [];
        foreach ($data as $field => $value) {
            $placeholders[] = "{$field} = :{$field}";
        }
        $placeholders = implode(', ', $placeholders);

        $query = "UPDATE users SET {$placeholders} WHERE id = :id";
        $data['id'] = $userId;

        try {
            $this->db->query($query, $data);
        } catch (\PDOException $e) {
            Log::logMessage('error', 'Connection failed: ' . $e->getMessage(), 5);
        }
    }

    public function getUserByToken(string $token): array
    {
        $sql = "SELECT * FROM users WHERE token = :token";
        $stmt = $this->db->query($sql, ['token' => $token]);
        $user = $stmt->fetch();

        return !$user ? [] : $user;
    }


}