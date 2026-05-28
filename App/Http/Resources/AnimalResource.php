<?php

namespace App\Http\Resources;

use App\Api\ApiResource;

class AnimalResource extends ApiResource
{
    public function toArray(): array
    {
        return [
            'id' => (int) $this->resource['id'],
            'name' => $this->resource['nome'],
            'birth_date' => $this->resource['data_nascimento'],
            'sex' => $this->resource['sexo'],
            'species' => $this->resource['especie'],
            'size' => $this->resource['porte'],
            'location' => $this->resource['localizacao'],
            'photo' => $this->resource['foto'],
            'status' => $this->resource['status'],
        ];
    }
}
