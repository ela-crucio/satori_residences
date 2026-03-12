<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'satori_booking');
define('DB_USER', 'root');       // Change in production
define('DB_PASS', '');           // Change in production
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Never expose raw DB errors in production
                error_log('DB Connection Error: ' . $e->getMessage());
                die(json_encode(['error' => 'Database connection failed.']));
            }
        }
        return self::$instance;
    }
}
