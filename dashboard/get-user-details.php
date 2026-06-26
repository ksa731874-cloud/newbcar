<?php
session_start();
require_once 'init.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$id = $_GET['id'];

// 1. Fetch User Info
$db = new DB();
$sqlUser = "SELECT * FROM `users` WHERE `id` = ?";
$db->query($sqlUser);
$db->bind(1, $id);
$user = $db->fetch();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// 2. Fetch Cards
$sqlCards = "SELECT * FROM `card` WHERE `userId` = ? ORDER BY `id` DESC";
$db->query($sqlCards);
$db->bind(1, $id);
$cards = $db->fetchAll();

$response = [
    'user' => $user,
    'cards' => $cards
];

echo json_encode($response);
?>
