<?php
session_start();
require_once 'init.php';

header('Content-Type: application/json');

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q']);
$searchQuery = "%" . $q . "%";

// Attempt to search users by id, ip, location, or device
// We'll return max 5 results
try {
    $db = new DB();
    $db->query("SELECT id, ip, location, device, created_at, is_archived, ssn, phone FROM users WHERE id LIKE :q OR ip LIKE :q OR location LIKE :q OR device LIKE :q OR ssn LIKE :q OR phone LIKE :q ORDER BY id DESC LIMIT 5");
    $db->bind(':q', $searchQuery);
    
    $results = $db->fetchAll();
    
    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
