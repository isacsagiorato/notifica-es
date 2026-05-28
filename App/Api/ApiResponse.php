<?php

namespace App\Api;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiResponse
{
    public static function success($data = [], array $meta = [], int $status = 200): array
    {
        return [
            'status' => $status,
            'body' => [
                'data' => self::normalizeData($data),
                'meta' => $meta,
            ],
        ];
    }

    public static function data($data = [], int $status = 200): array
    {
        return self::success($data, [], $status);
    }

    public static function paginated($data, int $page, int $perPage, int $total, int $status = 200): array
    {
        $lastPage = max(1, (int) ceil($total / max(1, $perPage)));

        return self::success($data, [
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ], $status);
    }

    public static function error(string $code, string $message, int $status, array $details = []): array
    {
        return [
            'status' => $status,
            'body' => [
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => $details,
                ],
            ],
        ];
    }

    public static function fromHttpException(HttpExceptionInterface $exception): array
    {
        $status = $exception->getStatusCode();
        $details = [];

        if ($status === 405 && isset($exception->getHeaders()['Allow'])) {
            $details['allowed_methods'] = array_map('trim', explode(',', $exception->getHeaders()['Allow']));
        }

        return self::error(
            self::codeForStatus($status),
            $exception->getMessage() ?: self::messageForStatus($status),
            $status,
            $details
        );
    }

    public static function normalize($response): array
    {
        if (
            is_array($response)
            && array_key_exists('status', $response)
            && array_key_exists('body', $response)
        ) {
            return $response;
        }

        return self::success($response);
    }

    private static function normalizeData($data)
    {
        if ($data instanceof ApiResource || $data instanceof ApiResourceCollection) {
            return $data->toArray();
        }

        if (is_array($data)) {
            return array_map(fn ($item) => self::normalizeData($item), $data);
        }

        return $data;
    }

    private static function codeForStatus(int $status): string
    {
        return match ($status) {
            400 => 'bad_request',
            401 => 'unauthenticated',
            403 => 'forbidden',
            404 => 'not_found',
            405 => 'method_not_allowed',
            422 => 'validation_error',
            default => 'http_error',
        };
    }

    private static function messageForStatus(int $status): string
    {
        return match ($status) {
            400 => 'Requisição inválida.',
            401 => 'Não autenticado.',
            403 => 'Acesso negado.',
            404 => 'Rota da API não encontrada.',
            405 => 'Método HTTP não permitido para esta rota.',
            422 => 'Dados inválidos.',
            default => 'Erro ao processar a requisição.',
        };
    }
}
