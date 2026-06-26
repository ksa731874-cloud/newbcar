<?php
/**
 * Pusher Configuration Helper
 * 
 * This file provides unified Pusher configuration throughout the application.
 * You can update these values in one place instead of modifying all files.
 */

// Get Pusher credentials from environment or use defaults
define('PUSHER_APP_ID', getenv('PUSHER_APP_ID') ?: '1918568');
define('PUSHER_KEY', getenv('PUSHER_KEY') ?: '4a9de0023f3255d461d9');
define('PUSHER_SECRET', getenv('PUSHER_SECRET') ?: '3803f60c4dc433d66655');
define('PUSHER_CLUSTER', getenv('PUSHER_CLUSTER') ?: 'ap2');

/**
 * Get Pusher instance with configuration
 * 
 * @return \Pusher\Pusher
 */
function getPusher() {
    $options = array(
        'cluster' => PUSHER_CLUSTER,
        'useTLS' => true
    );
    return new Pusher\Pusher(
        PUSHER_KEY,
        PUSHER_SECRET,
        PUSHER_APP_ID,
        $options
    );
}

/**
 * Trigger Pusher event
 * 
 * @param string $channel Channel name
 * @param string $event Event name
 * @param array $data Data to send
 * @return bool
 */
function triggerPusher($channel, $event, $data) {
    try {
        $pusher = getPusher();
        $pusher->trigger($channel, $event, $data);
        return true;
    } catch (Exception $e) {
        error_log('Pusher Error: ' . $e->getMessage());
        return false;
    }
}
?>
