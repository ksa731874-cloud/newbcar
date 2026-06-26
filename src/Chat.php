<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $sessions;
    private $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sessions = [];

        // Include the DB connection variables
        require __DIR__ . '/../DB_CON.php';
        
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
        $this->db = new \PDO($dsn, $DB_USER, $DB_PASSWORD);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data) return;

        $type = $data['type'] ?? '';
        $sessionId = htmlspecialchars($data['session_id'] ?? '');

        switch ($type) {
            case 'init':
                $this->sessions[$from->resourceId] = $sessionId;
                $name = htmlspecialchars($data['name'] ?? 'زائر');

                $stmt = $this->db->prepare("INSERT IGNORE INTO chat_sessions (session_id, visitor_name) VALUES (?, ?)");
                $stmt->execute([$sessionId, $name]);
                break;

            case 'message':
                $message = htmlspecialchars($data['message']);
                $senderType = $data['sender_type'] ?? 'visitor';

                $stmt = $this->db->prepare("INSERT INTO chat_messages (session_id, sender_type, message, status) VALUES (?, ?, ?, 'delivered')");
                $stmt->execute([$sessionId, $senderType, $message]);
                $msgId = $this->db->lastInsertId();

                $response = json_encode([
                    'type' => 'message',
                    'id' => $msgId,
                    'session_id' => $sessionId,
                    'sender_type' => $senderType,
                    'message' => $message,
                    'status' => 'delivered',
                    'time' => date('H:i')
                ]);

                foreach ($this->clients as $client) {
                    // Send to everyone in the same session AND admin (if we tracked admin)
                    // For simplicity, broadcast to the user who matches the session and admins
                    if (isset($this->sessions[$client->resourceId]) && $this->sessions[$client->resourceId] === $sessionId) {
                        $client->send($response);
                    } else if (isset($this->sessions[$client->resourceId]) && $this->sessions[$client->resourceId] === 'admin') {
                        $client->send($response);
                    }
                }
                break;

            case 'typing':
                $response = json_encode(['type' => 'typing', 'session_id' => $sessionId, 'sender_type' => $data['sender_type']]);
                foreach ($this->clients as $client) {
                    if ($from !== $client) {
                        if (isset($this->sessions[$client->resourceId]) && ($this->sessions[$client->resourceId] === $sessionId || $this->sessions[$client->resourceId] === 'admin')) {
                            $client->send($response);
                        }
                    }
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->sessions[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}
