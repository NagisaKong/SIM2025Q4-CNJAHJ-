<?php
declare(strict_types=1);

use shared\entity\UserAccount;

class ViewAccountsController
{
    private UserAccount $accounts;

    public function __construct()
    {
        $this->accounts = new UserAccount();
    }

    public function list(?string $search = null, ?string $role = null): array
    {
        if ($search) {
            return $this->accounts->searchUserAccounts($search);
        }

        if ($role && $role !== 'All') {
            return $this->accounts->filterUserAccountsByType($role);
        }

        return $this->accounts->getUserAccountsList();
    }
}
