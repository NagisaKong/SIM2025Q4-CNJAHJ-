<?php

namespace App\Repositories;

use PDO;

abstract class Repository
{
    public function __construct(protected PDO $pdo)
    {
    }
}
