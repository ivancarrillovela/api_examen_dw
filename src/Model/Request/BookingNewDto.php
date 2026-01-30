<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class BookingNewDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'El ID de la actividad es obligatorio.')]
        #[Assert\Positive(message: 'El ID de la actividad debe ser un número positivo.')]
        public int $activity_id,

        #[Assert\NotBlank(message: 'El ID del cliente es obligatorio.')]
        #[Assert\Positive(message: 'El ID del cliente debe ser un número positivo.')]
        public int $client_id
    ) {}
}