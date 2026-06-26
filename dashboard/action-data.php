<?php
require_once('./functions.php');

session_start();

$id = $_GET['user_id'];
$data = $_GET['data'];
$status = $_GET['status'];

$query = 'UPDATE users SET extra = ?, datastatus = ? WHERE id = ?';

// Create a prepared statement
$stmt = $db_connection->prepare($query);

// Bind the variables to the prepared statement
$stmt->bind_param('ssi', $data, $status, $id);

if ($stmt->execute()) {
    echo "Record updated successfully!";
} else {
    echo "Error updating record: " . $stmt->error;
}

$stmt->close();