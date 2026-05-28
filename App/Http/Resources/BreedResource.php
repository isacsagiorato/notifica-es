<?php

namespace App\Http\Resources;

use App\Api\ApiResource;

class BreedResource extends ApiResource
{
    public function toArray(): array
    {
        return [
            'id' => (int) $this->resource['id'],
            'name' => $this->resource['nome'],
            'species_id' => (int) $this->resource['fk_especie_id'],
        ];
    }
}
