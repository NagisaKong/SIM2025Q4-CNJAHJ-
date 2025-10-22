<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserAccount;

final class viewAccountsController
{
    public function __construct(private UserAccount $accounts)
    {
    }

    public function list(?string $role = null, ?string $search = null): array
    {
        return $this->accounts->listAccounts($role, $search);
    }

    public function find(int $id): ?array
    {
        return $this->accounts->find($id);
    }

    public function suspend(int $id): bool
    {
        return $this->accounts->suspendAccount($id);
    }
}
