<?php

return [
    'dsn' => getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/../storage/database.sqlite',
    'username' => getenv('DB_USERNAME') ?: null,
    'password' => getenv('DB_PASSWORD') ?: null,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];
