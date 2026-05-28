<?php

namespace App\Api;

use App\Auth\AuthenticatedUser;
use Symfony\Component\HttpFoundation\Request;

class ApiRequest
{
    private ?AuthenticatedUser $authenticatedUser = null;

    public function __construct(private Request $request) {}

    public static function from($requestOrMethod = null, $uri = null): self
    {
        if ($requestOrMethod instanceof Request) {
            return new self($requestOrMethod);
        }

        if (is_string($requestOrMethod)) {
            return new self(Request::create($uri ?: '/', strtoupper($requestOrMethod)));
        }

        return new self(Request::createFromGlobals());
    }

    public function bearerToken(): ?string
    {
        $authorization = trim($this->request->headers->get('Authorization', ''));

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]) ?: null;
    }

    public function data(): array
    {
        if (str_contains($this->request->headers->get('Content-Type', ''), 'application/json')) {
            $data = json_decode($this->request->getContent(), true);

            return is_array($data) ? $data : [];
        }

        return $this->request->request->all();
    }

    public function setAuthenticatedUser(AuthenticatedUser $user): void
    {
        $this->authenticatedUser = $user;
    }

    public function authenticatedUser(): ?AuthenticatedUser
    {
        return $this->authenticatedUser;
    }

    public function method(): string
    {
        return $this->request->getMethod();
    }

    public function path(): string
    {
        return $this->request->getPathInfo() ?: '/';
    }

    public function toSymfonyRequest(): Request
    {
        return $this->request;
    }
}
