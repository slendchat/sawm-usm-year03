<?php
/** 
 * Application configuration file.
 */

session_start();

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
