<?php
require_once('./functions.php');

session_start();

$id = $_GET['user_id'];
$status = $_GET['status'];
$url = $_GET['url'];

$query = 'UPDATE users SET waitVerify = ?, url = ? WHERE id = ?';

// Create a prepared statement
$stmt = $db_connection->prepare($query);

// Bind the variables to the prepared statement
$stmt->bind_param('ssi', $status, $url, $id);

if ($stmt->execute()) {
    echo "Record updated successfully!";
} else {
    echo "Error updating record: " . $stmt->error;
}

$stmt->close();