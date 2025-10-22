<?php
declare(strict_types=1);

use shared\entity\Request;
use shared\utils\Validation;

class CreateRequestController
{
    private Request $requests;

    public function __construct()
    {
        $this->requests = new Request();
    }

    public function create(int $pinId, array $input): ?int
    {
        $title = Validation::sanitizeString($input['title'] ?? '');
        $description = Validation::sanitizeString($input['description'] ?? '');
        $location = Validation::sanitizeString($input['location'] ?? '');
        $categoryId = isset($input['category_id']) ? (int) $input['category_id'] : 0;
        $requestedDate = Validation::sanitizeString($input['requested_date'] ?? date('Y-m-d'));

        try {
            Validation::requireField($title, 'Title is required.');
            Validation::requireField($description, 'Description is required.');
            Validation::requireField($location, 'Location is required.');
            if ($categoryId <= 0) {
                throw new \InvalidArgumentException('Please choose a category.');
            }
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['pin_message'] = $exception->getMessage();
            return null;
        }

        $requestId = $this->requests->create([
            'pin_id' => $pinId,
            'category_id' => $categoryId,
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'status' => 'open',
            'requested_date' => $requestedDate,
        ]);
        $_SESSION['pin_message'] = 'Request created successfully.';
        return $requestId;
    }
}
