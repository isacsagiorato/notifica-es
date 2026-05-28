<?php

namespace App\Api;

abstract class ApiResource
{
    public function __construct(protected mixed $resource) {}

    abstract public function toArray(): array;

    public static function collection(iterable $resources): ApiResourceCollection
    {
        return new ApiResourceCollection(static::class, $resources);
    }
}
