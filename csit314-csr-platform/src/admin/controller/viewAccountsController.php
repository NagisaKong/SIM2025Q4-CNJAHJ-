<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserAccount;

final class viewAccountsController
{
    public function __construct(private UserAccount $accounts)
    {
    }

    public function viewUserAccountList(?string $role = null, ?string $search = null): array
    {
        return $this->accounts->getUserAccountList($role, $search);
    }

    public function searchAccountList(string $searchQuery, ?string $role = null): array
    {
        if ($role !== null && $role !== '' && strtolower($role) !== 'all') {
            return $this->accounts->getUserAccountList($role, $searchQuery);
        }

        return $this->accounts->searchAccountList($searchQuery);
    }

    public function viewUserAccount(int $accountId): ?array
    {
        return $this->accounts->getUserAccount($accountId);
    }

    public function suspendUserAccount(int $accountId, string $status = 'suspended'): bool
    {
        return $this->accounts->holdUserAccount($accountId, $status);
    }

    /** @deprecated For backward compatibility */
    public function list(?string $role = null, ?string $search = null): array
    {
        return $this->viewUserAccountList($role, $search);
    }

    /** @deprecated For backward compatibility */
    public function find(int $id): ?array
    {
        return $this->viewUserAccount($id);
    }

    /** @deprecated For backward compatibility */
    public function suspend(int $id): bool
    {
        return $this->suspendUserAccount($id);
    }
}
