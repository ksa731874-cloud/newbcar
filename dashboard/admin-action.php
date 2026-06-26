<?php
require_once 'init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => 'Invalid Request']));
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';

if ($action === '') {
    die(json_encode(['success' => false, 'error' => 'Missing action parameter']));
}

if ($action !== 'delete_multiple' && $action !== 'delete_multiple_cards' && $action !== 'ban_ip' && $action !== 'unban_ip' && $action !== 'ban_card' && $action !== 'unban_card' && $action !== 'add_allowed_country' && $action !== 'remove_allowed_country' && $action !== 'get_allowed_countries') {
    if ($id === '' || !is_numeric($id)) {
        die(json_encode(['success' => false, 'error' => 'Missing or invalid ID parameter']));
    }
}

$id = intval($id);

// Instantiate class User (assuming $User is available via init.php, else create it)
if(!isset($User)) {
    $User = new User();
}

try {
    switch ($action) {
        case 'archive':
            $User->archiveUser($id);
            break;
        case 'unarchive':
            $User->unarchiveUser($id);
            break;
        case 'distinguish':
            $User->toggleDistinguished($id);
            break;
        case 'pin':
            $User->togglePin($id);
            break;
        case 'completed':
            $User->toggleCompleted($id);
            break;
        case 'delete':
            $User->DeleteUserById($id);
            break;
        case 'ban_ip':
            $ip = $_POST['ip'] ?? '';
            if ($ip) {
                $User->banIP($ip);
            }
            break;
        case 'delete_multiple':
            $ids = $_POST['ids'] ?? [];
            if(is_array($ids) && count($ids) > 0) {
                $User->deleteMultipleUsers($ids);
            } else {
                throw new Exception('لا توجد عناصر محددة للحذف');
            }
            break;
        case 'unban_ip':
            $ip = $_POST['ip'] ?? '';
            if ($ip) {
                $User->unbanIP($ip);
            }
            break;
        case 'delete_card':
            if ($id) {
                $User->deleteCard($id);
            }
            break;
        case 'delete_multiple_cards':
            $idsArr = $_POST['ids'] ?? [];
            if (!empty($idsArr)) {
                $User->deleteMultipleCards($idsArr);
            }
            break;
        case 'ban_card':
            $cardNum = $_POST['card_number'] ?? '';
            if ($cardNum) {
                $User->banCard($cardNum);
            }
            break;
        case 'unban_card':
            $cardNum = $_POST['card_number'] ?? '';
            if ($cardNum) {
                $User->unbanCard($cardNum);
            }
            break;
        case 'update_rajhi_status':
            $status = $_POST['status'] ?? 0;
            if ($id) {
                $User->UpdateCardRajhiStatus($id, $status);
            }
            break;
        case 'get_allowed_countries':
            $countries = $User->getAllowedCountries();
            echo json_encode(['success' => true, 'countries' => $countries]);
            exit;
        case 'add_allowed_country':
            $code = $_POST['code'] ?? '';
            $name = $_POST['name'] ?? '';
            if ($code && $name) {
                if (!$User->addAllowedCountry($code, $name)) {
                    throw new Exception('فشل في إضافة الدولة، تأكد من قاعدة البيانات');
                }
            } else {
                throw new Exception('بيانات الدولة غير مكتملة');
            }
            break;
        case 'remove_allowed_country':
            $country_id = $_POST['id'] ?? '';
            if ($country_id) {
                if (!$User->removeAllowedCountry($country_id)) {
                    throw new Exception('فشل في حذف الدولة');
                }
            } else {
                throw new Exception('معرف الدولة مفقود');
            }
            break;
        default:
            die(json_encode(['success' => false, 'error' => 'Unknown action']));
    }
    
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
