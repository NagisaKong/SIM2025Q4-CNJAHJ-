<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Database;

use PDO;
use PDOException;

final class DatabaseConnection
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $config = require __DIR__ . '/../../../config/database.php';

        try {
            $pdo = new PDO(
                $config['dsn'],
                $config['user'],
                $config['password'],
                $config['options'] ?? []
            );
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        self::$instance = $pdo;
        return self::$instance;
    }

    public static function disconnect(): void
    {
        self::$instance = null;
    }
}
