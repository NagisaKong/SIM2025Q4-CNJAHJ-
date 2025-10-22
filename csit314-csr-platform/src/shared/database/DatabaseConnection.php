<?php
declare(strict_types=1);

namespace shared\database;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseConnection
{
    private static ?PDO $connection = null;

    public static function get(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require dirname(__DIR__, 3) . '/config/database.php';
        $dsn = $config['dsn'] ?? null;
        if (!$dsn) {
            throw new RuntimeException('Database DSN is not configured.');
        }

        try {
            $pdo = new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            throw new RuntimeException('Unable to connect to the database: ' . $exception->getMessage());
        }

        if (str_starts_with($dsn, 'sqlite:')) {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        self::$connection = $pdo;
        return $pdo;
    }
}
