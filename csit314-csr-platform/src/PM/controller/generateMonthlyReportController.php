<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Entity\Report;

use DateTimeImmutable;

final class generateMonthlyReportController
{
    public function __construct(private Report $report)
    {
    }

    public function generateMonthlyReport(): array
    {
        $end = new DateTimeImmutable('today');
        $start = $end->modify('first day of this month');

        return $this->report->produceReport($start->format('Y-m-d'), $end->format('Y-m-d'));
    }

    /** @deprecated */
    public function generate(): array
    {
        return $this->generateMonthlyReport();
    }
}

