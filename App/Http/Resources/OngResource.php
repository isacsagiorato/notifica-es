<?php

namespace App\Http\Resources;

use App\Api\ApiResource;

class OngResource extends ApiResource
{
    public function toArray(): array
    {
        return [
            'id' => (int) $this->resource['id'],
            'name' => $this->resource['nome'],
            'cnpj' => $this->resource['cnpj'],
            'status' => $this->resource['status'],
            'animal_count' => $this->resource['quantidade_animais'] !== null ? (int) $this->resource['quantidade_animais'] : null,
            'phone_1' => $this->resource['telefone_1'],
            'phone_2' => $this->resource['telefone_2'],
            'city' => $this->resource['cidade'],
            'state' => $this->resource['estado'],
            'neighborhood' => $this->resource['bairro'],
            'street' => $this->resource['logradouro'],
            'number' => $this->resource['numero'] !== null ? (int) $this->resource['numero'] : null,
            'complement' => $this->resource['complemento'],
            'zip_code' => $this->resource['cep'],
        ];
    }
}
