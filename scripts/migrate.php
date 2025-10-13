<?php

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO($config['dsn'], $config['username'], $config['password'], $config['options']);

$migrations = glob(__DIR__ . '/../database/migrations/*.sql');
foreach ($migrations as $file) {
    $sql = file_get_contents($file);
    echo "Running migration: {$file}\n";
    $pdo->exec($sql);
}

echo "Migrations complete.\n";
