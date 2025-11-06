<?php
return [
    'dsn' => 'pgsql:host=127.0.0.1;port=5432;dbname=csr_platform',
    'user' => 'csr_user',
    'password' => 'yt801476@',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
