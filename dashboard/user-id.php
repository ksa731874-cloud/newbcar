<?php
require_once('./functions.php');

session_start();

$id = $_GET['user_id'];

$get = 'select * from users where id='.$id.' order by id desc';
$query = mysqli_query($db_connection,$get);

$rows = array();

while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}
echo json_encode($rows);