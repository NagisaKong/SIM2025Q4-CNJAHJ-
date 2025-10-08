<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\RequestRepository;
use App\Repositories\MatchRepository;
use App\Repositories\ShortlistRepository;
use App\Models\User;
use App\Models\PinRequest;
use App\Models\Shortlist;

class AccountService
{
    public function __construct(
        private UserRepository $users,
        private CategoryRepository $categories,
        private RequestRepository $requests,
        private ShortlistRepository $shortlists,
        private MatchRepository $matches
    ) {
    }

    public function listUsers(int $page, int $perPage, array $filters = []): array
    {
        return $this->users->paginate($page, $perPage, $filters);
    }

    public function createUser(array $data): int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);
        return $this->users->create($data);
    }

    public function updateUser(int $id, array $data): void
    {
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        unset($data['password']);
        $this->users->update($id, $data);
    }

    public function findUser(int $id): ?User
    {
        return $this->users->find($id);
    }

    public function listCategories(int $page, int $perPage, array $filters = []): array
    {
        return $this->categories->paginate($page, $perPage, $filters);
    }

    public function createRequest(array $data): int
    {
        return $this->requests->create($data);
    }

    public function updateRequest(int $id, array $data): void
    {
        $this->requests->update($id, $data);
    }

    public function findRequest(int $id): ?PinRequest
    {
        return $this->requests->find($id);
    }

    public function addShortlist(int $csrId, int $requestId): Shortlist
    {
        $shortlist = $this->shortlists->add($csrId, $requestId);
        $this->requests->incrementShortlist($requestId);
        return $shortlist;
    }

    public function listShortlist(int $csrId): array
    {
        return $this->shortlists->listForCsr($csrId);
    }

    public function listMatchesForCsr(int $csrId, array $filters = []): array
    {
        return $this->matches->listForCsr($csrId, $filters);
    }

    public function listMatchesForPin(int $pinId, array $filters = []): array
    {
        return $this->matches->listForPin($pinId, $filters);
    }
}
