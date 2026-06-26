<?php
require_once 'init.php';

$db = new DB();

$sql = "CREATE TABLE IF NOT EXISTS `card_otp_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `card_id` int(11) NOT NULL,
    `otp` varchar(20) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `card_id` (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

$db->query($sql);

if ($db->execute()) {
    echo "<p style='color:green;font-family:monospace;'>✅ تم إنشاء جدول card_otp_history بنجاح</p>";
} else {
    echo "<p style='color:red;font-family:monospace;'>❌ فشل إنشاء الجدول</p>";
}
?>
