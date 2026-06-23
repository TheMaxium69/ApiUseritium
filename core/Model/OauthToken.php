<?php

namespace Model;

class OauthToken extends Model
{
    protected $table = 'oauth_tokens';

    public function create(int $userId, string $clientId): string
    {
        $token = bin2hex(random_bytes(48));
        $stmt  = $this->pdo->prepare(
            "INSERT INTO {$this->table} (access_token, user_id, client_id, expires_at)
             VALUES (:token, :user_id, :client_id, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        );
        $stmt->execute([
            'token'     => $token,
            'user_id'   => $userId,
            'client_id' => $clientId,
        ]);
        return $token;
    }

    public function findByToken(string $token): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE access_token = :token AND expires_at > NOW()"
        );
        $stmt->execute(['token' => $token]);
        return $stmt->fetchObject() ?: null;
    }
}
