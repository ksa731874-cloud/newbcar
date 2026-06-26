<?php
require_once('./init.php');

$db = new DB();

echo "Running Migration V2...<br>";

// Add is_pinned
$sql1 = "ALTER TABLE `users` ADD COLUMN `is_pinned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_archived`";
try {
    $db->query($sql1);
    $db->execute();
    echo "✅ Added is_pinned column successfully.<br>";
} catch (Exception $e) {
    echo "ℹ️ is_pinned column might already exist or error: " . $e->getMessage() . "<br>";
}

// Add is_completed
$sql2 = "ALTER TABLE `users` ADD COLUMN `is_completed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_distinguished`";
try {
    $db->query($sql2);
    $db->execute();
    echo "✅ Added is_completed column successfully.<br>";
} catch (Exception $e) {
    echo "ℹ️ is_completed column might already exist or error: " . $e->getMessage() . "<br>";
}

echo "<br><b>Migration finished. Refresh the dashboard now.</b>";
// unlink(__FILE__); // Keep it for now to let the user run it safely
?>