<?php

namespace App\Core;

class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleString) {
            $rulesList = explode('|', $ruleString);
            foreach ($rulesList as $rule) {
                $rule = trim($rule);
                $value = $data[$field] ?? null;

                if ($rule === 'required' && ($value === null || $value === '')) {
                    $this->errors[$field] = 'This field is required.';
                }

                if ($rule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = 'Invalid email format.';
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (is_string($value) && strlen($value) < $min) {
                        $this->errors[$field] = "Must be at least {$min} characters.";
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
