<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Entity\ServiceCategories;
use CSRPlatform\Shared\Boundary\FormValidator;

final class updateServiceCategoryController
{
    private array $errors = [];

    public function __construct(
        private ServiceCategories $categories,
        private FormValidator $validator
    ) {
    }

    public function update(int $categoryId, array $payload): bool
    {
        $this->errors = [];
        $rules = [];
        if (isset($payload['name'])) {
            $rules['name'] = 'min:3';
        }
        if (isset($payload['status'])) {
            $rules['status'] = 'required';
        }
        if ($rules !== [] && !$this->validator->validate($payload, $rules)) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return $this->categories->updateCategory($categoryId, $payload);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
