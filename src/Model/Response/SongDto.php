<?php

namespace App\Model\Response;

class SongDto
{
    public function __construct(
        public int $id,
        public string $name,
        public int $duration_seconds
    ) {}
}