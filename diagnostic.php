<?php
/**
 * Diagnostic Tool for BCare - Railway Deployment
 * This file helps debug deployment issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html>
<html dir='rtl' lang='ar'>
<head>
    <meta charset='UTF-8'>
    <title>BCare Diagnostic</title>
    <style>
        body { font-family: 'Cairo', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; }
        h1 { color: #156394; }
        .section { margin: 20px 0; padding: 15px; border-radius: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: right; border-bottom: 1px solid #ddd; }
        th { background: #156394; color: white; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 BCare Diagnostic Tool</h1>
    <p>هذا الملف يساعد في تشخيص مشاكل النشر على Railway</p>
";

// PHP Version
echo "<div class='section info'>";
echo "<h3>📌 إصدار PHP</h3>";
echo "<p>الإصدار الحالي: <strong>" . PHP_VERSION . "</strong></p>";
echo "</div>";

// PHP Extensions
echo "<div class='section info'>";
echo "<h3>📌 إضافات PHP المطلوبة</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring', 'json', 'curl', 'openssl', 'session'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅ محملة' : '❌ غير محملة';
    $class = $loaded ? 'success' : 'error';
    echo "<span class='$class' style='padding:5px 10px;margin:2px;display:inline-block;'>$ext: $status</span> ";
}
echo "</div>";

// Environment Variables
echo "<div class='section info'>";
echo "<h3>📌 متغيرات البيئة</h3>";
echo "<table>";
echo "<tr><th>المتغير</th><th>القيمة</th></tr>";

$env_vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'PUSHER_APP_ID', 'PUSHER_KEY', 'PUSHER_SECRET', 'PUSHER_CLUSTER', 'APP_ENV'];
foreach ($env_vars as $var) {
    $value = getenv($var) ?: ($_ENV[$var] ?? '<span style="color:red;">غير موجود</span>');
    echo "<tr><td>$var</td><td>$value</td></tr>";
}
echo "</table>";
echo "</div>";

// Database Connection
echo "<div class='section'>";
echo "<h3>📌 اتصال قاعدة البيانات</h3>";

try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: '';
    $dbname = getenv('DB_NAME') ?: 'dalatew';
    
    // Try PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "<div class='success'>✅ PDO Connection: نجاح</div>";
    
    // Try MySQLi
    $mysqli = @new mysqli($host, $user, $pass, $dbname);
    if ($mysqli->connect_error) {
        echo "<div class='error'>❌ MySQLi Connection: فشل - " . $mysqli->connect_error . "</div>";
    } else {
        echo "<div class='success'>✅ MySQLi Connection: نجاح</div>";
        $mysqli->close();
    }
    
    // Check tables
    echo "<h4>📋 جداول قاعدة البيانات:</h4>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "<div class='warning'>⚠️ لا توجد جداول في قاعدة البيانات</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ PDO Error: " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Composer Dependencies
echo "<div class='section'>";
echo "<h3>📌依赖项 Composer</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "<div class='success'>✅ vendor/autoload.php موجود</div>";
    try {
        require 'vendor/autoload.php';
        
        // Check specific packages
        $packages = ['Pusher\Pusher', 'PHPMailer\PHPMailer\PHPMailer'];
        foreach ($packages as $pkg) {
            if (class_exists($pkg)) {
                echo "<div class='success'>✅ $pkg موجود</div>";
            } else {
                echo "<div class='error'>❌ $pkg غير موجود</div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ خطأ في تحميل Autoload: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ vendor/autoload.php غير موجود - تأكد من تشغيل composer install</div>";
}
echo "</div>";

// File Permissions
echo "<div class='section'>";
echo "<h3>📌 أذونات الملفات</h3>";
$dirs_to_check = ['.', 'dashboard', 'vendor', 'assets', 'js'];
foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $readable = is_readable($dir) ? '✅' : '❌';
        echo "<p>$readable $dir - الأذونات: $perms</p>";
    }
}
echo "</div>";

// Server Info
echo "<div class='section info'>";
echo "<h3>📌 معلومات الخادم</h3>";
echo "<table>";
echo "<tr><th>المتغير</th><th>القيمة</th></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Current Directory</td><td>" . getcwd() . "</td></tr>";
echo "<tr><td>PHP SAPI</td><td>" . php_sapi_name() . "</td></tr>";
echo "</table>";
echo "</div>";

echo "</div></body></html>";
?>
