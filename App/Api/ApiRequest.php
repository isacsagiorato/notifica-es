<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\Request;

class ApiRequest
{
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
