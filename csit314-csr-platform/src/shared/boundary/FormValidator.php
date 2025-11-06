<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Boundary;

final class FormValidator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $ruleParts = array_filter(array_map('trim', explode('|', (string) $ruleString)));

            foreach ($ruleParts as $rule) {
                if ($rule === 'required') {
                    if ($value === null || (is_string($value) && trim($value) === '') || $value === []) {
                        $this->errors[$field][] = 'This field is required.';
                    }
                    continue;
                }

                if ($rule === 'email') {
                    if ($value !== null && !filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field][] = 'Please enter a valid email address.';
                    }
                    continue;
                }

                if (str_starts_with($rule, 'min:')) {
                    $minLength = (int) substr($rule, 4);
                    if (is_string($value) && mb_strlen(trim($value)) < $minLength) {
                        $this->errors[$field][] = 'Minimum length is ' . $minLength . ' characters.';
                    }
                    continue;
                }

                if ($rule === 'integer') {
                    if ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
                        $this->errors[$field][] = 'This field must be a number.';
                    }
                    continue;
                }
            }
        }

        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
