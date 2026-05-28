<?php

namespace App\Http\Resources;

use App\Api\ApiResource;
use App\Auth\AuthenticatedUser;

class UserResource extends ApiResource
{
    public function toArray(): array
    {
        if ($this->resource instanceof AuthenticatedUser) {
            return [
                'id' => $this->resource->id,
                'email' => $this->resource->email,
                'type' => $this->resource->type,
                'status' => $this->resource->status,
            ];
        }

        return [
            'id' => (int) $this->resource['id'],
            'email' => $this->resource['email'],
            'type' => $this->resource['tipo_usuario'],
            'status' => $this->resource['status'],
        ];
    }
}
