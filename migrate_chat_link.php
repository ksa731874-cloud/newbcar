<?php
require_once 'DB_CON.php';

$sql = "ALTER TABLE users ADD COLUMN chat_session_id VARCHAR(50) DEFAULT NULL";
if (mysqli_query($con, $sql)) {
    echo "Column chat_session_id added successfully.";
} else {
    echo "Error adding column: " . mysqli_error($con);
}
?>
