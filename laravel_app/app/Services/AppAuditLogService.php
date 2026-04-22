<?php

namespace App\Services;

class AppAuditLogService
{
    public function readFilteredRows(string $levelFilter = '', string $q = ''): array
    {
        $logFile = dirname(base_path()) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log';
        $rows = [];

        if (is_readable($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $lines = array_reverse($lines);

            foreach ($lines as $line) {
                if (!preg_match('/^\[(.*?)\] \[(.*?)\] (.*?) (\{.*\})$/', $line, $matches)) {
                    continue;
                }

                $rows[] = [
                    'time' => $matches[1],
                    'level' => strtoupper($matches[2]),
                    'message' => $matches[3],
                    'context' => $matches[4],
                ];
            }
        }

        if ($levelFilter !== '') {
            $rows = array_values(array_filter($rows, static fn (array $row) => $row['level'] === $levelFilter));
        }

        if ($q !== '') {
            $rows = array_values(array_filter($rows, static function (array $row) use ($q) {
                return stripos($row['message'], $q) !== false || stripos($row['context'], $q) !== false;
            }));
        }

        return $rows;
    }
}