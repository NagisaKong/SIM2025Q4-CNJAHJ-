<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Boundary\FormValidator;
use CSRPlatform\Shared\Entity\ServiceCategories;

final class updateServiceCategoryController
{
    private array $errors = [];

    public function __construct(
        private ServiceCategories $categories,
        private FormValidator $validator
    ) {
    }

    public function updateServiceCategory(string $serviceID, string $name, string $description, string $status): bool
    {
        $this->errors = [];
        $payload = [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ];

        if (!$this->validator->validate($payload, [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'status' => 'required',
        ])) {
            $this->errors = $this->validator->errors();
            $this->recordError('Please review the highlighted fields.');
            return false;
        }

        $result = $this->categories->updateServiceCategory($serviceID, $name, $description, $status);
        if (!$result) {
            $this->recordError('Unable to update service category.');
        }

        return $result;
    }

    public function suspendServiceCategory(string $serviceID, string $status): bool
    {
        $this->errors = [];
        $status = $status !== '' ? $status : 'inactive';
        $result = $this->categories->hideServiceCategory($serviceID, $status);

        if (!$result) {
            $this->recordError('Unable to change category status.');
        }

        return $result;
    }

    public function createServiceCategory(string $name, string $description, string $status = 'active'): bool
    {
        $this->errors = [];
        $payload = [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ];

        if (!$this->validator->validate($payload, [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'status' => 'required',
        ])) {
            $this->errors = $this->validator->errors();
            $this->recordError('Please review the highlighted fields.');
            return false;
        }

        $result = $this->categories->createCategory($name, $description, $status);
        if (!$result) {
            $this->recordError('Unable to create service category.');
        }

        return $result;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /** @deprecated */
    public function update(int $categoryId, array $payload): bool
    {
        $name = (string) ($payload['name'] ?? '');
        $description = (string) ($payload['description'] ?? '');
        $status = (string) ($payload['status'] ?? '');

        if ($name === '' && $status !== '') {
            return $this->suspendServiceCategory((string) $categoryId, $status);
        }

        return $this->updateServiceCategory((string) $categoryId, $name, $description, $status);
    }

    private function recordError(string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['message_error'] = $message;
        $_SESSION['flash_error'] = $message;
    }
}
