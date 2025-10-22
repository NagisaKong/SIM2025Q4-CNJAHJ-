<?php
declare(strict_types=1);

use shared\entity\Shortlist;

class ViewRequestShortlistCountController
{
    private Shortlist $shortlist;

    public function __construct()
    {
        $this->shortlist = new Shortlist();
    }

    public function count(int $requestId): int
    {
        return $this->shortlist->shortlistCount($requestId);
    }
}
