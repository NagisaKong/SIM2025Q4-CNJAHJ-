<?php
declare(strict_types=1);

use shared\entity\Shortlist;
use shared\entity\Request;

class SaveRequestController
{
    private Shortlist $shortlist;
    private Request $requests;

    public function __construct()
    {
        $this->shortlist = new Shortlist();
        $this->requests = new Request();
    }

    public function addToShortlist(int $csrId, int $requestId): bool
    {
        $saved = $this->shortlist->add($csrId, $requestId);
        if ($saved) {
            $this->requests->updateShortlistCount($requestId, 1);
            $_SESSION['csr_message'] = 'Request added to shortlist.';
        } else {
            $_SESSION['csr_message'] = 'Request is already in shortlist.';
        }
        return $saved;
    }
}
