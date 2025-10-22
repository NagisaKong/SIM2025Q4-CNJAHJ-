<?php

declare(strict_types=1);

use CSRPlatform\Shared\Utils\Validation;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testValidatesRequiredFields(): void
    {
        $validator = new Validation();
        $result = $validator->validate(['name' => 'Alice'], ['name' => 'required']);
        self::assertTrue($result);
    }

    public function testFailsOnEmptyRequired(): void
    {
        $validator = new Validation();
        $result = $validator->validate(['name' => ''], ['name' => 'required']);
        self::assertFalse($result);
        self::assertNotEmpty($validator->errors()['name'] ?? []);
    }
}
