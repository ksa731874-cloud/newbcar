<?php
require_once __DIR__ . '/DB_CON.php';

header('Content-Type: application/json');

$sessionId = $_GET['session_id'] ?? $_GET['visitor_id'] ?? '';
$isAdmin = isset($_GET['is_admin']) && $_GET['is_admin'] === '1';

if (!$sessionId && !$isAdmin) {
    echo json_encode([]);
    exit;
}

if ($isAdmin) {
    // Admin might want all messages for a specific visitor or just all messages
    if (isset($_GET['visitor_id'])) {
        $vId = mysqli_real_escape_string($con, $_GET['visitor_id']);
        $sql = "SELECT * FROM chat_messages WHERE session_id = '$vId' ORDER BY created_at ASC";
    } else {
        // Just all recent messages
        $sql = "SELECT * FROM chat_messages ORDER BY created_at ASC LIMIT 100";
    }
} else {
    $sId = mysqli_real_escape_string($con, $sessionId);
    $sql = "SELECT * FROM chat_messages WHERE session_id = '$sId' ORDER BY created_at ASC";
}

$result = mysqli_query($con, $sql);
$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['time'] = date('H:i', strtotime($row['created_at']));
    $messages[] = $row;
}

echo json_encode($messages);
?>
