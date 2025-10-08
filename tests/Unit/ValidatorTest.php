<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Validator;

final class ValidatorTest extends TestCase
{
    public function testValidatesRequiredFields(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->validate(['email' => ''], ['email' => 'required|email']));
        $this->assertArrayHasKey('email', $validator->errors());
    }

    public function testPassesWithValidData(): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->validate(['email' => 'user@example.com'], ['email' => 'required|email']));
    }
}
