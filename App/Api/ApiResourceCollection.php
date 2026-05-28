<?php

namespace App\Api;

class ApiResourceCollection
{
    public function __construct(
        private string $resourceClass,
        private iterable $resources
    ) {}

    public function toArray(): array
    {
        $items = [];

        foreach ($this->resources as $resource) {
            $items[] = (new $this->resourceClass($resource))->toArray();
        }

        return $items;
    }
}
