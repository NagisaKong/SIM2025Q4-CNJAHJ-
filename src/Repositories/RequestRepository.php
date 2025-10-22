<?php

namespace App\Repositories;

use App\Models\PinRequest;
use PDO;

class RequestRepository extends Repository
{
    public function find(int $id): ?PinRequest
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pin_requests WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? PinRequest::fromArray($row) : null;
    }

    public function paginateForPin(int $pinId, int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT * FROM pin_requests WHERE pin_id = :pin_id';
        $params = ['pin_id' => $pinId];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (title LIKE :q OR location LIKE :q)';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':'.$key, $value);
        }
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }
        if (!empty($filters['q'])) {
            $stmt->bindValue(':q', '%' . $filters['q'] . '%');
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_map(fn($row) => PinRequest::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countSql = 'SELECT COUNT(*) FROM pin_requests WHERE pin_id = :pin_id';
        if (!empty($filters['status'])) {
            $countSql .= ' AND status = :status';
        }
        if (!empty($filters['q'])) {
            $countSql .= ' AND (title LIKE :q OR location LIKE :q)';
        }
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->bindValue(':pin_id', $pinId, PDO::PARAM_INT);
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
        $stmt = $this->pdo->prepare('INSERT INTO pin_requests (pin_id, category_id, title, description, location, status, requested_date, views_count, shortlist_count, created_at, updated_at) VALUES (:pin_id, :category_id, :title, :description, :location, :status, :requested_date, :views_count, :shortlist_count, :created_at, :updated_at)');
        $stmt->execute([
            'pin_id' => $data['pin_id'],
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'status' => $data['status'] ?? 'open',
            'requested_date' => $data['requested_date'],
            'views_count' => $data['views_count'] ?? 0,
            'shortlist_count' => $data['shortlist_count'] ?? 0,
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
        $sql = 'UPDATE pin_requests SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE pin_requests SET views_count = views_count + 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function incrementShortlist(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE pin_requests SET shortlist_count = shortlist_count + 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function searchForCsr(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT * FROM pin_requests WHERE status IN (\'open\', \'in_progress\')';
        if (!empty($filters['category_id'])) {
            $sql .= ' AND category_id = :category_id';
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (title LIKE :q OR description LIKE :q OR location LIKE :q)';
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND requested_date >= :from';
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND requested_date <= :to';
        }
        $sql .= ' ORDER BY requested_date ASC LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        if (!empty($filters['category_id'])) {
            $stmt->bindValue(':category_id', $filters['category_id']);
        }
        if (!empty($filters['q'])) {
            $stmt->bindValue(':q', '%' . $filters['q'] . '%');
        }
        if (!empty($filters['from'])) {
            $stmt->bindValue(':from', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $stmt->bindValue(':to', $filters['to']);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_map(fn($row) => PinRequest::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countSql = 'SELECT COUNT(*) FROM pin_requests WHERE status IN (\'open\', \'in_progress\')';
        if (!empty($filters['category_id'])) {
            $countSql .= ' AND category_id = :category_id';
        }
        if (!empty($filters['q'])) {
            $countSql .= ' AND (title LIKE :q OR description LIKE :q OR location LIKE :q)';
        }
        if (!empty($filters['from'])) {
            $countSql .= ' AND requested_date >= :from';
        }
        if (!empty($filters['to'])) {
            $countSql .= ' AND requested_date <= :to';
        }
        $countStmt = $this->pdo->prepare($countSql);
        if (!empty($filters['category_id'])) {
            $countStmt->bindValue(':category_id', $filters['category_id']);
        }
        if (!empty($filters['q'])) {
            $countStmt->bindValue(':q', '%' . $filters['q'] . '%');
        }
        if (!empty($filters['from'])) {
            $countStmt->bindValue(':from', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $countStmt->bindValue(':to', $filters['to']);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [$items, $total];
    }
}
