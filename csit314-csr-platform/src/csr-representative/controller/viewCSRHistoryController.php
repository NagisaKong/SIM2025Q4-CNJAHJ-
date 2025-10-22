<?php
declare(strict_types=1);

use shared\entity\Request;

class ViewCSRHistoryController
{
    private Request $requests;

    public function __construct()
    {
        $this->requests = new Request();
    }

    public function list(int $csrId): array
    {
        return $this->requests->historyForCsr($csrId);
    }
}
