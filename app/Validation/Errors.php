<?php
namespace App\Validation;

class Errors
{
    public static function getAll(): array
    {
        return $_SESSION['errors'] ?? [];
    }

    public static function get(string $key): ?string
    {
        return $_SESSION['errors'][$key] ?? null;
    }
}