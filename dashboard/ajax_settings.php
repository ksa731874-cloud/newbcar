<?php
require_once 'init.php';

header('Content-Type: application/json');

if (!$User->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['chat_disabled'])) {
    // If 'chat_disabled' is true (checked), it means they want to disable it, so chat_enabled should be '0'
    $chatEnabled = $data['chat_disabled'] ? '0' : '1';
    if ($User->updateSetting('chat_enabled', $chatEnabled)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update database']);
    }
} elseif (isset($data['action']) && $data['action'] === 'change_password') {
    $newPass = $data['new_password'] ?? '';
    if (empty($newPass)) {
        echo json_encode(['success' => false, 'error' => 'Password cannot be empty']);
        exit;
    }
    if ($User->updateAdminPassword($_SESSION['user_session'], $newPass)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update password']);
    }
} elseif (isset($data['action']) && $data['action'] === 'update_blocked_words') {
    $words = $data['blocked_words'] ?? '';
    if ($User->updateSetting('blocked_words', $words)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update blocked words']);
    }
} elseif (isset($data['action']) && $data['action'] === 'delete_all_data') {
    if ($User->DeleteAllUsers()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
