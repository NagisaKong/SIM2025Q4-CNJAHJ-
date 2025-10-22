<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Utils;

final class Validation
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleString) as $rule) {
                $rule = trim($rule);
                if ($rule === '') {
                    continue;
                }
                if ($rule === 'required' && ($value === null || (is_string($value) && trim($value) === '') || $value === [])) {
                    $this->errors[$field][] = 'This field is required.';
                }
                if ($rule === 'email' && $value !== null && !filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = 'Please enter a valid email address.';
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (is_string($value) && mb_strlen(trim($value)) < $min) {
                        $this->errors[$field][] = 'Minimum length is ' . $min . ' characters.';
                    }
                }
                if ($rule === 'integer' && $value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->errors[$field][] = 'This field must be a number.';
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
