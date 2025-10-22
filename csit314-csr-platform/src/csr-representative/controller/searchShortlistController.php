<?php
declare(strict_types=1);

use shared\entity\Shortlist;
use shared\utils\Validation;

class SearchShortlistController
{
    private Shortlist $shortlist;

    public function __construct()
    {
        $this->shortlist = new Shortlist();
    }

    public function search(int $csrId, array $query): array
    {
        $term = Validation::sanitizeString($query['q'] ?? '');
        if ($term === '') {
            return $this->shortlist->listForCsr($csrId);
        }
        return $this->shortlist->searchForCsr($csrId, $term);
    }
}
