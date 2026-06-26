<?php
/**
 * ajax_bin_proxy.php
 * Resolves CORS issues by fetching BIN details server-side.
 */
header('Content-Type: application/json');

$bin = isset($_GET['bin']) ? preg_replace('/\D/', '', $_GET['bin']) : '';

if (strlen($bin) < 6) {
    echo json_encode(['error' => 'Invalid BIN length']);
    exit;
}

$url = "https://lookup.binlist.net/" . substr($bin, 0, 6);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept-Version: 3',
    'User-Agent: B-CARE Dashboard Proxy'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo $response;
} else {
    echo json_encode(['error' => 'BIN not found or service unavailable', 'status' => $httpCode]);
}
