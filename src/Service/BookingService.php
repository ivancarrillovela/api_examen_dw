<?php

namespace App\Service;

use App\Entity\Booking;
use App\Model\Request\BookingNewDto;
use App\Repository\ActivityRepository;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepo,
        private ActivityRepository $activityRepo,
        private ClientRepository $clientRepo,
        private EntityManagerInterface $em
    ) {}

    public function createBooking(BookingNewDto $dto): Booking
    {
        // 1. Validar existencia (Devuelve 404 si falla)
        $client = $this->clientRepo->find($dto->client_id);
        $activity = $this->activityRepo->find($dto->activity_id);

        if (!$client) {
            throw new NotFoundHttpException(sprintf('El cliente con ID %d no existe.', $dto->client_id));
        }
        if (!$activity) {
            throw new NotFoundHttpException(sprintf('La actividad con ID %d no existe.', $dto->activity_id));
        }

        // 2. Validar duplicados (Devuelve 400 si falla)
        if ($this->bookingRepo->hasBooking($client, $activity)) {
            throw new BadRequestHttpException('El cliente ya tiene una reserva para esta actividad.');
        }

        // 3. Validar Plazas (Devuelve 400 si falla)
        if ($activity->getBookings()->count() >= $activity->getMaxParticipants()) {
            throw new BadRequestHttpException('La actividad está completa, no quedan plazas.');
        }

        // 4. Regla Standard vs Premium
        if ($client->getType() === 'standard') {
            $reservasSemana = $this->bookingRepo->countBookingsInCurrentWeek($client);
            if ($reservasSemana >= 2) {
                throw new BadRequestHttpException('Los clientes Standard solo pueden realizar 2 reservas por semana.');
            }
        }

        // 5. Persistir
        $booking = new Booking();
        $booking->setClient($client);
        $booking->setActivity($activity);

        $this->em->persist($booking);
        $this->em->flush();

        return $booking;
    }
}