<?php

namespace App\Repositories;

use App\Models\Profile;
use App\Models\User;
use PDO;

class UserRepository extends Repository
{
    public function find(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? User::fromArray($data)->withProfile($this->loadProfile((int) $data['profile_id'])) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? User::fromArray($data)->withProfile($this->loadProfile((int) $data['profile_id'])) : null;
    }

    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT * FROM users WHERE 1=1';
        $params = [];
        if (!empty($filters['q'])) {
            $sql .= ' AND (email LIKE :q OR name LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_map(fn($row) => User::fromArray($row)->withProfile($this->loadProfile((int) $row['profile_id'])), $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countSql = 'SELECT COUNT(*) FROM users WHERE 1=1';
        if (!empty($filters['q'])) {
            $countSql .= ' AND (email LIKE :q OR name LIKE :q)';
        }
        $countStmt = $this->pdo->prepare($countSql);
        if (!empty($filters['q'])) {
            $countStmt->bindValue(':q', '%' . $filters['q'] . '%');
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [$items, $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (profile_id, name, email, password_hash, status, created_at, updated_at) VALUES (:profile_id, :name, :email, :password_hash, :status, :created_at, :updated_at)');
        $stmt->execute([
            'profile_id' => $data['profile_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        $params['updated_at'] = date('Y-m-d H:i:s');
        $fields[] = 'updated_at = :updated_at';
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    private function loadProfile(int $id): ?Profile
    {
        if ($id === 0) {
            return null;
        }
        $stmt = $this->pdo->prepare('SELECT * FROM profiles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? Profile::fromArray($data) : null;
    }
}
