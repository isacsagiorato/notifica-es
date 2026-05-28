<?php

namespace App\Auth;

class AuthenticatedUser
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $type,
        public readonly string $status,
    ) {}

    public static function fromLoginRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            email: (string) $row['email'],
            type: (string) $row['tipo_usuario'],
            status: (string) $row['status'],
        );
    }
}
