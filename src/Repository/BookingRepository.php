<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Client;
use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function countBookingsInCurrentWeek(Client $client): int
    {
        $today = new \DateTime();
        // Calcular lunes y domingo de esta semana
        $monday = (clone $today)->modify('monday this week')->setTime(0, 0, 0);
        $sunday = (clone $today)->modify('sunday this week')->setTime(23, 59, 59);

        // Contar reservas del cliente en la semana actual
        return $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->join('b.activity', 'a') // Unimos con actividad para ver su fecha
            ->where('b.client = :client')
            ->andWhere('a.dateStart BETWEEN :start AND :end')
            ->setParameter('client', $client)
            ->setParameter('start', $monday)
            ->setParameter('end', $sunday)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function hasBooking(Client $client, Activity $activity): bool
    {
        // Verificar si el cliente ya tiene una reserva para esta actividad
        return (bool) $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->where('b.client = :client')
            ->andWhere('b.activity = :activity')
            ->setParameter('client', $client)
            ->setParameter('activity', $activity)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Booking[] Returns an array of Booking objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Booking
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
