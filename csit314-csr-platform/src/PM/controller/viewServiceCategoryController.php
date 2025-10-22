<?php
declare(strict_types=1);

use shared\entity\serviceCategories;

class ViewServiceCategoryController
{
    private serviceCategories $categories;

    public function __construct()
    {
        $this->categories = new serviceCategories();
    }

    public function list(): array
    {
        return $this->categories->listAll();
    }
}
