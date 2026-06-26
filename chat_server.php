<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/src/Chat.php';

$p = 8080;
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    $p
);

echo "WebSocket Server running on port $p...\n";
$server->run();
