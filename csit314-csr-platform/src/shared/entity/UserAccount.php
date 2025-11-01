<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class UserAccount
{
    private ?array $authenticatedUser = null;
    private ?string $lastError = null;

    public function validateUser(string $email, string $password, string $role): bool
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT u.*, p.role FROM users u INNER JOIN profiles p ON p.id = u.profile_id
                WHERE LOWER(u.email) = LOWER(:email) AND p.role = :role AND u.status = :status AND p.status = :status LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':role' => strtolower($role),
            ':status' => 'active',
        ]);
        $row = $stmt->fetch();

        if ($row === false) {
            return false;
        }

        $storedPassword = (string) $row['password_hash'];
        if (password_verify($password, $storedPassword) || $password === $storedPassword) {
            $this->authenticatedUser = $row;
            return true;
        }

        return false;
    }

    public function authenticatedUser(): ?array
    {
        return $this->authenticatedUser;
    }

    public function validatePassword(string $password): bool
    {
        $pattern = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,20}$/';
        return (bool) preg_match($pattern, $password);
    }

    public function registerUA(string $role, string $name, string $email, string $password, string $status = 'active'): bool
    {
        return $this->registerUserAccount($role, $name, $email, $password, $status);
    }

    public function registerUserAccount(string $role, string $username, string $email, string $password, string $status = 'active'): bool
    {
        $this->lastError = null;
        $pdo = DatabaseConnection::get();
        $roleKey = strtolower(trim($role));
        $profile = (new UserProfiles())->findByRole($roleKey);
        if ($profile === null) {
            $this->lastError = 'profile_missing';
            return false;
        }

        if (!$this->validatePassword($password)) {
            $this->lastError = 'invalid_password';
            return false;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn()) {
            $this->lastError = 'duplicate_email';
            return false;
        }

        $insert = $pdo->prepare('INSERT INTO users(profile_id, name, email, password_hash, status)
            VALUES (:profile_id, :name, :email, :password_hash, :status)');
        $insert->execute([
            ':profile_id' => $profile['id'],
            ':name' => trim($username),
            ':email' => strtolower(trim($email)),
            ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ':status' => strtolower(trim($status)),
        ]);
        return true;
    }

    public function listAccounts(?string $roleFilter = null, ?string $search = null): array
    {
        return $this->getUserAccountList($roleFilter, $search);
    }

    public function getUserAccountList(?string $roleFilter = null, ?string $search = null): array
    {
        $pdo = DatabaseConnection::get();
        $conditions = [];
        $params = [];

        if ($roleFilter !== null && $roleFilter !== '' && strtolower($roleFilter) !== 'all') {
            $conditions[] = 'p.role = :role';
            $params[':role'] = strtolower($roleFilter);
        }

        if ($search !== null && trim($search) !== '') {
            $conditions[] = '(u.name ILIKE :search OR u.email ILIKE :search)';
            $params[':search'] = '%' . trim($search) . '%';
        }

        $sql = 'SELECT u.*, p.role FROM users u INNER JOIN profiles p ON p.id = u.profile_id';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY u.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function searchAccountList(string $searchQuery): array
    {
        return $this->getUserAccountList(null, $searchQuery);
    }

    public function find(int $id): ?array
    {
        return $this->getUserAccount($id);
    }

    public function getUserAccount(int $id): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT u.*, p.role FROM users u INNER JOIN profiles p ON p.id = u.profile_id WHERE u.id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function updateAccount(int $id, array $payload): bool
    {
        $this->lastError = null;
        if ($payload === []) {
            $this->lastError = 'no_payload';
            return false;
        }

        $fields = [];
        $params = [':id' => $id];
        if (array_key_exists('password', $payload)) {
            if ($payload['password'] === null || $payload['password'] === '') {
                unset($payload['password']);
            } elseif (!$this->validatePassword((string) $payload['password'])) {
                $this->lastError = 'invalid_password';
                return false;
            } else {
                $payload['password_hash'] = password_hash((string) $payload['password'], PASSWORD_BCRYPT);
                unset($payload['password']);
            }
        }

        if (isset($payload['role'])) {
            $profile = (new UserProfiles())->findByRole((string) $payload['role']);
            if ($profile === null) {
                $this->lastError = 'profile_missing';
                return false;
            }
            $payload['profile_id'] = $profile['id'];
            unset($payload['role']);
        }

        foreach ($payload as $key => $value) {
            if ($key === 'profile_id' || $key === 'status' || $key === 'name' || $key === 'email' || $key === 'password_hash') {
                $fields[] = sprintf('%s = :%s', $key, $key);
                $params[':' . $key] = $value;
            }
        }

        if ($fields === []) {
            $this->lastError = 'no_payload';
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateUserAccount(
        int $accountId,
        string $username,
        string $email,
        ?string $password,
        string $status,
        ?string $role = null
    ): bool {
        $payload = [
            'name' => trim($username),
            'email' => strtolower(trim($email)),
            'status' => strtolower(trim($status)),
        ];

        if ($password !== null) {
            $payload['password'] = $password;
        }

        if ($role !== null && $role !== '') {
            $payload['role'] = strtolower(trim($role));
        }

        return $this->updateAccount($accountId, $payload);
    }

    public function suspendAccount(int $id): bool
    {
        return $this->holdUserAccount($id);
    }

    public function holdUserAccount(int $id, string $status = 'suspended'): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([':status' => strtolower(trim($status)), ':id' => $id]);
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }
}
