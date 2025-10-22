<?php
declare(strict_types=1);

use shared\entity\Request;

class ViewRequestController
{
    private Request $requests;

    public function __construct()
    {
        $this->requests = new Request();
    }

    public function find(int $requestId): ?array
    {
        $this->requests->incrementViewCount($requestId);
        return $this->requests->find($requestId);
    }
}
