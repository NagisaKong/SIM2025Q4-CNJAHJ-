<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Utils\Validation;

final class updateAccountController
{
    private array $errors = [];

    public function __construct(
        private UserAccount $accounts,
        private Validation $validator
    ) {
    }

    public function update(int $id, array $payload): bool
    {
        $this->errors = [];
        $rules = [
            'email' => 'email',
            'name' => 'min:3',
        ];
        $filtered = [];
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = trim($value);
            }
            if ($payload[$key] === '' || $payload[$key] === null) {
                continue;
            }
            $filtered[$key] = $payload[$key];
        }

        $dataToValidate = array_intersect_key($filtered, $rules);
        if ($dataToValidate !== [] && !$this->validator->validate($dataToValidate, array_intersect_key($rules, $dataToValidate))) {
            $this->errors = $this->validator->errors();
            return false;
        }

        if (!$this->accounts->updateAccount($id, $filtered)) {
            $this->errors['account'][] = 'Unable to update account: ' . ($this->accounts->lastError() ?? 'unknown error');
            return false;
        }

        return true;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
