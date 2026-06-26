<?php
require_once 'init.php';
require_once '../DB_CON.php';

header('Content-Type: application/json');
mysqli_set_charset($con, 'utf8mb4');

$data = json_decode(file_get_contents('php://input'), true);
$sessionId = $data['session_id'] ?? '';
$action = $data['action'] ?? 'mark_read';

if (!$sessionId) {
    echo json_encode(['error' => 'No session ID']);
    exit;
}

if ($action === 'mark_unread') {
    // Mark all visitor messages in this session as UNREAD
    $stmt = $con->prepare("UPDATE chat_messages SET is_read = 0 WHERE session_id = ? AND sender_type = 'visitor'");
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    $affected = $stmt->affected_rows;

    if ($affected > 0) {
        // Count total unread messages for this session
        $countStmt = $con->prepare("SELECT COUNT(*) as cnt FROM chat_messages WHERE session_id = ? AND sender_type = 'visitor' AND is_read = 0");
        $countStmt->bind_param("s", $sessionId);
        $countStmt->execute();
        $result = $countStmt->get_result();
        $count = $result->fetch_assoc()['cnt'] ?? 0;

        echo json_encode(['success' => true, 'count' => (int)$count]);
    } else {
        echo json_encode(['success' => false, 'error' => 'لا توجد رسائل لتعليمها']);
    }
} else {
    // Default: Mark all visitor messages in this session as READ
    $stmt = $con->prepare("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender_type = 'visitor'");
    $stmt->bind_param("s", $sessionId);
    $stmt->execute();
    echo json_encode(['success' => true]);
}
