<?php
declare(strict_types=1);

use shared\entity\Shortlist;

class ViewShortlistedRequestController
{
    private Shortlist $shortlist;

    public function __construct()
    {
        $this->shortlist = new Shortlist();
    }

    public function list(int $csrId): array
    {
        return $this->shortlist->listForCsr($csrId);
    }
}
