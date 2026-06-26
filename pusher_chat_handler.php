<?php
session_start();
// Enable full error reporting to capture any silent failures
error_reporting(E_ALL);
ini_set('display_errors', 0); // Log errors instead, better for AJAX
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/chat_error.log');

require_once __DIR__ . '/DB_CON.php';
require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !preg_match('/^[a-zA-Z0-9_-]+$/', $data['session_id'])) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$sessionId = $data['session_id'];
$message = $data['message'] ?? '';
$type = $data['type'] ?? 'message';
$senderType = $data['sender_type'] ?? 'visitor';
$visitorName = $data['visitor_name'] ?? '????';

// Use same config from travel.php/config.php
$options = array('cluster' => 'ap2', 'useTLS' => true);
$pusher = new Pusher\Pusher(
    '4a9de0023f3255d461d9',
    '3803f60c4dc433d66655',
    '1918568',
    $options
);

if ($type === 'message' && !empty($message)) {
    try {
        // Enforce UTF8MB4 for Arabic text safety
        mysqli_set_charset($con, 'utf8mb4');

        // Server-side check for blocked words (extra security)
        if ($senderType === 'visitor') {
            $blockedQuery = mysqli_query($con, "SELECT setting_value FROM settings WHERE setting_key = 'blocked_words' LIMIT 1");
            if ($blockedQuery) {
                $blockedRow = mysqli_fetch_assoc($blockedQuery);
                if ($blockedRow && !empty($blockedRow['setting_value'])) {
                    $blockedWords = array_map('trim', explode(',', strtolower($blockedRow['setting_value'])));
                    $lowerMessage = strtolower($message);
                    foreach ($blockedWords as $word) {
                        if (!empty($word) && strpos($lowerMessage, $word) !== false) {
                            echo json_encode(['error' => 'Blocked word detected']);
                            exit;
                        }
                    }
                }
            }
        }

        // 1. Ensure session exists in chat_sessions (MUST DO FIRST due to Foreign Key)
        $stmt2 = $con->prepare("INSERT IGNORE INTO chat_sessions (session_id, visitor_name) VALUES (?, ?)");
        $stmt2->bind_param("ss", $sessionId, $visitorName);
        if (!$stmt2->execute()) {
            error_log("DB Execute Failed (sessions): " . $stmt2->error);
        }

        // 2. Save to messages table
        $stmt = $con->prepare("INSERT INTO chat_messages (session_id, sender_type, message, is_read) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $sessionId, $senderType, $message);
        if (!$stmt->execute()) {
            error_log("DB Execute Failed (messages): " . $stmt->error);
            echo json_encode(['error' => 'DB Message Insert failed: ' . $stmt->error]);
            exit;
        }

        // 3. Trigger Pusher Event
        $pusherData = [
            'session_id' => $sessionId,
            'message' => $message,
            'sender_type' => $senderType,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($senderType === 'visitor') {
            $pusher->trigger('admin-global', 'new-visitor-message', [
                'session_id' => $sessionId,
                'visitor_name' => $visitorName
            ]);
        }
        
        $pusher->trigger('chat-' . $sessionId, 'new-message', $pusherData);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Chat API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($type === 'typing') {
    try {
        $pusher->trigger('chat-' . $sessionId, 'typing', ['sender_type' => $senderType]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid data state']);
}
?>
