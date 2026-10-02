<?php

class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            $configPath = __DIR__ . '/../config/database.php';
            if (!is_file($configPath)) {
                die('Database configuration is missing. Follow the README setup steps.');
            }
            $config = require $configPath;
            self::$connection = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['dbname']
            );

            if (self::$connection->connect_error) {
                die('Database connection failed: ' . self::$connection->connect_error);
            }

            self::$connection->set_charset('utf8mb4');
            self::$connection->query("SET collation_connection = utf8mb4_unicode_ci");
        }

        return self::$connection;
    }

    public static function disconnect() {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
