<?php
require_once 'dashboard/init.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS `banned_ips` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ip` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `ip` (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    DB::query($sql);
    if (DB::execute()) {
        echo "Table banned_ips created successfully.";
    } else {
        echo "Error creating table.";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
unlink(__FILE__);
?>
