<?php

namespace App\Http\Resources;

use App\Api\ApiResource;

class SpeciesResource extends ApiResource
{
    public function toArray(): array
    {
        return [
            'id' => (int) $this->resource['id'],
            'name' => $this->resource['nome'],
        ];
    }
}
