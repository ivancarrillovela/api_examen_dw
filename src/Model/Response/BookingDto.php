<?php

namespace App\Model\Response;

class BookingDto
{
    public function __construct(
        public int $id,
        public ActivityDto $activity,
        public int $client_id
    ) {}
}