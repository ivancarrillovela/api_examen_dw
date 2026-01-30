<?php

namespace App\Model\Response;

class MetadataDto
{
    public function __construct(
        public int $page,
        public int $limit,
        public int $total_items
    ) {}
}