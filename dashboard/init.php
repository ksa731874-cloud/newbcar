<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config.php file
require_once 'config.php';

// Included classes
require_once 'classes/db.php';
require_once 'classes/core.php';
require_once 'classes/user.php';

// Include functions.php file
require_once 'functions2.php';

// Check debug mode
debug_mode();

$Core = new Core();
$User = new User();

// Global IP Ban/Firewall Check
try {
    // Advanced IP Detection (CF, Proxies, etc)
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $visitor_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $visitor_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } else {
        $visitor_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    if ($visitor_ip && isset($User) && !$User->isLoggedIn()) {
        // 1. Check IP Ban
        if ($User->isIPBanned($visitor_ip)) {
            header("HTTP/1.1 404 Not Found");
            echo "<h1>404 Not Found</h1>";
            die();
        }

        // 2. Check Allowed Countries
        $allowed = $User->getAllowedCountries();
        if (!empty($allowed)) {
            // Check session cache
            if (!isset($_SESSION['visitor_country']) || $_SESSION['visitor_country'] === 'ERROR') {
                $ctx = stream_context_create(['http' => ['timeout' => 3]]);
                $geo = @file_get_contents("http://ip-api.com/json/{$visitor_ip}", false, $ctx);
                if ($geo) {
                    $geoData = json_decode($geo);
                    if (isset($geoData->status) && $geoData->status === 'success') {
                        $_SESSION['visitor_country'] = $geoData->countryCode;
                    } else {
                        // If it's a local IP or API fails, we shouldn't block by default in dev
                        // but for production, we might want to block UNKNOWN.
                        $_SESSION['visitor_country'] = 'UNKNOWN';
                    }
                } else {
                    $_SESSION['visitor_country'] = 'ERROR';
                }
            }
            
            $visitorCountry = $_SESSION['visitor_country'];
            
            // SECURITY DECISION: If we can't determine the country (ERROR/UNKNOWN), 
            // should we block? Usually yes for a firewall, but for testing on localhost (UNKNOWN),
            // it might block the developer. 
            
            if ($visitorCountry !== 'ERROR') {
                $isPermitted = false;
                foreach ($allowed as $c) {
                    if (strtoupper($c->country_code) === strtoupper($visitorCountry)) {
                        $isPermitted = true;
                        break;
                    }
                }
                
                if (!$isPermitted) {
                    header("HTTP/1.1 404 Not Found");
                    echo "<h1>404 Not Found</h1>";
                    die();
                }
            }
        }
    }
} catch (Exception $e) {
    // Fail safely
}
?>

