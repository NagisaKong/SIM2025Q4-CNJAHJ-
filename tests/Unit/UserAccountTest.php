<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Entity\UserAccount;
use App\Repositories\UserRepository;

final class UserAccountTest extends TestCase
{
    public function testValidatePasswordInvalid(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $account = new UserAccount($repository);

        $this->assertFalse($account->validatePassword('short'));
        $this->assertFalse($account->validatePassword('allletters'));
        $this->assertFalse($account->validatePassword('12345678'));
        $this->assertFalse($account->validatePassword('space here1'));
    }

    public function testValidatePasswordValid(): void
    {
        $repository = $this->createStub(UserRepository::class);
        $account = new UserAccount($repository);

        $this->assertTrue($account->validatePassword('ValidPass1'));
        $this->assertTrue($account->validatePassword('Alpha1234'));
    }
}
