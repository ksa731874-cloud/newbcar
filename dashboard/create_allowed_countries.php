<?php
require_once 'config.php';
$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASSWORD;
$name = DB_NAME;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass);
    $pdo->exec("CREATE TABLE IF NOT EXISTS allowed_countries (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        country_code VARCHAR(10) UNIQUE NOT NULL, 
        country_name VARCHAR(100) NOT NULL, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
