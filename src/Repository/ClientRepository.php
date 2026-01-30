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

    public function getStatistics(int $clientId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // En SQL Nativo por que suele ser más limpio para agrupaciones por año y tipo
        $sql = '
            SELECT 
                YEAR(a.date_start) as year,
                a.type,
                COUNT(a.id) as num_activities,
                SUM(TIME_TO_SEC(TIMEDIFF(a.date_end, a.date_start)) / 60) as num_minutes
            FROM booking b
            JOIN activity a ON b.activity_id = a.id
            WHERE b.client_id = :id
            GROUP BY year, a.type
            ORDER BY year DESC, a.type ASC
        ';

        $resultSet = $conn->executeQuery($sql, ['id' => $clientId]);
        return $resultSet->fetchAllAssociative();
    }

    //    /**
    //     * @return Client[] Returns an array of Client objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Client
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
