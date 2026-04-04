<?php
require_once 'core/AuthMiddleware.php';

class AuditLogController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function index() {
        AuthMiddleware::check($this->conn, 'xem_taikhoan');
        $quyen = $_SESSION['quyen'] ?? [];

        $levelFilter = strtoupper(trim((string)($_GET['level'] ?? '')));
        $q = trim((string)($_GET['q'] ?? ''));
        $rows = $this->readFilteredRows($levelFilter, $q);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $totalItems = count($rows);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $logs = array_slice($rows, $offset, $perPage);

        include 'views/auditlog/index.php';
    }

    public function exportCsv() {
        AuthMiddleware::check($this->conn, 'xem_taikhoan');

        $levelFilter = strtoupper(trim((string)($_GET['level'] ?? '')));
        $q = trim((string)($_GET['q'] ?? ''));
        $rows = $this->readFilteredRows($levelFilter, $q);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="audit-log-' . date('Ymd-His') . '.csv"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            echo 'Cannot export CSV';
            exit;
        }

        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['time', 'level', 'message', 'context']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['time'] ?? '',
                $row['level'] ?? '',
                $row['message'] ?? '',
                $row['context'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportJson() {
        AuthMiddleware::check($this->conn, 'xem_taikhoan');

        $levelFilter = strtoupper(trim((string)($_GET['level'] ?? '')));
        $q = trim((string)($_GET['q'] ?? ''));
        $rows = $this->readFilteredRows($levelFilter, $q);

        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="audit-log-' . date('Ymd-His') . '.json"');

        echo json_encode([
            'exportedAt' => date('c'),
            'filters' => [
                'level' => $levelFilter,
                'q' => $q,
            ],
            'count' => count($rows),
            'logs' => $rows,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function readFilteredRows($levelFilter = '', $q = '') {
        $logFile = __DIR__ . '/../logs/app.log';
        $rows = [];

        if (is_readable($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $lines = array_reverse($lines);

            foreach ($lines as $line) {
                if (!preg_match('/^\[(.*?)\] \[(.*?)\] (.*?) (\{.*\})$/', $line, $m)) {
                    continue;
                }

                $rows[] = [
                    'time' => $m[1],
                    'level' => strtoupper($m[2]),
                    'message' => $m[3],
                    'context' => $m[4],
                ];
            }
        }

        if ($levelFilter !== '') {
            $rows = array_values(array_filter($rows, function ($r) use ($levelFilter) {
                return $r['level'] === $levelFilter;
            }));
        }

        if ($q !== '') {
            $rows = array_values(array_filter($rows, function ($r) use ($q) {
                return (stripos($r['message'], $q) !== false) || (stripos($r['context'], $q) !== false);
            }));
        }

        return $rows;
    }
}
