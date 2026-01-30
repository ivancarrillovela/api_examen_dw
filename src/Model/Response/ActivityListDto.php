<?php

namespace App\Model\Response;

class ActivityListDto
{
    /**
     * @param ActivityDto[] $data
     */
    public function __construct(
        public array $data,
        public MetadataDto $meta
    ) {}
}