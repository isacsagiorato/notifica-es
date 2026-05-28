<?php

namespace App\DAO;

use FW\DB\Connection;

class LoginDAO extends Connection
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->getConn()->prepare(
            'SELECT id, email, senha, tipo_usuario, status FROM login WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->getConn()->prepare(
            'SELECT id, email, senha, tipo_usuario, status FROM login WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->getConn()->prepare('UPDATE login SET senha = :password WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'password' => $hash,
        ]);
    }
}
