<?php

namespace App\Services;

use App\Repositories\MatchRepository;

class ReportingService
{
    public function __construct(private MatchRepository $matches)
    {
    }

    public function aggregate(string $period): array
    {
        return $this->matches->aggregateByPeriod($period);
    }
}
