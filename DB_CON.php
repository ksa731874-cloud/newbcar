<?php
// Database Configuration - Supports Environment Variables for Railway
// Support Railway's MySQL environment variables
$DB_HOST = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$DB_PORT = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$DB_USER = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$DB_PASSWORD = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$DB_NAME = getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'dalatew';

// Connect to database
$con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if (!$con) {
    echo "<script>alert('Database Connection Failed: " . mysqli_connect_error() . "')</script>";
    die();
}

// Set charset
mysqli_set_charset($con, 'utf8mb4');

?>