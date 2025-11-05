<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Entity\Report;

use DateTimeImmutable;

final class generateDailyReportController
{
    public function __construct(private Report $report)
    {
    }

    public function generateDailyReport(): array
    {
        $today = new DateTimeImmutable('today');
        return $this->report->produceReport($today->format('Y-m-d'), $today->format('Y-m-d'));
    }

    /** @deprecated */
    public function generate(): array
    {
        return $this->generateDailyReport();
    }
}
