<?php

declare(strict_types=1);

use CSRPlatform\Shared\Boundary\FormValidator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testValidatesRequiredFields(): void
    {
        $validator = new FormValidator();
        $result = $validator->validate(['name' => 'Alice'], ['name' => 'required']);
        self::assertTrue($result);
    }

    public function testFailsOnEmptyRequired(): void
    {
        $validator = new FormValidator();
        $result = $validator->validate(['name' => ''], ['name' => 'required']);
        self::assertFalse($result);
        self::assertNotEmpty($validator->errors()['name'] ?? []);
    }
}
