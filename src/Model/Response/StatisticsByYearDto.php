<?php

namespace App\Model\Response;

class StatisticsByYearDto
{
    /**
     * @param StatisticsByTypeDto[] $statistics_by_type
     */
    public function __construct(
        public int $year,
        public array $statistics_by_type
    ) {}
}