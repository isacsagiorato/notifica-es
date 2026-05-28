<?php

namespace App\Controller;

use App\Api\ApiResponse;

class ApiHealthController
{
    public function health()
    {
        return ApiResponse::json([
            'status' => 'ok',
        ]);
    }
}
