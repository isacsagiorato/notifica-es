<?php

namespace App\DAO;

use FW\DB\Connection;

class RankingDAO extends Connection
{
    /**
     * Animais ordenados pelo número de solicitações de adoção (mais adotados primeiro).
     * O LEFT JOIN garante que animais sem solicitações também apareçam (com total 0).
     */
    public function mostAdopted(): array
    {
        $sql = 'SELECT a.*, COUNT(s.id) AS total_adocoes
                FROM animal a
                LEFT JOIN solicitacao_adocao s ON s.fk_animal_id = a.id
                GROUP BY a.id
                ORDER BY total_adocoes DESC, a.id ASC';

        return $this->getConn()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
