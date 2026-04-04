<?php

class RequestValidator
{
    private array $source;
    private array $errors = [];

    public function __construct(array $source)
    {
        $this->source = $source;
    }

    public function requiredString(string $field, string $label, int $min = 1, int $max = 255): string
    {
        $value = trim((string)($this->source[$field] ?? ''));
        $len = mb_strlen($value);

        if ($value === '') {
            $this->errors[] = $label . ' không được để trống.';
            return '';
        }

        if ($len < $min || $len > $max) {
            $this->errors[] = sprintf('%s phải từ %d đến %d ký tự.', $label, $min, $max);
        }

        return $value;
    }

    public function optionalString(string $field, int $max = 255): string
    {
        $value = trim((string)($this->source[$field] ?? ''));
        if ($value !== '' && mb_strlen($value) > $max) {
            $this->errors[] = sprintf('%s vượt quá %d ký tự.', $field, $max);
        }

        return $value;
    }

    public function requiredInt(string $field, string $label, int $min = 0): int
    {
        $raw = trim((string)($this->source[$field] ?? ''));
        if ($raw === '' || filter_var($raw, FILTER_VALIDATE_INT) === false) {
            $this->errors[] = $label . ' không hợp lệ.';
            return 0;
        }

        $value = (int)$raw;
        if ($value < $min) {
            $this->errors[] = sprintf('%s phải lớn hơn hoặc bằng %d.', $label, $min);
        }

        return $value;
    }

    public function optionalInt(string $field, int $min = 0): ?int
    {
        $raw = trim((string)($this->source[$field] ?? ''));
        if ($raw === '') {
            return null;
        }

        if (filter_var($raw, FILTER_VALIDATE_INT) === false) {
            $this->errors[] = $field . ' không hợp lệ.';
            return null;
        }

        $value = (int)$raw;
        if ($value < $min) {
            $this->errors[] = sprintf('%s phải lớn hơn hoặc bằng %d.', $field, $min);
        }

        return $value;
    }

    public function requiredDate(string $field, string $label): string
    {
        $value = trim((string)($this->source[$field] ?? ''));
        if ($value === '') {
            $this->errors[] = $label . ' không được để trống.';
            return '';
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt || $dt->format('Y-m-d') !== $value) {
            $this->errors[] = $label . ' không đúng định dạng ngày.';
            return '';
        }

        return $value;
    }

    public function optionalDate(string $field): ?string
    {
        $value = trim((string)($this->source[$field] ?? ''));
        if ($value === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt || $dt->format('Y-m-d') !== $value) {
            $this->errors[] = $field . ' không đúng định dạng ngày.';
            return null;
        }

        return $value;
    }

    public function optionalEmail(string $field, string $label, int $max = 150): string
    {
        $value = trim((string)($this->source[$field] ?? ''));
        if ($value === '') {
            return '';
        }

        if (mb_strlen($value) > $max || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = $label . ' không hợp lệ.';
        }

        return $value;
    }

    public function optionalPattern(string $field, string $label, string $pattern): string
    {
        $value = trim((string)($this->source[$field] ?? ''));
        if ($value === '') {
            return '';
        }

        if (!preg_match($pattern, $value)) {
            $this->errors[] = $label . ' không hợp lệ.';
        }

        return $value;
    }

    public function in(string $field, string $label, array $allowed): string
    {
        $value = trim((string)($this->source[$field] ?? ''));
        if (!in_array($value, $allowed, true)) {
            $this->errors[] = $label . ' không hợp lệ.';
        }

        return $value;
    }

    public function optionalFloat(string $field, float $min = 0): ?float
    {
        $raw = trim((string)($this->source[$field] ?? ''));
        if ($raw === '') {
            return null;
        }

        if (!is_numeric($raw)) {
            $this->errors[] = $field . ' không hợp lệ.';
            return null;
        }

        $value = (float)$raw;
        if ($value < $min) {
            $this->errors[] = sprintf('%s phải lớn hơn hoặc bằng %s.', $field, (string)$min);
        }

        return $value;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function firstError(): string
    {
        return $this->errors[0] ?? 'Dữ liệu không hợp lệ.';
    }

    public function allErrors(): array
    {
        return $this->errors;
    }
}
