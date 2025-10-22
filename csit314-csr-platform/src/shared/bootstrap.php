<?php
declare(strict_types=1);

$baseDir = __DIR__;
require_once $baseDir . '/database/DatabaseConnection.php';
require_once $baseDir . '/database/DataGenerator.php';
require_once $baseDir . '/utils/Validation.php';
require_once $baseDir . '/utils/Logger.php';
require_once $baseDir . '/entity/UserAccount.php';
require_once $baseDir . '/entity/UserProfiles.php';
require_once $baseDir . '/entity/Request.php';
require_once $baseDir . '/entity/Shortlist.php';
require_once $baseDir . '/entity/serviceCategories.php';

use shared\database\DatabaseConnection;
use shared\database\DataGenerator;

DatabaseConnection::get();
DataGenerator::migrateAndSeed();
