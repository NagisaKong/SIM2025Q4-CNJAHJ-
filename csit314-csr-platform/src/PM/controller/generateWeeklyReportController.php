<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Entity\Report;

use DateInterval;
use DateTimeImmutable;

final class generateWeeklyReportController
{
    public function __construct(private Report $report)
    {
    }

    public function generateWeeklyReport(): array
    {
        $end = new DateTimeImmutable('today');
        $start = $end->sub(new DateInterval('P6D'));

        return $this->report->produceReport($start->format('Y-m-d'), $end->format('Y-m-d'));
    }

    /** @deprecated */
    public function generate(): array
    {
        return $this->generateWeeklyReport();
    }
}
