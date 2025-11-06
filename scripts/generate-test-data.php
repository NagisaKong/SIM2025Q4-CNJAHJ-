<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CSRPlatform\Shared\Database\DataGenerator;

$generator = new DataGenerator();
$generator->seed();

echo "Demo data seeded successfully." . PHP_EOL;
