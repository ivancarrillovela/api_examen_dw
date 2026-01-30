<?php

namespace App\Service;

use App\Entity\Booking;
use App\Exception\BookingException;
use App\Model\Request\BookingNewDto;
use App\Model\Response\BookingDto;
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
        // 1. Validar existencia de Cliente y Actividad
        $client = $this->clientRepo->find($dto->client_id);
        $activity = $this->activityRepo->find($dto->activity_id);

        if (!$client) throw new NotFoundHttpException("Cliente no encontrado");
        if (!$activity) throw new NotFoundHttpException("Actividad no encontrada");

        // 2. Validar duplicados (Lógica básica: no apuntarse dos veces)
        if ($this->bookingRepo->hasBooking($client, $activity)) {
            throw new BadRequestHttpException("El cliente ya está apuntado a esta actividad");
        }

        // 3. Validar Plazas Suficientes
        // Nota: Activity tiene colección bookings, pero para ser eficientes confiamos en count()
        if ($activity->getBookings()->count() >= $activity->getMaxParticipants()) {
            throw new BadRequestHttpException("No quedan plazas libres en esta actividad");
        }

        // 4. Regla de Negocio Premium vs Standard
        if ($client->getType() === 'standard') {
            $reservasSemana = $this->bookingRepo->countBookingsInCurrentWeek($client);
            if ($reservasSemana >= 2) {
                throw new BadRequestHttpException("Los usuarios Standard solo pueden reservar 2 actividades por semana.");
            }
        }

        // 5. Crear y Guardar Reserva
        $booking = new Booking();
        $booking->setClient($client);
        $booking->setActivity($activity);

        $this->em->persist($booking);
        $this->em->flush();

        return $booking;
    }
}