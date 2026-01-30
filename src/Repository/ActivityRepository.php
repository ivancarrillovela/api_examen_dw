<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\Request\ActivityFilterDto;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    public function findByFilters(ActivityFilterDto $filters): Paginator
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.bookings', 'b') 
            ->addSelect('COUNT(b.id) as HIDDEN booking_count') 
            ->groupBy('a.id');

        // 1. Filtro por Tipo
        if ($filters->type) {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $filters->type);
        }

        // 2. Filtro "onlyfree"
        if ($filters->onlyfree) {
            // "HAVING" porque filtramos sobre un resultado agregado (COUNT)
            $qb->having('COUNT(b.id) < a.maxParticipants');
        }

        // 3. Ordenación
        $sortField = match($filters->sort) {
            'date' => 'a.dateStart',
            default => 'a.dateStart'
        };
        $qb->orderBy($sortField, $filters->order);

        // 4. Paginación
        $qb->setFirstResult(($filters->page - 1) * $filters->page_size)
           ->setMaxResults($filters->page_size);

        return new Paginator($qb, fetchJoinCollection: true);
    }

    //    /**
    //     * @return Activity[] Returns an array of Activity objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Activity
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
