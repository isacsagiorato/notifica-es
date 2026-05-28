<?php

namespace App\Controller;

use App\Api\ApiResponse;

class ApiHealthController
{
    public function health()
    {
        return ApiResponse::success([
            'status' => 'ok',
        ]);
    }
}
