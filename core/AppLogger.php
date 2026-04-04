<?php

class AppLogger
{
    private static function write(string $level, string $message, array $context = []): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $logFile = $logDir . '/app.log';
        $time = date('Y-m-d H:i:s');
        $contextJson = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '{}';

        $line = sprintf("[%s] [%s] %s %s\n", $time, strtoupper($level), $message, $contextJson);
        @file_put_contents($logFile, $line, FILE_APPEND);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function security(string $message, array $context = []): void
    {
        self::write('security', $message, $context);
    }
}
