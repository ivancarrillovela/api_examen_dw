<?php

namespace App\Model\Response;

class ClientDto
{
    /**
     * @param BookingDto[] $activities_booked
     * @param StatisticsByYearDto[] $activity_statistics
     */
    public function __construct(
        public int $id,
        public string $type,
        public string $name,
        public string $email,
        public array $activities_booked,
        public array $activity_statistics
    ) {}
}