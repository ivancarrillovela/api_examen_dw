<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Booking;
use App\Entity\Client;
use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Crear Clientes (Usuarios)
        $clients = [];
        $clientTypes = ['standard', 'premium'];
        
        for ($i = 1; $i <= 10; $i++) {
            $client = new Client();
            $client->setName("Cliente $i");
            $client->setEmail("cliente$i@4vgym.com");
            $client->setType($clientTypes[$i % 2]); 
            
            $manager->persist($client);
            $clients[] = $client; 
        }

        // 2. Crear Actividades
        $activityTypes = ['BodyPump', 'Spinning', 'Core'];
        
        for ($j = 1; $j <= 20; $j++) {
            $activity = new Activity();
            
            $type = $activityTypes[array_rand($activityTypes)];
            $activity->setType($type);
            
            // Usamos DateTime normal para asegurar compatibilidad si no usaste DateTimeImmutable
            $dateStart = new \DateTime(sprintf('now %s %d days', rand(0, 1) ? '+' : '-', rand(1, 10)));
            $dateStart->setTime(rand(8, 20), 0, 0);
            
            $activity->setDateStart($dateStart);
            // Clonamos para no modificar la fecha de inicio
            $dateEnd = (clone $dateStart)->modify('+45 minutes');
            $activity->setDateEnd($dateEnd);
            
            $activity->setMaxParticipants(rand(2, 5));
            
            // Persistimos la actividad ANTES de las canciones para que tenga ID (por seguridad)
            $manager->persist($activity);

            // 3. Añadir Canciones (Playlist)
            $numSongs = rand(3, 5);
            for ($k = 1; $k <= $numSongs; $k++) {
                $song = new Song();
                $song->setName("Canción $k del Mix $j");
                $song->setDurationSeconds(rand(180, 300));
                
                // Vinculamos ambos lados de la relación
                $song->setActivity($activity); 
                $activity->addPlaylist($song);

                // IMPORTANTE: Persistir explícitamente para evitar error de "Cascade Persist"
                $manager->persist($song);
            }

            // 4. Crear Reservas (Bookings)
            // Lógica robusta para no pasarnos de índices
            $maxBookings = min(count($clients), $activity->getMaxParticipants());
            $numBookings = rand(0, $maxBookings);
            
            // Barajamos una copia del array de clientes para esta actividad
            $shuffledClients = $clients;
            shuffle($shuffledClients);
            
            for ($l = 0; $l < $numBookings; $l++) {
                $booking = new Booking();
                $booking->setClient($shuffledClients[$l]); // Usamos el array barajado
                $booking->setActivity($activity);
                
                $manager->persist($booking);
            }
        }

        $manager->flush();
    }
}