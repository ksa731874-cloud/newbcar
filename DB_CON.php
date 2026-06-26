<?php
/**
 * Database Configuration - Railway MySQL Integration
 * Supports both Railway environment variables and local .env configuration
 * 
 * Railway automatically provides these variables:
 * - MYSQLHOST: Database host
 * - MYSQLPORT: Database port (default: 3306)
 * - MYSQLUSER: Database user
 * - MYSQLPASSWORD: Database password
 * - MYSQL_DATABASE: Database name
 * - MYSQL_ROOT_PASSWORD: Root password
 */

// Load environment variables from .env file if it exists (for local development)
if (file_exists(__DIR__ . '/.env')) {
    $env_file = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_file as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Railway MySQL environment variables with fallbacks
$DB_HOST = getenv('MYSQLHOST') ?: getenv('RAILWAY_PRIVATE_DOMAIN') ?: 'localhost';
$DB_PORT = getenv('MYSQLPORT') ?: '3306';
$DB_USER = getenv('MYSQLUSER') ?: 'root';
$DB_PASSWORD = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
$DB_NAME = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'railway';

// Debug mode (set to false in production)
$DEBUG_MODE = getenv('DEBUG_MODE') === 'true';

// Attempt database connection
try {
    $con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, (int)$DB_PORT);
    
    if (!$con) {
        $error_msg = mysqli_connect_error();
        if ($DEBUG_MODE) {
            echo "<script>alert('Database Connection Failed: " . htmlspecialchars($error_msg) . "')</script>";
        } else {
            echo "<script>alert('Database Connection Failed. Please contact administrator.')</script>";
        }
        error_log("Database Connection Error: " . $error_msg);
        die();
    }
    
    // Set charset to UTF-8
    mysqli_set_charset($con, 'utf8mb4');
    
    // Log successful connection (optional)
    if ($DEBUG_MODE) {
        error_log("Database connected successfully to: " . $DB_NAME . " on " . $DB_HOST);
    }
    
} catch (Exception $e) {
    if ($DEBUG_MODE) {
        echo "<script>alert('Database Error: " . htmlspecialchars($e->getMessage()) . "')</script>";
    } else {
        echo "<script>alert('Database Error. Please contact administrator.')</script>";
    }
    error_log("Database Exception: " . $e->getMessage());
    die();
}

?>

