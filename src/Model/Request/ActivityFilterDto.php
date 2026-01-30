<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ActivityFilterDto
{
    public function __construct(
        #[Assert\Choice(
            choices: ['BodyPump', 'Spinning', 'Core'],
            message: 'El tipo de actividad debe ser BodyPump, Spinning o Core.'
        )]
        public ?string $type = null,

        public bool $onlyfree = true,

        #[Assert\Positive(message: 'La página debe ser mayor a 0.')]
        public int $page = 1,

        #[Assert\Positive(message: 'El tamaño de página debe ser positivo.')]
        public int $page_size = 10,

        #[Assert\Choice(choices: ['date'], message: 'El criterio de ordenación solo soporta "date".')]
        public string $sort = 'date',

        #[Assert\Choice(choices: ['asc', 'desc'], message: 'El orden debe ser "asc" o "desc".')]
        public string $order = 'desc'
    ) {}
}