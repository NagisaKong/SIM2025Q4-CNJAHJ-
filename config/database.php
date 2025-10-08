<?php

return [
    'dsn' => getenv('DB_DSN') ?: 'pgsql:host=127.0.0.1;port=5432;dbname=csr_platform',
    'username' => getenv('DB_USERNAME') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: 'postgres',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];
