<?php

namespace App\Model\Request;

class ClientInfoFilterDto
{
    public function __construct(
        public bool $with_statistics = false,
        public bool $with_bookings = false
    ) {}
}