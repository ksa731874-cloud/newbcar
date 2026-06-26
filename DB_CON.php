<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASSWORD = "";

$DB_NAME = "dalatew";

$con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if (!$con) {
    echo "<script>alert('NO CONNECTION')</script>";
    die();
}

?>