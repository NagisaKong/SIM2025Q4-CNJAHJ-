<?php
return [
    'dsn' => getenv('CSR_PLATFORM_DSN') ?: 'sqlite:' . __DIR__ . '/../storage/csr_platform.sqlite',
    'username' => getenv('CSR_PLATFORM_DB_USER') ?: null,
    'password' => getenv('CSR_PLATFORM_DB_PASS') ?: null,
];
