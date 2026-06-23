<?php

namespace Model;

class OauthClient extends Model
{
    protected $table = 'oauth_clients';

    public function findById(string $clientId): ?object
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $clientId]);
        return $stmt->fetchObject() ?: null;
    }
}
