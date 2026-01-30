<?php

namespace App\Model\Response;

class StatisticsByTypeDto
{
    /**
     * @param StatisticsDto[] $statistics
     */
    public function __construct(
        public string $type,
        public array $statistics
    ) {}
}