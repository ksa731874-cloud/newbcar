<?php
// Database Configuration - Supports Environment Variables for Railway
// Railway MySQL environment variables
$DB_HOST = getenv('MYSQLHOST') ?: getenv('RAILWAY_PRIVATE_DOMAIN') ?: getenv('DB_HOST') ?: 'localhost';
$DB_PORT = getenv('MYSQLPORT') ?: '3306';
$DB_USER = getenv('MYSQLUSER') ?: 'root';
$DB_PASSWORD = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$DB_NAME = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';

// Connect to database
$con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if (!$con) {
    echo "<script>alert('Database Connection Failed: " . mysqli_connect_error() . "')</script>";
    die();
}

// Set charset
mysqli_set_charset($con, 'utf8mb4');

?>