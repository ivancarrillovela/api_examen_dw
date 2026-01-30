<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Obtiene estadísticas agrupadas por año y tipo de actividad.
     * Calcula el número total de actividades y la suma de minutos.
     */
    public function getStatistics(int $clientId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Usamos SQL Nativo para facilitar las funciones de fecha y agregación
        // TIMEDIFF devuelve la diferencia, TIME_TO_SEC la pasa a segundos y dividimos entre 60 para minutos.
        $sql = '
            SELECT 
                YEAR(a.date_start) as year,
                a.type,
                COUNT(a.id) as num_activities,
                COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(a.date_end, a.date_start)) / 60), 0) as num_minutes
            FROM booking b
            JOIN activity a ON b.activity_id = a.id
            WHERE b.client_id = :id
            GROUP BY year, a.type
            ORDER BY year DESC, a.type ASC
        ';

        $resultSet = $conn->executeQuery($sql, ['id' => $clientId]);
        return $resultSet->fetchAllAssociative();
    }
}