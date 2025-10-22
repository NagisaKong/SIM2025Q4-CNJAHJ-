<?php

declare(strict_types=1);

use CSRPlatform\Shared\Database\DatabaseConnection;
use PDO;
use PHPUnit\Framework\TestCase;

final class DatabaseConnectionTest extends TestCase
{
    public function testItConnectsAndReadsSeedAccount(): void
    {
        $pdo = DatabaseConnection::get();
        self::assertInstanceOf(PDO::class, $pdo, 'Failed asserting that a PDO connection was returned.');

        $statement = $pdo->query('SELECT "email" FROM "userAccounts" ORDER BY "accountID" ASC LIMIT 1');
        $row = $statement ? $statement->fetch() : false;

        self::assertIsArray($row, 'No rows were returned from userAccounts.');
        self::assertArrayHasKey('email', $row, 'Email column missing from query result.');
        self::assertNotEmpty($row['email'], 'Seed email address should not be empty.');
    }
}
