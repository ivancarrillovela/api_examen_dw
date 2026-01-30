<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Model\Request\ActivityFilterDto;
use App\Model\Response\ActivityDto;
use App\Model\Response\ActivityListDto;
use App\Model\Response\MetadataDto;
use App\Model\Response\SongDto;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activities')]
class ActivityController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(
        #[MapQueryString] ?ActivityFilterDto $filters, // Valida automáticamente los query params
        ActivityRepository $repo
    ): JsonResponse
    {
        // Si no vienen filtros en la URL, inicializamos unos por defecto
        $filters = $filters ?? new ActivityFilterDto();

        // 1. Llamar al Repositorio para buscar con filtros y paginación
        $paginator = $repo->findByFilters($filters);
        
        // 2. Mapeo: Convertir Entidades de Doctrine a DTOs de Salida
        $activityDtos = [];
        
        foreach ($paginator as $activity) {
            /** @var Activity $activity */
            
            // 2.1 Convertir las canciones de la actividad a SongDto
            $songsDtos = [];
            foreach ($activity->getPlaylist() as $song) {
                $songsDtos[] = new SongDto(
                    id: $song->getId(),
                    name: $song->getName(),
                    duration_seconds: $song->getDurationSeconds()
                );
            }

            // 2.2 Crear el ActivityDto (IMPORTANTE: El orden/nombres deben coincidir con tu clase ActivityDto)
            $activityDtos[] = new ActivityDto(
                id: $activity->getId(),
                max_participants: $activity->getMaxParticipants(),
                clients_signed: $activity->getBookings()->count(), // Doctrine cuenta los bookings
                type: $activity->getType(),
                play_list: $songsDtos,
                date_start: $activity->getDateStart()->format('Y-m-d H:i:s'),
                date_end: $activity->getDateEnd()->format('Y-m-d H:i:s') // ¡Este campo faltaba antes!
            );
        }

        // 3. Crear los Metadatos de paginación
        $totalItems = count($paginator);
        $metaDto = new MetadataDto(
            page: $filters->page,
            limit: $filters->page_size,
            total_items: $totalItems
        );

        // 4. Construir la respuesta final
        $response = new ActivityListDto(
            data: $activityDtos,
            meta: $metaDto
        );

        return $this->json($response);
    }
}