<?php
require_once 'DB_CON.php';

$sql1 = "ALTER TABLE chat_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0";
$sql2 = "ALTER TABLE chat_sessions ADD COLUMN last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";

mysqli_query($con, $sql1);
mysqli_query($con, $sql2);

echo "Database updated for unread messages tracking.";
?>
