<?php
declare(strict_types=1);

use shared\entity\serviceCategories;
use shared\utils\Validation;

class UpdateServiceCategoryController
{
    private serviceCategories $categories;

    public function __construct()
    {
        $this->categories = new serviceCategories();
    }

    public function update(int $id, array $input): bool
    {
        $name = Validation::sanitizeString($input['name'] ?? '');
        $status = Validation::sanitizeString($input['status'] ?? 'active');

        try {
            Validation::requireField($name, 'Category name is required.');
            Validation::requireField($status, 'Status is required.');
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['category_message'] = $exception->getMessage();
            return false;
        }

        $updated = $this->categories->update($id, $name, $status);
        $_SESSION['category_message'] = $updated ? 'Category updated successfully.' : 'Unable to update category.';
        return $updated;
    }

    public function find(int $id): ?array
    {
        return $this->categories->find($id);
    }
}
