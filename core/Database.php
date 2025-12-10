<?php
require_once __DIR__ . '/../config/config.php';

class Database {
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        }

        return self::$instance;
    }
}
