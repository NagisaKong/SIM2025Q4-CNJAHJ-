<?php

return [
    'name' => 'CSR Match Platform',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'session_name' => getenv('SESSION_NAME') ?: 'csr_session',
    'csrf_secret' => getenv('CSRF_SECRET') ?: 'secret-key',
];
