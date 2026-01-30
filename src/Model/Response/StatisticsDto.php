<?php

namespace App\Model\Response;

class StatisticsDto
{
    public function __construct(
        public int $num_activities,
        public int $num_minutes
    ) {}
}