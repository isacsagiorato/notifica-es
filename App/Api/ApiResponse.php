<?php

namespace App\Api;

class ApiResponse
{
    public static function json($body = [], $status = 200)
    {
        return [
            'status' => $status,
            'body' => $body,
        ];
    }

    public static function data($data = [], $status = 200, $message = null)
    {
        $body = ['data' => $data];

        if ($message !== null) {
            $body['message'] = $message;
        }

        return self::json($body, $status);
    }

    public static function error($code, $message, $status)
    {
        return self::json([
            'error' => $code,
            'message' => $message,
        ], $status);
    }

    public static function normalize($response)
    {
        if (
            is_array($response)
            && array_key_exists('status', $response)
            && array_key_exists('body', $response)
        ) {
            return $response;
        }

        return self::data($response);
    }
}
