<?php
declare(strict_types=1);

use shared\entity\Request;

class ViewPINHistoryController
{
    private Request $requests;

    public function __construct()
    {
        $this->requests = new Request();
    }

    public function list(int $pinId): array
    {
        return $this->requests->historyForPin($pinId);
    }
}
