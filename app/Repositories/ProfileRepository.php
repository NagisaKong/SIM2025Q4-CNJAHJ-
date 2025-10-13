<?php

namespace App\Repositories;

use App\Models\Profile;
use PDO;

class ProfileRepository extends Repository
{
    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT * FROM profiles WHERE 1=1';
        if (!empty($filters['role'])) {
            $sql .= ' AND role = :role';
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        if (!empty($filters['role'])) {
            $stmt->bindValue(':role', $filters['role']);
        }
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_map(fn($row) => Profile::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countSql = 'SELECT COUNT(*) FROM profiles WHERE 1=1';
        if (!empty($filters['role'])) {
            $countSql .= ' AND role = :role';
        }
        if (!empty($filters['status'])) {
            $countSql .= ' AND status = :status';
        }
        $countStmt = $this->pdo->prepare($countSql);
        if (!empty($filters['role'])) {
            $countStmt->bindValue(':role', $filters['role']);
        }
        if (!empty($filters['status'])) {
            $countStmt->bindValue(':status', $filters['status']);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [$items, $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO profiles (role, description, status, created_at, updated_at) VALUES (:role, :description, :status, :created_at, :updated_at)');
        $stmt->execute([
            'role' => $data['role'],
            'description' => $data['description'],
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?Profile
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profiles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Profile::fromArray($row) : null;
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
        $sql = 'UPDATE profiles SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function findByRole(string $role): ?Profile
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profiles WHERE LOWER(role) = LOWER(:role) LIMIT 1');
        $stmt->execute(['role' => $role]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Profile::fromArray($row) : null;
    }
}
