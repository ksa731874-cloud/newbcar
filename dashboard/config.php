<?php

/** MySQL hostname - Supports Environment Variables */
define('DB_HOST', getenv('DB_HOST') ?: (!empty($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost'));

/** MySQL database username */
define('DB_USER', getenv('DB_USER') ?: (!empty($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root'));

/** MySQL database password */
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: (!empty($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : ''));

/** MySQL database name */
define('DB_NAME', getenv('DB_NAME') ?: (!empty($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'dalatew'));

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('CAN_REGISTER', 'none');
define('DEFAULT_ROLE', 'member');

// For production - SECURE should be true
define('SECURE', getenv('APP_ENV') === 'production');

// For production - DEBUG should be false
define('DEBUG', getenv('APP_ENV') !== 'production');

// Pusher Configuration - Supports Environment Variables
define('PUSHER_APP_ID', getenv('PUSHER_APP_ID') ?: '1918568');
define('PUSHER_KEY', getenv('PUSHER_KEY') ?: '4a9de0023f3255d461d9');
define('PUSHER_SECRET', getenv('PUSHER_SECRET') ?: '3803f60c4dc433d66655');
define('PUSHER_CLUSTER', getenv('PUSHER_CLUSTER') ?: 'ap2');

// Application URL
define('APP_URL', getenv('APP_URL') ?: 'https://your-app.railway.app');

?>