<?php

class WebResponder
{
    public static function redirectWithMessage(string $url, string $message, string $type = 'success'): void
    {
        $_SESSION[$type] = $message;
        header('Location: ' . $url);
        exit;
    }

    public static function backWithMessage(string $message, string $type = 'error', ?string $fallbackUrl = null): void
    {
        $_SESSION[$type] = $message;
        $back = $_SERVER['HTTP_REFERER'] ?? $fallbackUrl ?? 'index.php';
        header('Location: ' . $back);
        exit;
    }

    public static function ajaxText(string $body, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo $body;
        exit;
    }
}
