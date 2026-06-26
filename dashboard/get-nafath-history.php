<?php
require_once 'init.php';
header('Content-Type: application/json');

$card_id = intval($_GET['card_id'] ?? 0);
if (!$card_id) {
    echo json_encode([]);
    exit;
}

// Using methods from User class which we just fixed
$history = $User->fetchNafathCodeHistory($card_id);

$result = [];
foreach ($history as $row) {
    $result[] = [
        'code' => $row->code,
        'time' => date('d/m H:i', strtotime($row->created_at)),
        'created_at' => $row->created_at
    ];
}

echo json_encode($result);
