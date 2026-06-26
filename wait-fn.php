<?php
require_once('./dashboard/functions.php');

session_start();

$id = $_SESSION['card_id'];

$get = 'select * from card where id='.$id.'';
$query = mysqli_query($db_connection,$get);
$row = mysqli_fetch_assoc($query);

echo json_encode(['status' => $row['status'], 'url' => $row['url']]);