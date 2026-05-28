<?php

namespace App\DAO;

use FW\DB\Connection;

class PublicCatalogDAO extends Connection
{
    private const TABLES = [
        'animals' => 'animal',
        'species' => 'especie',
        'breeds' => 'raca',
        'ongs' => 'ong',
        'clinics' => 'clinica',
        'veterinarians' => 'veterinario',
    ];

    public function list(string $entity): array
    {
        $table = $this->tableFor($entity);
        $stmt = $this->getConn()->query("SELECT * FROM {$table} ORDER BY id");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(string $entity, int $id): ?array
    {
        $table = $this->tableFor($entity);
        $stmt = $this->getConn()->prepare("SELECT * FROM {$table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function tableFor(string $entity): string
    {
        if (! isset(self::TABLES[$entity])) {
            throw new \InvalidArgumentException("Entidade pública inválida: {$entity}");
        }

        return self::TABLES[$entity];
    }
}
