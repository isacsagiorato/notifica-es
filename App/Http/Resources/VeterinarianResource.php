<?php

namespace App\Http\Resources;

use App\Api\ApiResource;

class VeterinarianResource extends ApiResource
{
    public function toArray(): array
    {
        return [
            'id' => (int) $this->resource['id'],
            'name' => $this->resource['nome'],
            'crmv' => $this->resource['crmv'],
            'phone' => $this->resource['telefone'],
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
