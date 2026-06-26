<?php
// Database Configuration - Supports Environment Variables for Railway
$DB_HOST = getenv('DB_HOST') ?: (!empty($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
$DB_PORT = getenv('DB_PORT') ?: (!empty($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306');
$DB_USER = getenv('DB_USER') ?: (!empty($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
$DB_PASSWORD = getenv('DB_PASSWORD') ?: (!empty($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : '');
$DB_NAME = getenv('DB_NAME') ?: (!empty($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'dalatew');

// Connect to database
$con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if (!$con) {
    echo "<script>alert('Database Connection Failed: " . mysqli_connect_error() . "')</script>";
    die();
}

// Set charset
mysqli_set_charset($con, 'utf8mb4');

?>