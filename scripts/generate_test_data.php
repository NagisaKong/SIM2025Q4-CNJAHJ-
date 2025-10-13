<?php

require __DIR__ . '/../vendor/autoload.php';

use Faker\Factory;

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
$faker = Factory::create();

$pdo->exec('TRUNCATE TABLE audit_logs, matches, shortlists, pin_requests, service_categories, users, profiles RESTART IDENTITY CASCADE');

$profiles = [
    ['role' => 'user_admin', 'description' => 'User administrator with full account access'],
    ['role' => 'csr_rep', 'description' => 'Corporate social responsibility representative'],
    ['role' => 'pin', 'description' => 'Person in need requesting assistance'],
    ['role' => 'platform_manager', 'description' => 'Platform manager for categories and reports'],
];

$profileIds = [];
$now = date('Y-m-d H:i:s');
$stmtProfile = $pdo->prepare('INSERT INTO profiles (role, description, status, created_at, updated_at) VALUES (:role, :description, :status, :created_at, :updated_at)');
foreach ($profiles as $profile) {
    $stmtProfile->execute([
        'role' => $profile['role'],
        'description' => $profile['description'],
        'status' => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $profileIds[$profile['role']] = (int) $pdo->lastInsertId();
}

$stmtUser = $pdo->prepare('INSERT INTO users (profile_id, name, email, password_hash, status, created_at, updated_at) VALUES (:profile_id, :name, :email, :password_hash, :status, :created_at, :updated_at)');

$accounts = [
    ['role' => 'user_admin', 'name' => 'Admin One', 'email' => 'admin@example.com'],
    ['role' => 'csr_rep', 'name' => 'CSR Representative', 'email' => 'csr.rep@example.com'],
    ['role' => 'pin', 'name' => 'Person In Need', 'email' => 'pin.user@example.com'],
    ['role' => 'platform_manager', 'name' => 'Platform Manager', 'email' => 'manager@example.com'],
];

foreach ($accounts as $account) {
    $stmtUser->execute([
        'profile_id' => $profileIds[$account['role']],
        'name' => $account['name'],
        'email' => $account['email'],
        'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
        'status' => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

// Generate additional random users for each profile
foreach ($profileIds as $role => $id) {
    for ($i = 0; $i < 30; $i++) {
        $stmtUser->execute([
            'profile_id' => $id,
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'status' => $faker->randomElement(['active', 'active', 'suspended']),
            'created_at' => $faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s'),
            'updated_at' => $now,
        ]);
    }
}

$categoryStmt = $pdo->prepare('INSERT INTO service_categories (name, status, created_at, updated_at) VALUES (:name, :status, :created_at, :updated_at)');
$categoryIds = [];
for ($i = 0; $i < 20; $i++) {
    $categoryStmt->execute([
        'name' => ucfirst($faker->word()) . ' Support',
        'status' => $faker->randomElement(['active', 'active', 'suspended']),
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $categoryIds[] = (int) $pdo->lastInsertId();
}

$pinIds = $pdo->query("SELECT id FROM users WHERE profile_id = {$profileIds['pin']}")->fetchAll(PDO::FETCH_COLUMN);
$csrIds = $pdo->query("SELECT id FROM users WHERE profile_id = {$profileIds['csr_rep']}")->fetchAll(PDO::FETCH_COLUMN);

$requestStmt = $pdo->prepare('INSERT INTO pin_requests (pin_id, category_id, title, description, location, status, requested_date, views_count, shortlist_count, created_at, updated_at) VALUES (:pin_id, :category_id, :title, :description, :location, :status, :requested_date, :views_count, :shortlist_count, :created_at, :updated_at)');

$requestIds = [];
for ($i = 0; $i < 120; $i++) {
    $pinId = (int) $faker->randomElement($pinIds);
    $status = $faker->randomElement(['open', 'open', 'in_progress', 'completed']);
    $requestStmt->execute([
        'pin_id' => $pinId,
        'category_id' => $faker->randomElement($categoryIds),
        'title' => ucfirst($faker->words(3, true)),
        'description' => $faker->paragraph(),
        'location' => $faker->city(),
        'status' => $status,
        'requested_date' => $faker->date(),
        'views_count' => $faker->numberBetween(0, 250),
        'shortlist_count' => $faker->numberBetween(0, 50),
        'created_at' => $faker->dateTimeBetween('-1 years')->format('Y-m-d H:i:s'),
        'updated_at' => $now,
    ]);
    $requestIds[] = (int) $pdo->lastInsertId();
}

$shortlistStmt = $pdo->prepare('INSERT INTO shortlists (csr_id, request_id, created_at) VALUES (:csr_id, :request_id, :created_at)');
for ($i = 0; $i < 150; $i++) {
    $shortlistStmt->execute([
        'csr_id' => $faker->randomElement($csrIds),
        'request_id' => $faker->randomElement($requestIds),
        'created_at' => $faker->dateTimeBetween('-6 months')->format('Y-m-d H:i:s'),
    ]);
}

$matchStmt = $pdo->prepare('INSERT INTO matches (csr_id, request_id, status, matched_at, completed_at) VALUES (:csr_id, :request_id, :status, :matched_at, :completed_at)');
for ($i = 0; $i < 160; $i++) {
    $status = $faker->randomElement(['in_progress', 'completed']);
    $matchedAt = $faker->dateTimeBetween('-1 years');
    $completedAt = $status === 'completed' ? $faker->dateTimeBetween($matchedAt)->format('Y-m-d H:i:s') : null;
    $matchStmt->execute([
        'csr_id' => $faker->randomElement($csrIds),
        'request_id' => $faker->randomElement($requestIds),
        'status' => $status,
        'matched_at' => $matchedAt->format('Y-m-d H:i:s'),
        'completed_at' => $completedAt,
    ]);
}

echo "Seed data generated.\n";
