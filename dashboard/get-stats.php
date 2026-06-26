<?php
require_once 'init.php';
header('Content-Type: application/json');

$User = new User();
$stats = $User->getDashboardStats();

echo json_encode($stats);
