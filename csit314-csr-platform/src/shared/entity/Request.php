<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class Request
{
    public function registerRequest(
        int $pinId,
        int $serviceId,
        string $additionalDetails,
        ?string $title = null,
        ?string $location = null,
        ?string $requestedDate = null,
        ?string $status = null
    ): bool {
        $pdo = DatabaseConnection::get();

        $serviceName = $this->fetchServiceName($serviceId);
        if ($serviceName === null) {
            return false;
        }

        $resolvedTitle = $title !== null && trim($title) !== ''
            ? trim($title)
            : $serviceName . ' support request';
        $resolvedDescription = trim($additionalDetails) !== ''
            ? trim($additionalDetails)
            : 'No additional details provided.';
        $resolvedLocation = $location !== null && trim($location) !== ''
            ? trim($location)
            : 'Not specified';
        $resolvedRequestedDate = $requestedDate !== null && $requestedDate !== ''
            ? $requestedDate
            : date('Y-m-d');
        $resolvedStatus = $status !== null && trim($status) !== ''
            ? strtolower(trim($status))
            : 'open';
        $now = date('Y-m-d H:i:s');

        $sql = 'INSERT INTO "Requests" (
                    "pinID", "serviceID", "title", "description", "location",
                    "status", "requestedDate", "additionalDetails", "created_at", "updated_at"
                ) VALUES (
                    :pin_id, :service_id, :title, :description, :location,
                    :status, :requested_date, :additional_details, :created_at, :updated_at
                )';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':pin_id' => $pinId,
            ':service_id' => $serviceId,
            ':title' => $resolvedTitle,
            ':description' => $resolvedDescription,
            ':location' => $resolvedLocation,
            ':status' => $resolvedStatus,
            ':requested_date' => $resolvedRequestedDate,
            ':additional_details' => $resolvedDescription,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }

    public function createRequest(
        int $pinId,
        int $categoryId,
        string $title,
        string $description,
        string $location,
        string $requestedDate
    ): bool {
        return $this->registerRequest(
            $pinId,
            $categoryId,
            $description,
            $title,
            $location,
            $requestedDate,
            null
        );
    }

    public function increaseShortlistCount(int $requestId): void
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE "Requests" SET "shortlistCount" = "shortlistCount" + 1, "updated_at" = NOW() WHERE "requestID" = :id');
        $stmt->execute([':id' => $requestId]);
    }

    public function updateShortlistCount(int $requestId): void
    {
        $this->increaseShortlistCount($requestId);
    }

    public function incrementView(int $requestId): void
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE "Requests" SET "viewCount" = "viewCount" + 1, "updated_at" = NOW() WHERE "requestID" = :id');
        $stmt->execute([':id' => $requestId]);
    }

    public function find(int $id): ?array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, u.name AS pin_name FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                INNER JOIN users u ON u.id = pr.pin_id
                WHERE pr.id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function getPostedRequest(int $pinId, int $requestId): ?array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name
                FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE pr.id = :id AND pr.pin_id = :pin_id
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $requestId,
            ':pin_id' => $pinId,
        ]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function searchRequests(string $searchQuery): array
    {
        return $this->searchRequestsByCriteria($searchQuery);
    }

    public function searchPostedRequests(
        int $pinId,
        ?string $searchQuery = null,
        ?string $status = null,
        ?int $serviceId = null
    ): array {
        $pdo = DatabaseConnection::get();
        $conditions = ['pr.pin_id = :pin_id'];
        $params = [':pin_id' => $pinId];

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $conditions[] = '(
                pr.title ILIKE :query OR
                pr.description ILIKE :query OR
                pr.status ILIKE :query OR
                sc.name ILIKE :query
            )';
            $params[':query'] = '%' . trim($searchQuery) . '%';
        }

        if ($status !== null && $status !== '' && strtolower($status) !== 'all') {
            $conditions[] = 'pr.status = :status';
            $params[':status'] = strtolower($status);
        }

        if ($serviceId !== null) {
            $conditions[] = 'pr.category_id = :service_id';
            $params[':service_id'] = $serviceId;
        }

        $sql = 'SELECT pr.*, sc.name AS category_name
                FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY pr.updated_at DESC, pr.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function searchRequestsByCriteria(
        ?string $searchQuery = null,
        ?string $status = null,
        ?int $categoryId = null
    ): array {
        $pdo = DatabaseConnection::get();
        $conditions = [];
        $params = [];

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $conditions[] = '(pr.title ILIKE :search OR pr.description ILIKE :search OR pr.location ILIKE :search)';
            $params[':search'] = '%' . trim($searchQuery) . '%';
        }

        if ($status !== null && $status !== '' && strtolower($status) !== 'all') {
            $conditions[] = 'pr.status = :status';
            $params[':status'] = strtolower($status);
        }

        if ($categoryId !== null) {
            $conditions[] = 'pr.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $sql = 'SELECT pr.*, sc.name AS category_name, u.name AS pin_name
                FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                INNER JOIN users u ON u.id = pr.pin_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY pr.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listRequestsByPin(int $pinId): array
    {
        return $this->searchPostedRequests($pinId);
    }

    public function shortlistCountsForPin(int $pinId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.id, pr.title, pr.shortlist_count FROM pin_requests pr WHERE pr.pin_id = :pin_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pin_id' => $pinId]);
        return $stmt->fetchAll();
    }

    public function getRequestShortlistCount(int $requestId): int
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT shortlist_count FROM pin_requests WHERE id = :id');
        $stmt->execute([':id' => $requestId]);
        $value = $stmt->fetchColumn();
        return $value === false ? 0 : (int) $value;
    }

    public function getRequestViewCount(int $requestId): int
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT views_count FROM pin_requests WHERE id = :id');
        $stmt->execute([':id' => $requestId]);
        $value = $stmt->fetchColumn();
        return $value === false ? 0 : (int) $value;
    }

    public function listShortlistedRequests(int $csrId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE sl.csr_id = :csr_id
                ORDER BY sl.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':csr_id' => $csrId]);
        return $stmt->fetchAll();
    }

    public function getShortlistedRequest(int $requestId, int $csrId): ?array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE sl.csr_id = :csr_id AND pr.id = :request_id
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':csr_id' => $csrId,
            ':request_id' => $requestId,
        ]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function searchShortlistedRequests(int $csrId, string $searchQuery): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE sl.csr_id = :csr_id
                AND (pr.title ILIKE :query OR pr.description ILIKE :query OR pr.location ILIKE :query)
                ORDER BY sl.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':csr_id' => $csrId,
            ':query' => '%' . trim($searchQuery) . '%',
        ]);
        return $stmt->fetchAll();
    }

    public function getCSRHistory(int $csrId): array
    {
        return $this->searchCSRHistory($csrId);
    }

    public function searchCSRHistory(
        int $csrId,
        ?string $searchQuery = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $serviceId = null
    ): array {
        $pdo = DatabaseConnection::get();
        $conditions = ['sl.csr_id = :csr_id'];
        $params = [':csr_id' => $csrId];

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $conditions[] = '(pr.title ILIKE :history_query OR pr.description ILIKE :history_query)';
            $params[':history_query'] = '%' . trim($searchQuery) . '%';
        }

        if ($startDate !== null && $startDate !== '') {
            $conditions[] = 'sl.created_at >= :start_date';
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null && $endDate !== '') {
            $conditions[] = 'sl.created_at <= :end_date';
            $params[':end_date'] = $endDate;
        }

        if ($serviceId !== null) {
            $conditions[] = 'pr.category_id = :service_id';
            $params[':service_id'] = $serviceId;
        }

        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at, pr.updated_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY sl.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function requestHistory(int $pinId): array
    {
        return $this->getPINHistory($pinId);
    }

    public function getPINHistory(int $pinId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, pr.updated_at
                FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE pr.pin_id = :pin_id
                ORDER BY pr.updated_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pin_id' => $pinId]);
        return $stmt->fetchAll();
    }

    public function searchPINHistory(
        int $pinId,
        ?string $searchQuery = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $serviceId = null
    ): array {
        $pdo = DatabaseConnection::get();
        $conditions = ['pr.pin_id = :pin_id'];
        $params = [':pin_id' => $pinId];

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $conditions[] = '(
                pr.title ILIKE :history_query OR
                pr.description ILIKE :history_query OR
                pr.status ILIKE :history_query OR
                sc.name ILIKE :history_query
            )';
            $params[':history_query'] = '%' . trim($searchQuery) . '%';
        }

        if ($startDate !== null && $startDate !== '') {
            $conditions[] = 'pr.updated_at >= :start_date';
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null && $endDate !== '') {
            $conditions[] = 'pr.updated_at <= :end_date';
            $params[':end_date'] = $endDate;
        }

        if ($serviceId !== null) {
            $conditions[] = 'pr.category_id = :history_service_id';
            $params[':history_service_id'] = $serviceId;
        }

        $sql = 'SELECT pr.*, sc.name AS category_name, pr.updated_at
                FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY pr.updated_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateRequestDetails(
        int $pinId,
        int $requestId,
        int $serviceId,
        string $additionalDetails,
        string $status,
        ?string $title = null,
        ?string $location = null,
        ?string $requestedDate = null
    ): bool {
        $pdo = DatabaseConnection::get();

        $serviceName = $this->fetchServiceName($serviceId);
        if ($serviceName === null) {
            return false;
        }

        $resolvedTitle = $title !== null && trim($title) !== ''
            ? trim($title)
            : $serviceName . ' support request';
        $resolvedDescription = trim($additionalDetails) !== ''
            ? trim($additionalDetails)
            : 'No additional details provided.';
        $resolvedLocation = $location !== null && trim($location) !== ''
            ? trim($location)
            : 'Not specified';
        $resolvedRequestedDate = $requestedDate !== null && $requestedDate !== ''
            ? $requestedDate
            : date('Y-m-d');
        $resolvedStatus = $status !== null && trim($status) !== ''
            ? strtolower(trim($status))
            : 'open';

        $sql = 'UPDATE "Requests" SET
                    "serviceID" = :service_id,
                    "title" = :title,
                    "description" = :description,
                    "location" = :location,
                    "status" = :status,
                    "requestedDate" = :requested_date,
                    "additionalDetails" = :additional_details,
                    "updated_at" = :updated_at
                WHERE "requestID" = :request_id AND "pinID" = :pin_id';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':service_id' => $serviceId,
            ':title' => $resolvedTitle,
            ':description' => $resolvedDescription,
            ':location' => $resolvedLocation,
            ':status' => $resolvedStatus,
            ':requested_date' => $resolvedRequestedDate,
            ':additional_details' => $resolvedDescription,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':request_id' => $requestId,
            ':pin_id' => $pinId,
        ]);
    }

    public function hideRequest(int $pinId, int $requestId, string $status): bool
    {
        $pdo = DatabaseConnection::get();
        $sql = 'UPDATE "Requests" SET "status" = :status, "updated_at" = NOW()
                WHERE "requestID" = :request_id AND "pinID" = :pin_id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':status' => strtolower(trim($status)),
            ':request_id' => $requestId,
            ':pin_id' => $pinId,
        ]);
    }

    private function fetchServiceName(int $serviceId): ?string
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT name FROM service_categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serviceId]);
        $name = $stmt->fetchColumn();
        return $name === false ? null : (string) $name;
    }
}
