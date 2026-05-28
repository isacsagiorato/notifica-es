<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private const ALGORITHM = 'HS256';

    public function __construct(
        private readonly string $secret,
        private readonly int $ttlSeconds,
        private readonly string $issuer,
    ) {}

    public static function fromEnvironment(): self
    {
        return new self(
            secret: $_ENV['JWT_SECRET'] ?? 'amigopet_dev_secret_32_bytes_minimum_for_hs256',
            ttlSeconds: (int) ($_ENV['JWT_TTL_SECONDS'] ?? 3600),
            issuer: $_ENV['JWT_ISSUER'] ?? 'amigopet-api',
        );
    }

    public function issue(AuthenticatedUser $user): string
    {
        $issuedAt = time();

        return JWT::encode([
            'iss' => $this->issuer,
            'sub' => (string) $user->id,
            'email' => $user->email,
            'type' => $user->type,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $this->ttlSeconds,
        ], $this->secret, self::ALGORITHM);
    }

    public function decode(string $token): array
    {
        return (array) JWT::decode($token, new Key($this->secret, self::ALGORITHM));
    }

    public function ttlSeconds(): int
    {
        return $this->ttlSeconds;
    }
}
