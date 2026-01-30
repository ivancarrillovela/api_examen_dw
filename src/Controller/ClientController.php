<?php

namespace App\Controller;

use App\Entity\Client;
use App\Model\Request\ClientInfoFilterDto;
use App\Model\Response\ActivityDto;
use App\Model\Response\BookingDto;
use App\Model\Response\ClientDto;
use App\Model\Response\SongDto;
use App\Model\Response\StatisticsByTypeDto;
use App\Model\Response\StatisticsByYearDto;
use App\Model\Response\StatisticsDto;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    #[Route('/{id}', methods: ['GET'])]
    public function show(
        Client $client,
        #[MapQueryString] ?ClientInfoFilterDto $filters,
        ClientRepository $repo
    ): JsonResponse
    {
        $filters = $filters ?? new ClientInfoFilterDto();

        // 1. Lógica de Estadísticas (Agrupación por Año y Tipo)
        $statsResponse = [];
        if ($filters->with_statistics) {
            $rawStats = $repo->getStatistics($client->getId());
            
            $groupedByYear = [];
            foreach ($rawStats as $row) {
                $year = $row['year'];
                if (!isset($groupedByYear[$year])) {
                    $groupedByYear[$year] = [];
                }
                
                // Agrupamos bajo el año correspondiente
                $groupedByYear[$year][] = new StatisticsByTypeDto(
                    type: $row['type'],
                    statistics: [
                        new StatisticsDto(
                            num_activities: (int)$row['num_activities'],
                            num_minutes: (int)$row['num_minutes']
                        )
                    ]
                );
            }

            // Convertimos el mapa a una lista de DTOs
            foreach ($groupedByYear as $year => $types) {
                $statsResponse[] = new StatisticsByYearDto($year, $types);
            }
        }

        // 2. Lógica de Reservas (IMPLEMENTACIÓN COMPLETA)
        $bookingsResponse = []; 
        if ($filters->with_bookings) {
             foreach ($client->getBookings() as $booking) {
                $activity = $booking->getActivity();

                // a) Mapear Canciones
                $songsDtos = [];
                foreach ($activity->getPlaylist() as $song) {
                    $songsDtos[] = new SongDto(
                        id: $song->getId(),
                        name: $song->getName(),
                        duration_seconds: $song->getDurationSeconds()
                    );
                }

                // b) Mapear Actividad
                $activityDto = new ActivityDto(
                    id: $activity->getId(),
                    max_participants: $activity->getMaxParticipants(),
                    clients_signed: $activity->getBookings()->count(),
                    type: $activity->getType(),
                    play_list: $songsDtos,
                    date_start: $activity->getDateStart()->format('Y-m-d H:i:s'),
                    date_end: $activity->getDateEnd()->format('Y-m-d H:i:s')
                );

                // c) Crear el DTO de Reserva
                $bookingsResponse[] = new BookingDto(
                    id: $booking->getId(),
                    activity: $activityDto,
                    client_id: $client->getId()
                );
             }
        }

        // 3. Respuesta Final
        $response = new ClientDto(
            id: $client->getId(),
            type: $client->getType(),
            name: $client->getName(),
            email: $client->getEmail(),
            activities_booked: $bookingsResponse,
            activity_statistics: $statsResponse
        );

        return $this->json($response);
    }
}
