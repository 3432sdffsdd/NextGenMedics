<?php
namespace App\Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                match ($rule) {
                    'required' => $this->checkRequired($field, $value),
                    'email'    => $this->checkEmail($field, $value),
                    'min'      => $this->checkMin($field, $value, (int) ($params[0] ?? 0)),
                    'max'      => $this->checkMax($field, $value, (int) ($params[0] ?? 0)),
                    'numeric'  => $this->checkNumeric($field, $value),
                    'integer'  => $this->checkInteger($field, $value),
                    'in'       => $this->checkIn($field, $value, $params),
                    'date'     => $this->checkDate($field, $value),
                    default    => null,
                };
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function checkRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "{$field} is required");
        }
    }

    private function checkEmail(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "{$field} must be a valid email");
        }
    }

    private function checkMin(string $field, mixed $value, int $min): void
    {
        if ($value !== null && $value !== '' && strlen((string) $value) < $min) {
            $this->addError($field, "{$field} must be at least {$min} characters");
        }
    }

    private function checkMax(string $field, mixed $value, int $max): void
    {
        if ($value !== null && $value !== '' && strlen((string) $value) > $max) {
            $this->addError($field, "{$field} must not exceed {$max} characters");
        }
    }

    private function checkNumeric(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "{$field} must be numeric");
        }
    }

    private function checkInteger(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        if (is_int($value)) {
            return;
        }
        if (is_numeric($value) && (string) (int) $value === (string) $value) {
            return;
        }
        if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return;
        }
        $this->addError($field, "{$field} must be an integer");
    }

    private function checkIn(string $field, mixed $value, array $allowed): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->addError($field, "{$field} must be one of: " . implode(', ', $allowed));
        }
    }

    private function checkDate(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $normalized = str_replace('T', ' ', (string) $value);
        if (strtotime($normalized) === false) {
            $this->addError($field, "{$field} must be a valid date");
        }
    }
}
