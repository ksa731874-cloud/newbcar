<?php
header('Content-Type: application/json');
session_start();

require_once 'dashboard/init.php';
require_once 'vendor/autoload.php';

// Allow both visitors and admins to authenticate
$user_id = null;
$user_data = [];

if (isset($_SESSION['user_id'])) {
    // Visitor Path
    $user_id = 'visitor-' . $_SESSION['user_id'];
    $row = $User->fetchUserById($_SESSION['user_id']);
    $user_data = [
        'db_id' => $_SESSION['user_id'],
        'ip' => $row->ip ?? $_SERVER['REMOTE_ADDR'],
        'page' => $row->page ?? 'Unknown',
        'type' => 'visitor'
    ];
} else if (isset($_SESSION['user_session'])) {
    // Admin Path
    $user_id = 'admin-' . $_SESSION['user_session'];
    $user_data = [
        'name' => 'Admin',
        'type' => 'admin'
    ];
}

if (!$user_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: No valid session']);
    exit;
}

$socket_id = $_POST['socket_id'];
$channel_name = $_POST['channel_name'];

$options = [
    'cluster' => PUSHER_CLUSTER,
    'useTLS' => true
];

$pusher = new Pusher\Pusher(
    PUSHER_KEY,
    PUSHER_SECRET,
    PUSHER_APP_ID,
    $options
);

// Presence channel data
$presence_data = [
    'user_id' => $user_id,
    'user_info' => $user_data
];

echo $pusher->presence_auth($channel_name, $socket_id, $user_id, $user_data);
?>
