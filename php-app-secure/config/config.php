<?php
/** 
 * Application configuration file.
 */

session_start();

// Ensure storage paths for logs exist
define('APP_STORAGE_PATH', __DIR__ . '/../storage');
define('APP_LOG_PATH', APP_STORAGE_PATH . '/logs/app.log');
define('APP_ERROR_LOG_PATH', APP_STORAGE_PATH . '/logs/error.log');
define('APP_SAFE_PAGE_URI', '/errors/safe.html');
define('APP_SAFE_PAGE_PATH', __DIR__ . '/../public' . APP_SAFE_PAGE_URI);

if (!is_dir(APP_STORAGE_PATH)) {
    mkdir(APP_STORAGE_PATH, 0777, true);
} elseif (!is_writable(APP_STORAGE_PATH)) {
    @chmod(APP_STORAGE_PATH, 0777);
}

$logDir = APP_STORAGE_PATH . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
} elseif (!is_writable($logDir)) {
    @chmod($logDir, 0777);
}

/**
 * Establishes a PDO connection to the MySQL database using env vars from docker-compose
 *
 * @var string
 * @var string
 * @var string
 * @var string
 */
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'tickets';
$user = getenv('DB_USER') ?: 'app';
$pass = getenv('DB_PASS') ?: '';

/**
 * PDO instance for database connection.
 *
 * On failure, terminates execution with an error message.
 *
 * @var \PDO
 */
try {
    $db = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8",
        $user,
        $pass,
        [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
    );
} catch (PDOException $e) {
    // die("Database connection failed: " . $e->getMessage());
}
