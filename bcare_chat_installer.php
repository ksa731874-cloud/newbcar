<?php
require_once __DIR__ . '/DB_CON.php';

echo "Setting up chat database tables...\n";

$sql = "
CREATE TABLE IF NOT EXISTS `chat_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `visitor_name` varchar(100) DEFAULT '????',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($con, $sql)) {
    echo "chat_sessions table created or exists.\n";
} else {
    echo "Error creating chat_sessions: " . mysqli_error($con) . "\n";
}

$sql2 = "
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `sender_type` enum('visitor','admin') NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','delivered','seen') DEFAULT 'sent',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `fk_session` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`session_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($con, $sql2)) {
    echo "chat_messages table created or exists.\n";
} else {
    echo "Error creating chat_messages: " . mysqli_error($con) . "\n";
}

echo "Setup done.\n";
?>
