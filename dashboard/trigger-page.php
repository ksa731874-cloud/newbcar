<?php
session_start();

include_once("../vendor/autoload.php");
include_once(__DIR__ . "/../vendor/autoload.php");


$options = array(
    'cluster' => 'ap2',
    'useTLS' => true
);
$pusher = new Pusher\Pusher(
    '4a9de0023f3255d461d9',
    '3803f60c4dc433d66655',
    '1918568',
    $options
);

if (isset($_POST['userId']) && isset($_POST['page'])) {
    $data = [
        'userId' => $_POST['userId'],
        'page' => $_POST['page']
    ];

    $pusher->trigger('presence-bcare', 'remote_redirect', $data);

    echo 'Event triggered successfully';
} else {
    echo 'Missing data';
}
?>
