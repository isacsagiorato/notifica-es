<?php

namespace App\DAO;

use FW\DB\Connection;

/**
 * Acesso a dados de animais para a API.
 *
 * DAO dedicado (separado do App\DAO\AnimalDAO legado, usado pelo fluxo web):
 * trabalha com arrays, cobre todas as colunas e deixa as PDOException
 * propagarem para o ApiKernel converter no envelope de erro.
 */
class ApiAnimalDAO extends Connection
{
    private const COLUMNS = [
        'nome',
        'data_nascimento',
        'sexo',
        'especie',
        'porte',
        'localizacao',
        'foto',
        'status',
    ];

    public function filter(?string $species, ?string $status): array
    {
        $conditions = [];
        $params = [];

        if ($species !== null && $species !== '') {
            $conditions[] = 'especie = :species';
            $params['species'] = $species;
        }

        if ($status !== null && $status !== '') {
            $conditions[] = 'status = :status';
            $params['status'] = $status;
        }

        $sql = 'SELECT * FROM animal';

        if ($conditions !== []) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY id';

        $stmt = $this->getConn()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->getConn()->prepare('SELECT * FROM animal WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->getConn()->prepare(
            'INSERT INTO animal (nome, data_nascimento, sexo, especie, porte, localizacao, foto, status)
             VALUES (:nome, :data_nascimento, :sexo, :especie, :porte, :localizacao, :foto, :status)'
        );
        $stmt->execute($this->bindable($data));

        return (int) $this->getConn()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $params = $this->bindable($data);
        $params['id'] = $id;

        $stmt = $this->getConn()->prepare(
            'UPDATE animal SET
                nome = :nome,
                data_nascimento = :data_nascimento,
                sexo = :sexo,
                especie = :especie,
                porte = :porte,
                localizacao = :localizacao,
                foto = :foto,
                status = :status
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->getConn()->prepare('DELETE FROM animal WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Garante exatamente as colunas da tabela como parâmetros nomeados,
     * preenchendo com null o que não veio em $data.
     */
    private function bindable(array $data): array
    {
        $bindable = [];

        foreach (self::COLUMNS as $column) {
            $bindable[$column] = $data[$column] ?? null;
        }

        return $bindable;
    }
}
