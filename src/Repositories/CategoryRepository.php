<?php

namespace App\Repositories;

use App\Models\ServiceCategory;
use PDO;

class CategoryRepository extends Repository
{
    public function allActive(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM service_categories WHERE status = 'active' ORDER BY name");
        return array_map(fn($row) => ServiceCategory::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT * FROM service_categories WHERE 1=1';
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND name LIKE :q';
        }
        $sql .= ' ORDER BY name LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }
        if (!empty($filters['q'])) {
            $stmt->bindValue(':q', '%' . $filters['q'] . '%');
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_map(fn($row) => ServiceCategory::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countSql = 'SELECT COUNT(*) FROM service_categories WHERE 1=1';
        if (!empty($filters['status'])) {
            $countSql .= ' AND status = :status';
        }
        if (!empty($filters['q'])) {
            $countSql .= ' AND name LIKE :q';
        }
        $countStmt = $this->pdo->prepare($countSql);
        if (!empty($filters['status'])) {
            $countStmt->bindValue(':status', $filters['status']);
        }
        if (!empty($filters['q'])) {
            $countStmt->bindValue(':q', '%' . $filters['q'] . '%');
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [$items, $total];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO service_categories (name, status, created_at, updated_at) VALUES (:name, :status, :created_at, :updated_at)');
        $stmt->execute([
            'name' => $data['name'],
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?ServiceCategory
    {
        $stmt = $this->pdo->prepare('SELECT * FROM service_categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ServiceCategory::fromArray($row) : null;
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
        $sql = 'UPDATE service_categories SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}
