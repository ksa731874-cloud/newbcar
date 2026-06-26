<?php
require_once 'dashboard/init.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS `banned_cards` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `card_number` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `card_number` (`card_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db = new DB();
    $db->query($sql);
    if ($db->execute()) {
        echo "Table banned_cards created successfully via DB class.";
    } else {
        echo "Error or already created.";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
unlink(__FILE__);
?>