<?php
declare(strict_types=1);

namespace shared\database;

use PDO;

class DataGenerator
{
    public static function migrateAndSeed(): void
    {
        $pdo = DatabaseConnection::get();
        self::createTables($pdo);
        self::seed($pdo);
    }

    private static function createTables(PDO $pdo): void
    {
        $tableExists = (bool) $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='profiles'")->fetchColumn();
        if ($tableExists) {
            return;
        }

        $schema = file_get_contents(dirname(__DIR__, 3) . '/create_data_table.sql');
        $pdo->exec($schema);
    }

    private static function seed(PDO $pdo): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM profiles')->fetchColumn();
        if ($count > 0) {
            return;
        }

        $profiles = [
            ['role' => 'user_admin', 'description' => 'User Administrator'],
            ['role' => 'csr_rep', 'description' => 'CSR Representative'],
            ['role' => 'pin', 'description' => 'Person In Need'],
            ['role' => 'platform_manager', 'description' => 'Platform Manager'],
        ];
        $stmt = $pdo->prepare('INSERT INTO profiles (role, description, status) VALUES (:role, :description, :status)');
        foreach ($profiles as $profile) {
            $stmt->execute([
                ':role' => $profile['role'],
                ':description' => $profile['description'],
                ':status' => 'active',
            ]);
        }

        $users = [
            ['profile_id' => 1, 'name' => 'Admin User', 'email' => 'admin@example.com', 'password' => 'password123', 'status' => 'active'],
            ['profile_id' => 2, 'name' => 'CSR Representative', 'email' => 'csr.rep@example.com', 'password' => 'password123', 'status' => 'active'],
            ['profile_id' => 3, 'name' => 'Person In Need', 'email' => 'pin.user@example.com', 'password' => 'password123', 'status' => 'active'],
            ['profile_id' => 4, 'name' => 'Platform Manager', 'email' => 'manager@example.com', 'password' => 'password123', 'status' => 'active'],
        ];
        $userStmt = $pdo->prepare('INSERT INTO users (profile_id, name, email, password, status) VALUES (:profile_id, :name, :email, :password, :status)');
        foreach ($users as $user) {
            $userStmt->execute([
                ':profile_id' => $user['profile_id'],
                ':name' => $user['name'],
                ':email' => $user['email'],
                ':password' => password_hash($user['password'], PASSWORD_BCRYPT),
                ':status' => $user['status'],
            ]);
        }

        $categories = ['Food Assistance', 'Medical Support', 'Education Aid'];
        $catStmt = $pdo->prepare('INSERT INTO service_categories (name, status) VALUES (:name, :status)');
        foreach ($categories as $name) {
            $catStmt->execute([':name' => $name, ':status' => 'active']);
        }

        $reqStmt = $pdo->prepare('INSERT INTO pin_requests (pin_id, category_id, title, description, location, status, requested_date, views_count, shortlist_count) VALUES (:pin_id, :category_id, :title, :description, :location, :status, :requested_date, :views_count, :shortlist_count)');
        $requests = [
            ['pin_id' => 3, 'category_id' => 1, 'title' => 'Groceries for elderly couple', 'description' => 'Need weekly groceries delivered.', 'location' => 'Downtown', 'status' => 'open', 'requested_date' => '2024-01-05'],
            ['pin_id' => 3, 'category_id' => 2, 'title' => 'Medical appointment transport', 'description' => 'Need ride to hospital appointment.', 'location' => 'Uptown', 'status' => 'open', 'requested_date' => '2024-01-07'],
            ['pin_id' => 3, 'category_id' => 3, 'title' => 'Tutoring support', 'description' => 'Math tutoring needed twice a week.', 'location' => 'Midtown', 'status' => 'matched', 'requested_date' => '2023-12-15'],
        ];
        foreach ($requests as $request) {
            $reqStmt->execute([
                ':pin_id' => $request['pin_id'],
                ':category_id' => $request['category_id'],
                ':title' => $request['title'],
                ':description' => $request['description'],
                ':location' => $request['location'],
                ':status' => $request['status'],
                ':requested_date' => $request['requested_date'],
                ':views_count' => rand(10, 80),
                ':shortlist_count' => rand(1, 12),
            ]);
        }
    }
}
