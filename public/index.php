<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Container;

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        putenv("{$key}={$value}");
    }
}

$container = new Container();
$app = new Application($container);
$app->bootstrap();
$app->handle();
