<?php

namespace App\Controller;

use App\Model\Request\BookingNewDto;
use App\Model\Response\ActivityDto;
use App\Model\Response\BookingDto;
use App\Model\Response\SongDto;
use App\Service\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bookings')]
class BookingController extends AbstractController
{
    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] BookingNewDto $bookingDto,
        BookingService $service
    ): JsonResponse
    {
        // Delegamos toda la lógica al servicio
        $booking = $service->createBooking($bookingDto);

        // Mapeamos a DTO de Salida
        // Reutilizamos el mapeo de Activity
        $activity = $booking->getActivity();
        
        // Mapeo rápido de canciones
        $songsDtos = [];
        foreach ($activity->getPlaylist() as $song) {
            $songsDtos[] = new SongDto($song->getId(), $song->getName(), $song->getDurationSeconds());
        }

        // Crear el ActivityDto
        $activityDto = new ActivityDto(
            id: $activity->getId(),
            max_participants: $activity->getMaxParticipants(),
            clients_signed: $activity->getBookings()->count(),
            type: $activity->getType(),
            play_list: $songsDtos,
            date_start: $activity->getDateStart()->format('Y-m-d H:i:s'),
            date_end: $activity->getDateEnd()->format('Y-m-d H:i:s')
        );

        // Crear el BookingDto
        $response = new BookingDto(
            id: $booking->getId(),
            activity: $activityDto,
            client_id: $booking->getClient()->getId()
        );

        // Devolver la respuesta
        return $this->json($response);
    }
}