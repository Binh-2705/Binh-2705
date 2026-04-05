<?php

class ChatbotAuditModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getOrCreateSession(string $sessionKey, int $maTK, string $username, string $role): int {
        $stmt = $this->conn->prepare('SELECT id FROM chatbot_sessions WHERE session_key = ? LIMIT 1');
        $stmt->bind_param('s', $sessionKey);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $sessionId = (int)$row['id'];
            $touch = $this->conn->prepare('UPDATE chatbot_sessions SET last_interaction_at = CURRENT_TIMESTAMP, username = ?, role_name = ? WHERE id = ?');
            $touch->bind_param('ssi', $username, $role, $sessionId);
            $touch->execute();
            $touch->close();
            return $sessionId;
        }

        $insert = $this->conn->prepare('INSERT INTO chatbot_sessions (session_key, ma_tk, username, role_name) VALUES (?, ?, ?, ?)');
        $insert->bind_param('siss', $sessionKey, $maTK, $username, $role);
        $insert->execute();
        $sessionId = (int)$insert->insert_id;
        $insert->close();
        return $sessionId;
    }

    public function logMessage(int $sessionId, string $role, string $content, string $source = '', array $actions = [], array $suggestions = [], ?string $actionDraftToken = null): void {
        $actionsJson = !empty($actions) ? json_encode(array_values($actions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $suggestionsJson = !empty($suggestions) ? json_encode(array_values($suggestions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $stmt = $this->conn->prepare('INSERT INTO chatbot_messages (session_id, role_name, content, source_name, actions_json, suggestions_json, action_draft_token) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssss', $sessionId, $role, $content, $source, $actionsJson, $suggestionsJson, $actionDraftToken);
        $stmt->execute();
        $stmt->close();
    }

    public function createActionDraft(int $sessionId, int $createdBy, array $draft): string {
        $token = bin2hex(random_bytes(16));
        $title = (string)($draft['title'] ?? 'Hành động Chatbot');
        $summary = (string)($draft['summary'] ?? '');
        $actionType = (string)($draft['action_type'] ?? 'unknown');
        $permission = (string)($draft['required_permission'] ?? '');
        $payloadJson = json_encode($draft['payload'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $stmt = $this->conn->prepare('INSERT INTO chatbot_action_drafts (session_id, token, action_type, title, summary, permission_required, payload_json, status_name, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $status = 'pending';
        $stmt->bind_param('isssssssi', $sessionId, $token, $actionType, $title, $summary, $permission, $payloadJson, $status, $createdBy);
        $stmt->execute();
        $stmt->close();

        return $token;
    }

    public function getPendingActionDraft(string $token, int $currentMaTK = 0): ?array {
        $status = 'pending';
        if ($currentMaTK > 0) {
            $stmt = $this->conn->prepare('SELECT * FROM chatbot_action_drafts WHERE token = ? AND status_name = ? AND created_by = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE) LIMIT 1');
            $stmt->bind_param('ssi', $token, $status, $currentMaTK);
        } else {
            $stmt = $this->conn->prepare('SELECT * FROM chatbot_action_drafts WHERE token = ? AND status_name = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE) LIMIT 1');
            $stmt->bind_param('ss', $token, $status);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function markActionDraftCompleted(int $id, int $confirmedBy, string $status, string $resultMessage): void {
        $stmt = $this->conn->prepare('UPDATE chatbot_action_drafts SET status_name = ?, confirmed_by = ?, confirmed_at = CURRENT_TIMESTAMP, executed_at = CURRENT_TIMESTAMP, result_message = ? WHERE id = ?');
        $stmt->bind_param('sisi', $status, $confirmedBy, $resultMessage, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function getAuditRowsCount(string $q = '', string $source = '', string $status = ''): int {
        $sql = "SELECT COUNT(*) as cnt FROM chatbot_messages m
                INNER JOIN chatbot_sessions s ON m.session_id = s.id
                LEFT JOIN chatbot_action_drafts d ON m.action_draft_token = d.token
                WHERE 1=1";
        $params = [];
        $types = '';

        if ($q !== '') {
            $sql .= ' AND (m.content LIKE ? OR s.username LIKE ? OR IFNULL(d.title, "") LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= 'sss';
        }

        if ($source !== '') {
            $sql .= ' AND m.source_name = ?';
            $params[] = $source;
            $types .= 's';
        }

        if ($status !== '') {
            $sql .= ' AND IFNULL(d.status_name, "") = ?';
            $params[] = $status;
            $types .= 's';
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    public function getAuditRows(string $q = '', string $source = '', string $status = '', int $limit = 20, int $offset = 0): array {
        $sql = "SELECT m.created_at, s.username, s.role_name AS user_role, m.role_name AS message_role, m.content, m.source_name,
                       d.token AS action_token, d.action_type, d.status_name AS action_status, d.title AS action_title
                FROM chatbot_messages m
                INNER JOIN chatbot_sessions s ON m.session_id = s.id
                LEFT JOIN chatbot_action_drafts d ON m.action_draft_token = d.token
                WHERE 1=1";
        $params = [];
        $types = '';

        if ($q !== '') {
            $sql .= ' AND (m.content LIKE ? OR s.username LIKE ? OR IFNULL(d.title, "") LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= 'sss';
        }

        if ($source !== '') {
            $sql .= ' AND m.source_name = ?';
            $params[] = $source;
            $types .= 's';
        }

        if ($status !== '') {
            $sql .= ' AND IFNULL(d.status_name, "") = ?';
            $params[] = $status;
            $types .= 's';
        }

        $sql .= ' ORDER BY m.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
