<?php

namespace App\Http\Resources;

use App\Api\ApiResource;

class AnimalResource extends ApiResource
{
    public function toArray(): array
    {
        $data = [
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

        // Presente apenas no ranking, onde a contagem de adoções é agregada.
        if (array_key_exists('total_adocoes', $this->resource)) {
            $data['adoption_requests'] = (int) $this->resource['total_adocoes'];
        }

        return $data;
    }
}
