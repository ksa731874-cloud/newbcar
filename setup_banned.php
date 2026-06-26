<?php
require_once 'DB_CON.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS `banned_ips` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ip` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `ip` (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (mysqli_query($con, $sql)) {
        echo "Table banned_ips created successfully.";
    } else {
        echo "Error creating table: " . mysqli_error($con);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
unlink(__FILE__);
?>
