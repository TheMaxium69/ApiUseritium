<?php

namespace Model;

class OauthCode extends Model
{
    protected $table = 'oauth_codes';

    public function create(string $clientId, int $userId, string $redirectUri): string
    {
        $code = bin2hex(random_bytes(32));
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (code, client_id, user_id, redirect_uri, expires_at)
             VALUES (:code, :client_id, :user_id, :redirect_uri, DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
        );
        $stmt->execute([
            'code'         => $code,
            'client_id'    => $clientId,
            'user_id'      => $userId,
            'redirect_uri' => $redirectUri,
        ]);
        return $code;
    }

    public function findByCode(string $code): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE code = :code AND expires_at > NOW()"
        );
        $stmt->execute(['code' => $code]);
        return $stmt->fetchObject() ?: null;
    }

    public function consume(string $code): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE code = :code");
        $stmt->execute(['code' => $code]);
    }
}
