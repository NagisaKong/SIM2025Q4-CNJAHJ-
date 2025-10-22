<?php
declare(strict_types=1);

namespace shared\entity;

use shared\database\DatabaseConnection;
use PDO;

class UserAccount
{
    private array $userData = [];

    public function validateUser(string $email, string $password, string $userType): bool
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT u.password FROM users u JOIN profiles p ON u.profile_id = p.id
                WHERE u.status = "active" AND p.role = :role AND LOWER(u.email) = LOWER(:email)
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':role' => $userType, ':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        $dbPassword = (string) $row['password'];
        if (password_verify($password, $dbPassword) || $password === $dbPassword) {
            return true;
        }
        return false;
    }

    public function validatePassword(string $password): bool
    {
        $pattern = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,20}$/';
        return (bool) preg_match($pattern, $password);
    }

    public function validateUA(string $userType, string $email, string $password): bool
    {
        $pdo = DatabaseConnection::get();
        $existsSql = 'SELECT 1 FROM users u JOIN profiles p ON u.profile_id = p.id
                      WHERE LOWER(u.email) = LOWER(:email) AND p.role = :role LIMIT 1';
        $stmt = $pdo->prepare($existsSql);
        $stmt->execute([':email' => $email, ':role' => $userType]);
        $emailExists = (bool) $stmt->fetchColumn();
        $passwordIsCorrect = $this->validatePassword($password);
        return (!$emailExists && $passwordIsCorrect);
    }

    public function registerUA(string $userType, string $name, string $email, string $password): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $pdo = DatabaseConnection::get();
        $profileId = $this->getProfileIdByRole($userType);
        $sql = 'INSERT INTO users (profile_id, name, email, password) VALUES (:profile_id, :name, :email, :password)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':profile_id' => $profileId,
            ':name' => $name,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
        ]);
        $_SESSION['userID'] = (int) $pdo->lastInsertId();
    }

    public function getUserData(string $userType, string $email): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT u.*, p.role FROM users u JOIN profiles p ON u.profile_id = p.id
                WHERE p.role = :role AND LOWER(u.email) = LOWER(:email)
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':role' => $userType, ':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->userData = $row ? $row : [];
        return $this->userData;
    }

    public function getUserAccountsList(): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT u.*, p.role FROM users u JOIN profiles p ON u.profile_id = p.id ORDER BY u.id DESC';
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterUserAccountsByType(string $type): array
    {
        if ($type === 'All') {
            return $this->getUserAccountsList();
        }
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT u.*, p.role FROM users u JOIN profiles p ON u.profile_id = p.id
                WHERE p.role = :role ORDER BY u.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':role' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchUserAccounts(string $searchQuery): array
    {
        $pdo = DatabaseConnection::get();
        $like = '%' . $searchQuery . '%';
        $sql = 'SELECT u.*, p.role FROM users u JOIN profiles p ON u.profile_id = p.id
                WHERE LOWER(u.name) LIKE LOWER(:q) OR LOWER(u.email) LIKE LOWER(:q)
                ORDER BY u.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':q' => $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserAccountList(int $userID): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT u.*, p.role FROM users u JOIN profiles p ON u.profile_id = p.id WHERE u.id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $userID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : [];
    }

    public function updateAccountDetails(int $userID, string $type, string $email, string $status): bool
    {
        $pdo = DatabaseConnection::get();
        $profileId = $this->getProfileIdByRole($type);
        $sql = 'UPDATE users SET profile_id = :profile_id, email = :email, status = :status WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':profile_id' => $profileId,
            ':email' => $email,
            ':status' => $status,
            ':id' => $userID,
        ]);
    }

    public function getSellerID(string $sellerEmail): int
    {
        $pdo = DatabaseConnection::get();
        $sql = "SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $sellerEmail]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : 0;
    }

    private function getProfileIdByRole(string $role): int
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT id FROM profiles WHERE role = :role LIMIT 1');
        $stmt->execute([':role' => $role]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            $pdo->prepare('INSERT INTO profiles (role, description, status) VALUES (:role, :description, :status)')
                ->execute([':role' => $role, ':description' => ucfirst(str_replace('_', ' ', $role)), ':status' => 'active']);
            $id = $pdo->lastInsertId();
        }
        return (int) $id;
    }
}
