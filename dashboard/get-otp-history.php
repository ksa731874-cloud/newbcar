<?php
require_once 'init.php';
header('Content-Type: application/json');

$card_id = intval($_GET['card_id'] ?? 0);
if (!$card_id) {
    echo json_encode([]);
    exit;
}

$db = new DB();
$db->query('SELECT `otp`, `created_at` FROM `card_otp_history` WHERE `card_id` = :card_id ORDER BY `created_at` DESC LIMIT 10');
$db->bind(':card_id', $card_id);
$db->execute();
$data = $db->fetchAll();

$result = [];
foreach ($data as $row) {
    $result[] = [
        'otp' => $row->otp,
        'time' => date('d/m H:i', strtotime($row->created_at)),
        'created_at' => $row->created_at
    ];
}

echo json_encode($result);
